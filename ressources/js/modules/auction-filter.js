/**
 * AuctionFilter - Dynamic filtering for auction listings
 */
class AuctionFilter {
    constructor() {
        this.baseUrl = this.getBaseUrl();
        this.currentFilters = this.getInitialFilters();
        this.currentPage = 1;
        this.isLoading = false;
        this.debounceTimeout = null;

        this.init();
    }

    init() {
        this.createFilterUI();
        this.bindEvents();
        this.updateUrlFromFilters();
    }

    getBaseUrl() {
        // Get base URL from config or determine from current URL
        const metaBase = document.querySelector('meta[name="base-url"]');
        if (metaBase) {
            return metaBase.getAttribute("content");
        }

        // Fallback: extract from current URL
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

    createFilterUI() {
        const auctionsHeader = document.querySelector(".auctions__header");
        if (!auctionsHeader) return;

        // Replace the existing tools section with enhanced filters
        const toolsSection = auctionsHeader.querySelector(".auctions__tools");
        if (toolsSection) {
            toolsSection.innerHTML = this.getFilterHTML();
        }
    }

    getFilterHTML() {
        return `
            <div class="auctions__search-section">
                <form class="auctions__search-form" id="auction-search-form">
                    <div class="search-row">
                        <div class="search-field">
                            <input 
                                class="auctions__input" 
                                type="text" 
                                name="q" 
                                id="search-input"
                                placeholder="Rechercher un timbre..." 
                                value="${this.escapeHtml(this.currentFilters.search)}"
                            >
                        </div>
                        <div class="search-field">
                            <input 
                                class="auctions__input" 
                                type="text" 
                                name="seller" 
                                id="seller-input"
                                placeholder="Vendeur..." 
                                value="${this.escapeHtml(this.currentFilters.seller)}"
                            >
                        </div>
                        <button class="button button--primary" type="submit">
                            <i class="icon-search"></i> Rechercher
                        </button>
                    </div>
                </form>
            </div>

            <div class="auctions__filters">
                <button class="button button--ghost filter-toggle" id="filter-toggle">
                    <i class="icon-filter"></i> Filtres avancés
                </button>
                
                <div class="advanced-filters" id="advanced-filters">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="min-price">Prix min.</label>
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
                            <label for="max-price">Prix max.</label>
                            <input 
                                type="number" 
                                id="max-price" 
                                name="max_price" 
                                min="0" 
                                step="0.01" 
                                placeholder="Aucun max"
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
                                <option value="price_asc" ${this.currentFilters.sort_by === "price_asc" ? "selected" : ""}>Prix (croissant)</option>
                                <option value="price_desc" ${
                                    this.currentFilters.sort_by === "price_desc" ? "selected" : ""
                                }>Prix (décroissant)</option>
                                <option value="title_asc" ${this.currentFilters.sort_by === "title_asc" ? "selected" : ""}>Titre (A-Z)</option>
                                <option value="title_desc" ${this.currentFilters.sort_by === "title_desc" ? "selected" : ""}>Titre (Z-A)</option>
                                <option value="seller_asc" ${this.currentFilters.sort_by === "seller_asc" ? "selected" : ""}>Vendeur (A-Z)</option>
                                <option value="seller_desc" ${this.currentFilters.sort_by === "seller_desc" ? "selected" : ""}>Vendeur (Z-A)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="button" class="button button--secondary" id="clear-filters">
                            Effacer les filtres
                        </button>
                        <button type="button" class="button button--primary" id="apply-filters">
                            Appliquer
                        </button>
                    </div>
                </div>
            </div>

            <div class="auctions__results-info">
                <span id="results-count">Chargement...</span>
                <div class="loading-indicator" id="loading-indicator">
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

        // Seller input with debounce
        const sellerInput = document.getElementById("seller-input");
        if (sellerInput) {
            sellerInput.addEventListener("input", () => {
                this.debounceSearch();
            });
        }

        // Filter toggle
        const filterToggle = document.getElementById("filter-toggle");
        const advancedFilters = document.getElementById("advanced-filters");
        if (filterToggle && advancedFilters) {
            filterToggle.addEventListener("click", () => {
                advancedFilters.classList.toggle("expanded");
                filterToggle.classList.toggle("active");
            });
        }

        // Filter controls
        const applyBtn = document.getElementById("apply-filters");
        if (applyBtn) {
            applyBtn.addEventListener("click", () => {
                this.applyFilters();
            });
        }

        const clearBtn = document.getElementById("clear-filters");
        if (clearBtn) {
            clearBtn.addEventListener("click", () => {
                this.clearFilters();
            });
        }

        // Real-time filter changes
        const filterInputs = document.querySelectorAll("#advanced-filters input, #advanced-filters select");
        filterInputs.forEach((input) => {
            input.addEventListener("change", () => {
                this.debounceFilter();
            });
        });

        // Pagination handling
        this.bindPaginationEvents();
    }

    bindPaginationEvents() {
        document.addEventListener("click", (e) => {
            if (e.target.matches(".pagination__link, .pagination__nav")) {
                e.preventDefault();
                const url = new URL(e.target.href);
                const page = parseInt(url.searchParams.get("page")) || 1;
                this.loadPage(page);
            }
        });
    }

    debounceSearch() {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(() => {
            this.handleSearch();
        }, 500); // 500ms debounce
    }

    debounceFilter() {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(() => {
            this.applyFilters();
        }, 300); // 300ms debounce for filters
    }

    handleSearch() {
        const searchInput = document.getElementById("search-input");
        const sellerInput = document.getElementById("seller-input");

        this.currentFilters.search = searchInput ? searchInput.value.trim() : "";
        this.currentFilters.seller = sellerInput ? sellerInput.value.trim() : "";
        this.currentPage = 1;

        this.loadResults();
    }

    applyFilters() {
        // Get all filter values
        this.currentFilters.min_price = document.getElementById("min-price")?.value || "";
        this.currentFilters.max_price = document.getElementById("max-price")?.value || "";
        this.currentFilters.status = document.getElementById("status-filter")?.value || "";
        this.currentFilters.sort_by = document.getElementById("sort-filter")?.value || "end_time_asc";

        this.currentPage = 1;
        this.loadResults();
    }

    clearFilters() {
        // Reset all filters
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

        this.loadResults();
    }

    loadPage(page) {
        this.currentPage = page;
        this.loadResults();
    }

    async loadResults() {
        if (this.isLoading) return;

        this.setLoading(true);

        try {
            const params = new URLSearchParams({
                page: this.currentPage.toString(),
                ...this.currentFilters,
            });

            // Remove empty parameters
            for (const [key, value] of [...params.entries()]) {
                if (!value) {
                    params.delete(key);
                }
            }

            const url = `${this.baseUrl}/auctions/api/filter?${params.toString()}`;
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                this.updateResults(data.data);
                this.updateUrl(params);
                this.updateResultsCount(data.data.pagination.total);
            } else {
                this.showError("Erreur lors du chargement des résultats.");
            }
        } catch (error) {
            console.error("Filter error:", error);
            this.showError("Erreur de connexion. Veuillez réessayer.");
        } finally {
            this.setLoading(false);
        }
    }

    updateResults(data) {
        const auctionsGrid = document.querySelector(".auctions-grid");
        if (!auctionsGrid) return;

        if (data.auctions.length === 0) {
            auctionsGrid.innerHTML = `
                <div class="no-auctions">
                    <p>Aucune enchère ne correspond à vos critères de recherche.</p>
                </div>
            `;
        } else {
            auctionsGrid.innerHTML = data.auctions.map((auction) => this.generateAuctionCard(auction)).join("");
        }

        // Update pagination
        this.updatePagination(data.pagination);

        // Restart countdown timers
        this.initCountdowns();
    }

    generateAuctionCard(auction) {
        const now = new Date().getTime();
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
        const mainImage = auction.main_image || "";

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

    updatePagination(pagination) {
        const existingPagination = document.querySelector(".pagination");
        if (!existingPagination) return;

        if (pagination.pages <= 1) {
            existingPagination.style.display = "none";
            return;
        }

        existingPagination.style.display = "block";

        // Update pagination info
        const paginationInfo = existingPagination.querySelector(".pagination__info");
        if (paginationInfo) {
            paginationInfo.textContent = `Page ${pagination.page} sur ${pagination.pages} (${pagination.total} enchères au total)`;
        }

        // Update pagination controls
        const paginationControls = existingPagination.querySelector(".pagination__controls");
        if (paginationControls) {
            paginationControls.innerHTML = this.generatePaginationHTML(pagination);
        }
    }

    generatePaginationHTML(pagination) {
        const { page, pages } = pagination;
        let html = "";

        // Previous button
        if (page > 1) {
            html += `<a href="${this.baseUrl}/auctions?page=${page - 1}" class="pagination__nav pagination__nav--prev">‹ Précédent</a>`;
        }

        // Page numbers
        html += '<div class="pagination__numbers">';

        const start = Math.max(1, page - 2);
        const end = Math.min(pages, page + 2);

        if (start > 1) {
            html += `<a href="${this.baseUrl}/auctions?page=1" class="pagination__link">1</a>`;
            if (start > 2) {
                html += '<span class="pagination__ellipsis">...</span>';
            }
        }

        for (let i = start; i <= end; i++) {
            const activeClass = i === page ? " pagination__link--active" : "";
            const ariaCurrent = i === page ? ' aria-current="page"' : "";
            html += `<a href="${this.baseUrl}/auctions?page=${i}" class="pagination__link${activeClass}"${ariaCurrent}>${i}</a>`;
        }

        if (end < pages) {
            if (end < pages - 1) {
                html += '<span class="pagination__ellipsis">...</span>';
            }
            html += `<a href="${this.baseUrl}/auctions?page=${pages}" class="pagination__link">${pages}</a>`;
        }

        html += "</div>";

        // Next button
        if (page < pages) {
            html += `<a href="${this.baseUrl}/auctions?page=${page + 1}" class="pagination__nav pagination__nav--next">Suivant ›</a>`;
        }

        return html;
    }

    initCountdowns() {
        // Initialize countdown functionality for new auction cards
        if (window.updateCountdowns) {
            window.updateCountdowns();
        }
    }

    updateUrl(params) {
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.replaceState(null, "", newUrl);
    }

    updateUrlFromFilters() {
        const params = new URLSearchParams();

        for (const [key, value] of Object.entries(this.currentFilters)) {
            if (value) {
                params.set(key, value);
            }
        }

        if (this.currentPage > 1) {
            params.set("page", this.currentPage.toString());
        }

        this.updateUrl(params);
    }

    updateResultsCount(total) {
        const resultsCount = document.getElementById("results-count");
        if (resultsCount) {
            const text = total === 0 ? "Aucun résultat" : total === 1 ? "1 enchère trouvée" : `${total} enchères trouvées`;
            resultsCount.textContent = text;
        }
    }

    setLoading(loading) {
        this.isLoading = loading;
        const loadingIndicator = document.getElementById("loading-indicator");
        if (loadingIndicator) {
            loadingIndicator.style.display = loading ? "block" : "none";
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
    // Only initialize on auction pages
    if (window.location.pathname.includes("/auctions") && document.querySelector(".auctions")) {
        window.auctionFilter = new AuctionFilter();
    }
});

// Export for use in other scripts
if (typeof module !== "undefined" && module.exports) {
    module.exports = AuctionFilter;
}
