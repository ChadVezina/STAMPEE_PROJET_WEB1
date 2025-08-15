// Password visibility toggle functionality
class PasswordToggle {
    constructor() {
        this.init();
    }

    init() {
        // Find all password toggle buttons
        const toggleButtons = document.querySelectorAll(".field__toggle-password");

        toggleButtons.forEach((button) => {
            this.setupToggle(button);
        });
    }

    setupToggle(button) {
        const inputGroup = button.closest(".field__input-group");
        const passwordInput = inputGroup.querySelector('input[type="password"], input[type="text"]');
        const toggleIcon = button.querySelector(".toggle-icon");

        if (!passwordInput || !toggleIcon) return;

        // Set initial state
        let isVisible = false;
        this.updateToggleState(passwordInput, toggleIcon, isVisible);

        // Add click event listener
        button.addEventListener("click", (e) => {
            e.preventDefault();
            isVisible = !isVisible;
            this.updateToggleState(passwordInput, toggleIcon, isVisible);
        });
    }

    updateToggleState(input, icon, isVisible) {
        if (isVisible) {
            input.type = "text";
            icon.textContent = "🙈"; // Hide icon
            icon.setAttribute("aria-label", "Masquer le mot de passe");
        } else {
            input.type = "password";
            icon.textContent = "👁️"; // Show icon
            icon.setAttribute("aria-label", "Afficher le mot de passe");
        }
    }
}

// Auto-initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    if (document.querySelector(".field__toggle-password")) {
        window.passwordToggle = new PasswordToggle();
    }
});

// Export for module systems
if (typeof module !== "undefined" && module.exports) {
    module.exports = PasswordToggle;
}
