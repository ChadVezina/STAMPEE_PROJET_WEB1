// Flash message handling
class FlashMessages {
    constructor() {
        this.init();
    }

    init() {
        // Handle close buttons for existing messages
        this.handleCloseButtons();

        // Auto-hide success messages after 5 seconds
        this.autoHideMessages();
    }

    handleCloseButtons() {
        const closeButtons = document.querySelectorAll('[data-dismiss="alert"]');
        closeButtons.forEach((button) => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                this.dismissAlert(button.closest(".alert"));
            });
        });
    }

    autoHideMessages() {
        const successAlerts = document.querySelectorAll(".alert-success");
        successAlerts.forEach((alert) => {
            setTimeout(() => {
                if (alert.parentNode) {
                    this.dismissAlert(alert);
                }
            }, 5000); // Auto-hide after 5 seconds
        });
    }

    dismissAlert(alertElement) {
        if (!alertElement) return;

        alertElement.classList.add("fade-out");
        setTimeout(() => {
            if (alertElement.parentNode) {
                alertElement.parentNode.removeChild(alertElement);
            }
        }, 300); // Match the animation duration
    }

    // Method to show messages dynamically via JavaScript
    showMessage(message, type = "success") {
        const container = document.querySelector("main.container") || document.body;
        const flashContainer = container.querySelector(".flash-messages") || this.createFlashContainer(container);

        const alertElement = this.createAlertElement(message, type);
        flashContainer.appendChild(alertElement);

        // Auto-hide success messages
        if (type === "success") {
            setTimeout(() => {
                this.dismissAlert(alertElement);
            }, 5000);
        }

        return alertElement;
    }

    createFlashContainer(parent) {
        const container = document.createElement("div");
        container.className = "flash-messages";

        // Insert after header or at the beginning of main
        const main = parent.querySelector("main") || parent;
        const firstChild = main.firstElementChild;
        if (firstChild) {
            main.insertBefore(container, firstChild);
        } else {
            main.appendChild(container);
        }

        return container;
    }

    createAlertElement(message, type) {
        const alertDiv = document.createElement("div");
        alertDiv.className = `alert alert-${type}`;
        alertDiv.setAttribute("data-alert", type);

        alertDiv.innerHTML = `
            ${this.escapeHtml(message)}
            <button type="button" class="close-btn" data-dismiss="alert">&times;</button>
        `;

        // Add event listener to the close button
        const closeBtn = alertDiv.querySelector('[data-dismiss="alert"]');
        closeBtn.addEventListener("click", (e) => {
            e.preventDefault();
            this.dismissAlert(alertDiv);
        });

        return alertDiv;
    }

    escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }

    // Static methods for easy access
    static showError(message) {
        return window.flashMessages.showMessage(message, "error");
    }

    static showSuccess(message) {
        return window.flashMessages.showMessage(message, "success");
    }
}

// Form validation and message handling
class FormHandler {
    constructor() {
        this.init();
    }

    init() {
        // Handle all forms with validation
        const forms = document.querySelectorAll("form");
        forms.forEach((form) => {
            this.setupFormValidation(form);
        });
    }

    setupFormValidation(form) {
        const fields = form.querySelectorAll("input, textarea, select");

        fields.forEach((field) => {
            // Real-time validation
            field.addEventListener("blur", () => this.validateField(field));
            field.addEventListener("input", () => this.clearFieldError(field));
        });

        // Form submission handling
        form.addEventListener("submit", (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
                FlashMessages.showError("Veuillez corriger les erreurs dans le formulaire.");
            }
        });
    }

    validateField(field) {
        const fieldContainer = field.closest(".field");
        const errorElement = fieldContainer?.querySelector(".field__error");

        if (!errorElement) return true;

        let isValid = true;
        let errorMessage = "";

        // Basic validation rules
        if (field.hasAttribute("required") && !field.value.trim()) {
            isValid = false;
            errorMessage = "Ce champ est obligatoire.";
        } else if (field.type === "email" && field.value && !this.isValidEmail(field.value)) {
            isValid = false;
            errorMessage = "Veuillez entrer une adresse e-mail valide.";
        } else if (field.name === "confirm" && field.value) {
            const passwordField = field.form.querySelector('input[name="password"]');
            if (passwordField && field.value !== passwordField.value) {
                isValid = false;
                errorMessage = "Les mots de passe ne correspondent pas.";
            }
        }

        // Show/hide error
        if (!isValid) {
            this.showFieldError(fieldContainer, errorElement, errorMessage);
        } else {
            this.hideFieldError(fieldContainer, errorElement);
        }

        return isValid;
    }

    validateForm(form) {
        const fields = form.querySelectorAll("input, textarea, select");
        let isValid = true;

        fields.forEach((field) => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    showFieldError(fieldContainer, errorElement, message) {
        fieldContainer.classList.add("field--error");
        errorElement.textContent = message;
        errorElement.style.display = "block";
    }

    hideFieldError(fieldContainer, errorElement) {
        fieldContainer.classList.remove("field--error");
        errorElement.style.display = "none";
    }

    clearFieldError(field) {
        const fieldContainer = field.closest(".field");
        const errorElement = fieldContainer?.querySelector(".field__error");

        if (fieldContainer?.classList.contains("field--error")) {
            this.hideFieldError(fieldContainer, errorElement);
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    window.flashMessages = new FlashMessages();
    window.formHandler = new FormHandler();
});

// Export for use in other scripts
if (typeof module !== "undefined" && module.exports) {
    module.exports = { FlashMessages, FormHandler };
}
