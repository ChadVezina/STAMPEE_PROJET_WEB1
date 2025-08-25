/**
 * Gestionnaire d'enchères côté client
 * Gère la validation en temps réel et l'interface utilisateur pour les offres
 */

class BidManager {
    constructor(auctionId) {
        this.auctionId = auctionId;
        this.bidForm = document.getElementById("bid-form");
        this.priceInput = document.getElementById("bid-price");
        this.submitButton = document.getElementById("bid-submit");
        this.errorContainer = document.getElementById("bid-errors");

        // Éléments d'affichage
        this.currentPriceElement = document.querySelector(".info-card .price");
        this.totalBidsElement = document.querySelector(".info-card .info-value");
        this.minimumBidElement = document.getElementById("minimum-bid");
        this.bidHistoryContainer = document.querySelector(".bid-list");
        this.noBidsMessage = document.querySelector(".no-bids");

        this.init();
    }

    init() {
        if (!this.bidForm) return;

        // Validation en temps réel du montant
        if (this.priceInput) {
            this.priceInput.addEventListener("input", this.debounce(this.validatePrice.bind(this), 500));
            this.priceInput.addEventListener("blur", this.validatePrice.bind(this));
        }

        // Soumission du formulaire en AJAX
        if (this.bidForm) {
            this.bidForm.addEventListener("submit", this.handleSubmit.bind(this));
        }

        // Charger les informations initiales
        this.loadAuctionInfo();

        // Actualisation périodique des statistiques
        this.startStatsRefresh();
    }

    async validatePrice() {
        const price = parseFloat(this.priceInput.value);

        if (isNaN(price) || price <= 0) {
            this.showError("Veuillez entrer un montant valide.");
            this.setSubmitButtonState(false);
            return;
        }

        try {
            const baseUrl = window.location.origin + window.location.pathname.split("/").slice(0, -2).join("/");
            const response = await fetch(`${baseUrl}/bid/validate`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: `auction_id=${this.auctionId}&price=${price}`,
            });

            const data = await response.json();

            if (data.valid) {
                this.clearError();
                this.setSubmitButtonState(true);
            } else {
                this.showError(data.errors.join(" "));
                this.setSubmitButtonState(false);

                // Suggestion du montant minimum
                if (data.minimum_bid) {
                    this.updateMinimumBid(data.minimum_bid);
                }
            }
        } catch (error) {
            console.error("Erreur de validation:", error);
            this.showError("Erreur de validation. Veuillez réessayer.");
            this.setSubmitButtonState(false);
        }
    }

    async handleSubmit(event) {
        event.preventDefault();

        // Désactiver le bouton pour éviter les double soumissions
        this.setSubmitButtonState(false, "Placement en cours...");

        const formData = new FormData(this.bidForm);

        try {
            const baseUrl = window.location.origin + window.location.pathname.split("/").slice(0, -2).join("/");
            const response = await fetch(`${baseUrl}/bid/ajax-store`, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                // Mettre à jour l'interface avec les nouvelles données
                this.updateUI(result.data);
                this.showSuccess(result.message);
                this.resetForm();
            } else {
                this.showError(result.errors.join(" "));
            }
        } catch (error) {
            console.error("Erreur lors du placement de l'enchère:", error);
            this.showError("Erreur technique lors du placement de l'enchère.");
        }

        this.setSubmitButtonState(true);
    }

    updateUI(data) {
        // Mettre à jour le prix actuel
        if (this.currentPriceElement && data.current_price) {
            const priceElements = document.querySelectorAll(".info-card .price");
            if (priceElements[1]) {
                // Deuxième élément = prix actuel
                priceElements[1].textContent = this.formatPrice(data.current_price);
            }
        }

        // Mettre à jour le nombre d'enchères
        const bidCountElements = document.querySelectorAll(".info-card .info-value");
        if (bidCountElements[2]) {
            // Troisième élément = nombre d'offres
            bidCountElements[2].textContent = data.total_bids;
        }

        // Mettre à jour l'enchère minimum
        this.updateMinimumBid(data.minimum_bid);

        // Mettre à jour l'historique des enchères
        this.updateBidHistory(data.bids);

        // Mettre à jour les statistiques
        this.updateStats(data.stats);
    }

    updateBidHistory(bids) {
        if (!this.bidHistoryContainer) return;

        // Cacher le message "aucune enchère"
        if (this.noBidsMessage) {
            this.noBidsMessage.style.display = "none";
        }

        // Créer le HTML pour l'historique
        const bidHTML = bids
            .map((bid, index) => {
                const isWinning = index === 0;
                const isOwnBid = bid.bidder_name === "Vous"; // À améliorer selon votre logique

                let classes = "bid-item";
                if (isWinning) classes += " winning";
                if (isOwnBid) classes += " own-bid";

                // Calculer le temps écoulé
                const bidTime = new Date(bid.bid_at);
                const timeAgo = this.formatTimeAgo(bidTime);

                // Calculer le pourcentage d'augmentation si c'est pas la première enchère
                let increasePercent = "";
                if (index < bids.length - 1) {
                    const previousBid = bids[index + 1];
                    const increase = (((bid.price - previousBid.price) / previousBid.price) * 100).toFixed(1);
                    increasePercent = `<span class="increase-percent">+${increase}%</span>`;
                }

                return `
                <div class="${classes}">
                    <div class="bid-card">
                        <div class="bidder-info">
                            <div class="bidder-name">
                                ${bid.bidder_name}
                                ${isWinning ? '<span class="bid-status-badge winning">En tête</span>' : ""}
                            </div>
                            <div class="bid-meta">
                                <span class="bid-time">${bidTime.toLocaleDateString("fr-CA")} ${bidTime.toLocaleTimeString("fr-CA", {
                    hour: "2-digit",
                    minute: "2-digit",
                    second: "2-digit",
                })}</span>
                                <span class="time-ago">${timeAgo}</span>
                            </div>
                        </div>
                        <div class="bid-amount-section">
                            <div class="bid-amount ${isWinning ? "winning" : ""}">
                                ${this.formatPrice(bid.price)}
                            </div>
                            ${increasePercent}
                        </div>
                    </div>
                </div>
            `;
            })
            .join("");

        this.bidHistoryContainer.innerHTML = bidHTML;
    }

    formatTimeAgo(date) {
        const now = new Date();
        const diff = now - date;
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (days > 0) return `il y a ${days}j`;
        if (hours > 0) return `il y a ${hours}h`;
        if (minutes > 0) return `il y a ${minutes}min`;
        return `il y a ${seconds}s`;
    }

    async loadAuctionInfo() {
        try {
            const baseUrl = window.location.origin + window.location.pathname.split("/").slice(0, -2).join("/");
            const response = await fetch(`${baseUrl}/bid/auction-stats?auction_id=${this.auctionId}`, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
            });
            const data = await response.json();

            this.updateStats(data.stats);
            this.updateMinimumBid(data.minimum_bid);
            this.updateBidHistory(data.bids || []);
        } catch (error) {
            console.error("Erreur de chargement des informations:", error);
        }
    }

    updateStats(stats) {
        // Mettre à jour les statistiques dans la sidebar
        const statsContainer = document.querySelector(".auction-stats");
        if (!statsContainer) return;

        const statElements = statsContainer.querySelectorAll(".stat .value");
        if (statElements[0]) {
            // Enchérisseurs uniques
            statElements[0].textContent = stats.unique_bidders;
        }
        if (statElements[1] && stats.average_bid > 0) {
            // Offre moyenne
            statElements[1].textContent = this.formatPrice(stats.average_bid);
        }
    }

    updateMinimumBid(amount) {
        if (this.minimumBidElement) {
            this.minimumBidElement.textContent = this.formatPrice(amount);
        }

        // Mettre à jour le placeholder de l'input
        if (this.priceInput) {
            this.priceInput.placeholder = `Minimum: ${this.formatPrice(amount)}`;
            this.priceInput.setAttribute("min", amount.toString());
        }
    }

    resetForm() {
        if (this.priceInput) {
            this.priceInput.value = "";
        }
        this.clearError();
    }

    showError(message) {
        if (this.errorContainer) {
            this.errorContainer.innerHTML = `<div class="alert alert-error">${message}</div>`;
            this.errorContainer.style.display = "block";
        }
    }

    showSuccess(message) {
        if (this.errorContainer) {
            this.errorContainer.innerHTML = `<div class="alert alert-success">${message}</div>`;
            this.errorContainer.style.display = "block";

            // Auto-hide success message
            setTimeout(() => {
                this.clearError();
            }, 3000);
        }
    }

    clearError() {
        if (this.errorContainer) {
            this.errorContainer.innerHTML = "";
            this.errorContainer.style.display = "none";
        }
    }

    setSubmitButtonState(enabled, text = null) {
        if (!this.submitButton) return;

        this.submitButton.disabled = !enabled;
        if (text) {
            this.submitButton.textContent = text;
        } else {
            this.submitButton.textContent = enabled ? "Placer l'offre" : "Offre invalide";
        }
    }

    startStatsRefresh() {
        // Actualiser les stats toutes les 30 secondes
        setInterval(() => {
            this.loadAuctionInfo();
        }, 30000);
    }

    formatPrice(amount) {
        return new Intl.NumberFormat("fr-CA", {
            style: "currency",
            currency: "CAD",
        }).format(amount);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Fonctions utilitaires pour les suggestions de montants
class BidSuggestions {
    static generateSuggestions(currentBid, minIncrement = 0.01) {
        const suggestions = [];
        const base = Math.max(currentBid, 0);

        // Suggestions d'incréments logiques
        const increments = [minIncrement, 0.5, 1.0, 2.0, 5.0, 10.0];

        increments.forEach((increment) => {
            if (increment >= minIncrement) {
                suggestions.push(base + increment);
            }
        });

        return suggestions.slice(0, 4); // Limiter à 4 suggestions
    }

    static renderSuggestions(suggestions, containerSelector, callback) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        const html = suggestions
            .map(
                (amount) =>
                    `<button type="button" class="bid-suggestion" data-amount="${amount}">
                ${new Intl.NumberFormat("fr-CA", { style: "currency", currency: "CAD" }).format(amount)}
            </button>`
            )
            .join("");

        container.innerHTML = `<div class="bid-suggestions">${html}</div>`;

        // Ajouter les listeners
        container.querySelectorAll(".bid-suggestion").forEach((btn) => {
            btn.addEventListener("click", () => {
                const amount = parseFloat(btn.dataset.amount);
                callback(amount);
            });
        });
    }
}

// Initialisation automatique si on est sur une page d'enchère
document.addEventListener("DOMContentLoaded", () => {
    const auctionElement = document.querySelector("[data-auction-id]");
    if (auctionElement) {
        const auctionId = parseInt(auctionElement.dataset.auctionId);
        if (auctionId) {
            window.bidManager = new BidManager(auctionId);

            // Charger les suggestions de montants
            const currentBidElement = document.querySelector("[data-current-bid]");
            if (currentBidElement) {
                const currentBid = parseFloat(currentBidElement.dataset.currentBid) || 0;
                const suggestions = BidSuggestions.generateSuggestions(currentBid);

                BidSuggestions.renderSuggestions(suggestions, "#bid-suggestions", (amount) => {
                    const priceInput = document.getElementById("bid-price");
                    if (priceInput) {
                        priceInput.value = amount.toFixed(2);
                        priceInput.dispatchEvent(new Event("input"));
                    }
                });
            }
        }
    }
});
