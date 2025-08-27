/**
 * AuctionFilter - Client-side dynamic filtering for auction listings
 */
class AuctionFilter {
    constructor() {
        this.baseUrl = this.getBaseUrl();
        this.allAuctions = [];
        this.filteredAuctions = [];
        this.currentFilters = this.getInitialFilters();
        this.currentPage = 1;
        this.itemsPerPage = 9;
        this.isLoading = false;
        this.debounceTimeout = null;

        this.init();
    }

    init() {
        this.loadAuctionData();
        this.createFilterUI();
        this.bindEvents();
    }

    getBaseUrl() {
        const metaBase = document.querySelector('meta[name="base-url"]');
        if (metaBase) {
            return metaBase.getAttribute("content");
        }

        const path = window.location.pathname;
        const segments = path.split("/");
        const baseIndex = segments.indexOf("auctions");
        if (baseIndex > 0) {
            return segments.slice(0, baseIndex).join("/");
        }

        return "";
    }

    getInitialFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            search: urlParams.get("q") || "",
            seller: urlParams.get("seller") || "",
            min_price: urlParams.get("min_price") || "",
            max_price: urlParams.get("max_price") || "",
            status: urlParams.get("status") || "",
            sort_by: urlParams.get("sort_by") || "end_time_asc",
        };
    }

    loadAuctionData() {
        // Load from embedded JSON data
        const dataScript = document.getElementById("auction-data");
        if (dataScript) {
            try {
                this.allAuctions = JSON.parse(dataScript.textContent);
                this.applyFiltersAndRender();
                return;
            } catch (e) {
                console.error("Failed to parse embedded auction data:", e);
            }
        }

        // Fallback: load from API
        this.loadAuctionsFromAPI();
    }

    async loadAuctionsFromAPI() {
        this.setLoading(true);
        try {
            const response = await fetch(`${this.baseUrl}/auctions/api/all`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (data.success) {
                this.allAuctions = data.auctions;
                this.applyFiltersAndRender();
            } else {
                this.showError("Erreur lors du chargement des enchères.");
            }
        } catch (error) {
            console.error("API loading error:", error);
            this.showError("Erreur de connexion. Veuillez recharger la page.");
        } finally {
            this.setLoading(false);
        }
    }

    createFilterUI() {
        const toolsContainer = document.querySelector(".auctions__tools");
        if (!toolsContainer) return;

        toolsContainer.innerHTML = this.getFilterHTML();
    }

    getFilterHTML() {
        return `
            <div class="auctions__filters">
                <div class="filters-container">
                    <form class="auctions__search-form" id="auction-search-form">
                        <div class="filters-grid">
                            <div class="filter-group">
                                <label for="search-input">Rechercher</label>
                                <input 
                                    class="auctions__input" 
                                    type="text" 
                                    name="q" 
                                    id="search-input"
                                    placeholder="Rechercher un timbre..." 
                                    value="${this.escapeHtml(this.currentFilters.search)}"
                                >
                            </div>
                            
                            <div class="filter-group">
                                <label for="seller-input">Vendeur</label>
                                <input 
                                    class="auctions__input" 
                                    type="text" 
                                    name="seller" 
                                    id="seller-input"
                                    placeholder="Nom du vendeur..." 
                                    value="${this.escapeHtml(this.currentFilters.seller)}"
                                >
                            </div>
                            
                            <div class="filter-group">
                                <label for="min-price">Prix minimum</label>
                                <input 
                                    type="number" 
                                    id="min-price" 
                                    name="min_price" 
                                    min="0" 
                                    step="0.01" 
                                    placeholder="0.00"
                                    value="${this.currentFilters.min_price}"
                                >
                            </div>
                            
                            <div class="filter-group">
                                <label for="max-price">Prix maximum</label>
                                <input 
                                    type="number" 
                                    id="max-price" 
                                    name="max_price" 
                                    min="0" 
                                    step="0.01" 
                                    placeholder="Aucun maximum"
                                    value="${this.currentFilters.max_price}"
                                >
                            </div>
                            
                            <div class="filter-group">
                                <label for="status-filter">Statut</label>
                                <select id="status-filter" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="active" ${this.currentFilters.status === "active" ? "selected" : ""}>En cours</option>
                                    <option value="upcoming" ${this.currentFilters.status === "upcoming" ? "selected" : ""}>À venir</option>
                                    <option value="ended" ${this.currentFilters.status === "ended" ? "selected" : ""}>Terminées</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="sort-filter">Trier par</label>
                                <select id="sort-filter" name="sort_by">
                                    <option value="end_time_asc" ${
                                        this.currentFilters.sort_by === "end_time_asc" ? "selected" : ""
                                    }>Fin (croissant)</option>
                                    <option value="end_time_desc" ${
                                        this.currentFilters.sort_by === "end_time_desc" ? "selected" : ""
                                    }>Fin (décroissant)</option>
                                    <option value="price_asc" ${
                                        this.currentFilters.sort_by === "price_asc" ? "selected" : ""
                                    }>Prix (croissant)</option>
                                    <option value="price_desc" ${
                                        this.currentFilters.sort_by === "price_desc" ? "selected" : ""
                                    }>Prix (décroissant)</option>
                                    <option value="title_asc" ${this.currentFilters.sort_by === "title_asc" ? "selected" : ""}>Titre (A-Z)</option>
                                    <option value="title_desc" ${this.currentFilters.sort_by === "title_desc" ? "selected" : ""}>Titre (Z-A)</option>
                                    <option value="seller_asc" ${
                                        this.currentFilters.sort_by === "seller_asc" ? "selected" : ""
                                    }>Vendeur (A-Z)</option>
                                    <option value="seller_desc" ${
                                        this.currentFilters.sort_by === "seller_desc" ? "selected" : ""
                                    }>Vendeur (Z-A)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="button" class="button button--secondary" id="clear-filters">
                                🗑️ Effacer les filtres
                            </button>
                            <button type="submit" class="button button--primary">
                                🔍 Rechercher
                            </button>
                    </div>
                </div>
            </div>

            <div class="auctions__results-info">
                <span id="results-count">Chargement...</span>
                <div class="loading-indicator" id="loading-indicator" style="display: none;">
                    <div class="spinner"></div>
                </div>
            </div>
        `;
    }

    bindEvents() {
        // Search form submission
        const searchForm = document.getElementById("auction-search-form");
        if (searchForm) {
            searchForm.addEventListener("submit", (e) => {
                e.preventDefault();
                this.handleSearch();
            });
        }

        // Real-time search with debounce
        const searchInput = document.getElementById("search-input");
        if (searchInput) {
            searchInput.addEventListener("input", () => {
                this.debounceSearch();
            });
        }

        const sellerInput = document.getElementById("seller-input");
        if (sellerInput) {
            sellerInput.addEventListener("input", () => {
                this.debounceSearch();
            });
        }

        // Clear filters button
        const clearBtn = document.getElementById("clear-filters");
        if (clearBtn) {
            clearBtn.addEventListener("click", () => {
                this.clearFilters();
            });
        }

        // Real-time filter changes - all filter inputs including search and seller
        const filterInputs = document.querySelectorAll(".filters-container input, .filters-container select");
        filterInputs.forEach((input) => {
            if (input.type === "text") {
                // For text inputs, use debounced search
                input.addEventListener("input", () => {
                    this.debounceFilter();
                });
            } else {
                // For selects and number inputs, apply immediately
                input.addEventListener("change", () => {
                    this.debounceFilter();
                });
            }
        });

        // Pagination event delegation
        document.addEventListener("click", (e) => {
            if (e.target.matches(".pagination__link")) {
                e.preventDefault();
                const page = parseInt(e.target.textContent) || 1;
                this.goToPage(page);
            } else if (e.target.matches(".pagination__nav")) {
                e.preventDefault();
                const isPrev = e.target.classList.contains("pagination__nav--prev");
                this.goToPage(isPrev ? this.currentPage - 1 : this.currentPage + 1);
            }
        });
    }

    debounceSearch() {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(() => {
            this.handleSearch();
        }, 300);
    }

    debounceFilter() {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(() => {
            this.handleSearch();
        }, 200);
    }

    handleSearch() {
        // Update all filter values from form
        this.currentFilters.search = document.getElementById("search-input")?.value.trim() || "";
        this.currentFilters.seller = document.getElementById("seller-input")?.value.trim() || "";
        this.currentFilters.min_price = document.getElementById("min-price")?.value || "";
        this.currentFilters.max_price = document.getElementById("max-price")?.value || "";
        this.currentFilters.status = document.getElementById("status-filter")?.value || "";
        this.currentFilters.sort_by = document.getElementById("sort-filter")?.value || "end_time_asc";

        this.currentPage = 1;
        this.applyFiltersAndRender();
    }

    clearFilters() {
        this.currentFilters = {
            search: "",
            seller: "",
            min_price: "",
            max_price: "",
            status: "",
            sort_by: "end_time_asc",
        };
        this.currentPage = 1;

        // Update form fields
        document.getElementById("search-input").value = "";
        document.getElementById("seller-input").value = "";
        document.getElementById("min-price").value = "";
        document.getElementById("max-price").value = "";
        document.getElementById("status-filter").value = "";
        document.getElementById("sort-filter").value = "end_time_asc";

        this.applyFiltersAndRender();
    }

    applyFiltersAndRender() {
        this.setLoading(true);

        // Apply filters
        this.filteredAuctions = this.allAuctions.filter((auction) => {
            // Search filter (stamp name)
            if (this.currentFilters.search) {
                const searchTerm = this.currentFilters.search.toLowerCase();
                const stampName = (auction.stamp_name || "").toLowerCase();
                if (!stampName.includes(searchTerm)) {
                    return false;
                }
            }

            // Seller filter
            if (this.currentFilters.seller) {
                const sellerTerm = this.currentFilters.seller.toLowerCase();
                const sellerName = (auction.seller_name || "").toLowerCase();
                if (!sellerName.includes(sellerTerm)) {
                    return false;
                }
            }

            // Price filters
            const currentPrice = parseFloat(auction.current_price || auction.min_price || 0);
            if (this.currentFilters.min_price && currentPrice < parseFloat(this.currentFilters.min_price)) {
                return false;
            }
            if (this.currentFilters.max_price && currentPrice > parseFloat(this.currentFilters.max_price)) {
                return false;
            }

            // Status filter
            if (this.currentFilters.status) {
                const now = Date.now();
                const startTime = new Date(auction.auction_start).getTime();
                const endTime = new Date(auction.auction_end).getTime();

                let status = "";
                if (startTime > now) {
                    status = "upcoming";
                } else if (endTime > now) {
                    status = "active";
                } else {
                    status = "ended";
                }

                if (status !== this.currentFilters.status) {
                    return false;
                }
            }

            return true;
        });

        // Apply sorting
        this.sortAuctions();

        // Update UI
        this.renderAuctions();
        this.renderPagination();
        this.updateResultsCount();
        this.updateURL();

        this.setLoading(false);
    }

    sortAuctions() {
        this.filteredAuctions.sort((a, b) => {
            switch (this.currentFilters.sort_by) {
                case "end_time_asc":
                    return new Date(a.auction_end) - new Date(b.auction_end);
                case "end_time_desc":
                    return new Date(b.auction_end) - new Date(a.auction_end);
                case "price_asc":
                    return parseFloat(a.current_price || a.min_price || 0) - parseFloat(b.current_price || b.min_price || 0);
                case "price_desc":
                    return parseFloat(b.current_price || b.min_price || 0) - parseFloat(a.current_price || a.min_price || 0);
                case "title_asc":
                    return (a.stamp_name || "").localeCompare(b.stamp_name || "");
                case "title_desc":
                    return (b.stamp_name || "").localeCompare(a.stamp_name || "");
                case "seller_asc":
                    return (a.seller_name || "").localeCompare(b.seller_name || "");
                case "seller_desc":
                    return (b.seller_name || "").localeCompare(a.seller_name || "");
                default:
                    return 0;
            }
        });
    }

    renderAuctions() {
        const auctionsGrid = document.querySelector(".auctions-grid");
        const loadingState = document.querySelector(".loading-state");

        if (loadingState) {
            loadingState.style.display = "none";
        }

        if (!auctionsGrid) return;

        auctionsGrid.style.display = "grid";

        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        const pageAuctions = this.filteredAuctions.slice(start, end);

        if (pageAuctions.length === 0) {
            auctionsGrid.innerHTML = `
                <div class="no-auctions">
                    <p>Aucune enchère ne correspond à vos critères de recherche.</p>
                </div>
            `;
        } else {
            auctionsGrid.innerHTML = pageAuctions.map((auction) => this.generateAuctionCard(auction)).join("");
            this.initCountdowns();
        }
    }

    generateAuctionCard(auction) {
        const now = Date.now();
        const startTime = new Date(auction.auction_start).getTime();
        const endTime = new Date(auction.auction_end).getTime();

        let badge = "";
        let badgeClass = "";

        if (startTime > now) {
            badge = "À venir";
            badgeClass = "auction-card__badge--upcoming";
        } else if (endTime > now) {
            badge = "En cours";
            badgeClass = "auction-card__badge--active";
        } else {
            badge = "Terminée";
            badgeClass = "auction-card__badge--ended";
        }

        const currentPrice = auction.current_price || auction.min_price;
        const mainImage = auction.main_image || "/uploads/stamps/placeholder.jpg";

        return `
            <article class="auction-card">
                <a class="auction-card__link" href="${this.baseUrl}/auctions/show?id=${auction.id}">
                    <div class="auction-card__image" style="background-image:url('${this.escapeHtml(mainImage)}');">
                        <span class="auction-card__badge ${badgeClass}">${badge}</span>
                    </div>
                    <div class="auction-card__content">
                        <h3 class="auction-card__title">${this.escapeHtml(auction.stamp_name || "Timbre")}</h3>
                        <div class="auction-card__seller">Vendeur: ${this.escapeHtml(auction.seller_name || "Inconnu")}</div>
                        <div class="auction-card__price">
                            ${parseFloat(currentPrice).toFixed(2)} $ CAD
                        </div>
                        <div class="auction-card__countdown" data-end-time="${new Date(auction.auction_end).toISOString()}">
                            <span class="countdown-text">Calcul en cours...</span>
                        </div>
                    </div>
                </a>
            </article>
        `;
    }

    renderPagination() {
        const totalPages = Math.ceil(this.filteredAuctions.length / this.itemsPerPage);
        const pagination = document.querySelector(".pagination");

        if (!pagination) return;

        if (totalPages <= 1) {
            pagination.style.display = "none";
            return;
        }

        pagination.style.display = "flex";
        pagination.innerHTML = `
            <div class="pagination__info">
                Page ${this.currentPage} sur ${totalPages} (${this.filteredAuctions.length} enchères au total)
            </div>
            <div class="pagination__controls">
                ${this.generatePaginationHTML(totalPages)}
            </div>
        `;
    }

    generatePaginationHTML(totalPages) {
        let html = "";

        // Previous button
        if (this.currentPage > 1) {
            html += `<a href="#" class="pagination__nav pagination__nav--prev">‹ Précédent</a>`;
        }

        // Page numbers
        html += '<div class="pagination__numbers">';

        const start = Math.max(1, this.currentPage - 2);
        const end = Math.min(totalPages, this.currentPage + 2);

        if (start > 1) {
            html += `<a href="#" class="pagination__link">1</a>`;
            if (start > 2) {
                html += '<span class="pagination__ellipsis">...</span>';
            }
        }

        for (let i = start; i <= end; i++) {
            const activeClass = i === this.currentPage ? " pagination__link--active" : "";
            html += `<a href="#" class="pagination__link${activeClass}">${i}</a>`;
        }

        if (end < totalPages) {
            if (end < totalPages - 1) {
                html += '<span class="pagination__ellipsis">...</span>';
            }
            html += `<a href="#" class="pagination__link">${totalPages}</a>`;
        }

        html += "</div>";

        // Next button
        if (this.currentPage < totalPages) {
            html += `<a href="#" class="pagination__nav pagination__nav--next">Suivant ›</a>`;
        }

        return html;
    }

    goToPage(page) {
        const totalPages = Math.ceil(this.filteredAuctions.length / this.itemsPerPage);
        this.currentPage = Math.max(1, Math.min(page, totalPages));
        this.renderAuctions();
        this.renderPagination();
        this.updateURL();

        // Scroll to top
        document.querySelector(".auctions__header").scrollIntoView({ behavior: "smooth" });
    }

    updateResultsCount() {
        const resultsCount = document.getElementById("results-count");
        if (resultsCount) {
            const total = this.filteredAuctions.length;
            const text = total === 0 ? "Aucun résultat" : total === 1 ? "1 enchère trouvée" : `${total} enchères trouvées`;
            resultsCount.textContent = text;
        }
    }

    updateURL() {
        const params = new URLSearchParams();

        Object.entries(this.currentFilters).forEach(([key, value]) => {
            if (value && value !== "end_time_asc") {
                params.set(key === "search" ? "q" : key, value);
            }
        });

        if (this.currentPage > 1) {
            params.set("page", this.currentPage.toString());
        }

        const newUrl = `${window.location.pathname}${params.toString() ? "?" + params.toString() : ""}`;
        window.history.replaceState(null, "", newUrl);
    }

    initCountdowns() {
        if (window.updateCountdowns && typeof window.updateCountdowns === "function") {
            window.updateCountdowns();
        } else {
            // Fallback countdown implementation
            this.updateCountdowns();
            setInterval(() => this.updateCountdowns(), 1000);
        }
    }

    updateCountdowns() {
        const countdownElements = document.querySelectorAll(".auction-card__countdown");

        countdownElements.forEach((element) => {
            const endTime = new Date(element.getAttribute("data-end-time")).getTime();
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                element.innerHTML = '<span class="countdown-text">Terminée</span>';
                element.classList.add("expired");
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let timeString = "";
            if (days > 0) {
                timeString = `${days}j ${hours}h ${minutes}m`;
            } else if (hours > 0) {
                timeString = `${hours}h ${minutes}m ${seconds}s`;
            } else if (minutes > 0) {
                timeString = `${minutes}m ${seconds}s`;
            } else {
                timeString = `${seconds}s`;
            }

            element.innerHTML = `<span class="countdown-text">${timeString}</span>`;
        });
    }

    setLoading(loading) {
        this.isLoading = loading;
        const loadingIndicator = document.getElementById("loading-indicator");
        if (loadingIndicator) {
            loadingIndicator.style.display = loading ? "flex" : "none";
        }
    }

    showError(message) {
        if (window.flashMessages && window.flashMessages.showMessage) {
            window.flashMessages.showMessage(message, "error");
        } else {
            alert(message);
        }
    }

    escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text || "";
        return div.innerHTML;
    }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    if (window.location.pathname.includes("/auctions") && document.querySelector(".auctions")) {
        window.auctionFilter = new AuctionFilter();
    }
});

// Export for use in other scripts
if (typeof module !== "undefined" && module.exports) {
    module.exports = AuctionFilter;
}
