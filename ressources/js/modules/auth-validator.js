// Authentication form validation module
class AuthValidator {
    constructor() {
        this.validators = {
            nom: this.validateName.bind(this),
            email: this.validateEmail.bind(this),
            password: this.validatePassword.bind(this),
            confirm: this.validatePasswordConfirm.bind(this),
        };
        this.init();
    }

    init() {
        // Initialize validation for login and register forms
        const loginForm = document.getElementById("form-login");
        const registerForm = document.getElementById("form-register");

        if (loginForm) {
            this.setupFormValidation(loginForm);
        }

        if (registerForm) {
            this.setupFormValidation(registerForm);
        }
    }

    setupFormValidation(form) {
        const fields = form.querySelectorAll("input[name]");
        const submitButton = form.querySelector('button[type="submit"]');

        fields.forEach((field) => {
            // Validate on blur (when user leaves field)
            field.addEventListener("blur", () => {
                this.validateField(field);
                this.updateSubmitButton(form, submitButton);
            });

            // Clear errors on input (when user starts typing)
            field.addEventListener("input", () => {
                this.clearFieldError(field);
                // Re-validate after a short delay for real-time feedback
                setTimeout(() => {
                    this.validateField(field);
                    this.updateSubmitButton(form, submitButton);
                }, 300);
            });

            // Special handling for password confirmation
            if (field.name === "password") {
                field.addEventListener("input", () => {
                    const confirmField = form.querySelector('input[name="confirm"]');
                    if (confirmField && confirmField.value) {
                        setTimeout(() => {
                            this.validateField(confirmField);
                            this.updateSubmitButton(form, submitButton);
                        }, 300);
                    }
                });
            }
        });

        // Prevent form submission if validation fails
        form.addEventListener("submit", (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
                this.focusFirstErrorField(form);
            }
        });

        // Initial validation check
        this.updateSubmitButton(form, submitButton);
    }

    validateField(field) {
        const fieldName = field.name;
        const validator = this.validators[fieldName];

        if (!validator) return true;

        const result = validator(field.value, field.form);

        if (result.isValid) {
            this.hideFieldError(field);
        } else {
            this.showFieldError(field, result.message);
        }

        return result.isValid;
    }

    validateForm(form) {
        const fields = form.querySelectorAll("input[name]");
        let isValid = true;

        fields.forEach((field) => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    // Validation methods
    validateName(value) {
        if (!value || value.trim().length === 0) {
            return { isValid: false, message: "Le nom est obligatoire." };
        }

        if (value.trim().length < 2) {
            return { isValid: false, message: "Le nom doit contenir au moins 2 caractères." };
        }

        if (value.trim().length > 50) {
            return { isValid: false, message: "Le nom ne peut pas dépasser 50 caractères." };
        }

        // Check for valid characters (letters, spaces, hyphens, apostrophes)
        const nameRegex = /^[a-zA-ZÀ-ÿ\s\-']+$/;
        if (!nameRegex.test(value.trim())) {
            return { isValid: false, message: "Le nom ne peut contenir que des lettres, espaces, traits d'union et apostrophes." };
        }

        return { isValid: true, message: "" };
    }

    validateEmail(value) {
        if (!value || value.trim().length === 0) {
            return { isValid: false, message: "L'adresse e-mail est obligatoire." };
        }

        // Comprehensive email regex
        const emailRegex =
            /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;

        if (!emailRegex.test(value.trim())) {
            return { isValid: false, message: "Veuillez entrer une adresse e-mail valide." };
        }

        if (value.length > 254) {
            return { isValid: false, message: "L'adresse e-mail est trop longue." };
        }

        return { isValid: true, message: "" };
    }

    validatePassword(value) {
        if (!value || value.length === 0) {
            return { isValid: false, message: "Le mot de passe est obligatoire." };
        }

        if (value.length < 8) {
            return { isValid: false, message: "Le mot de passe doit contenir au moins 8 caractères." };
        }

        if (value.length > 128) {
            return { isValid: false, message: "Le mot de passe ne peut pas dépasser 128 caractères." };
        }

        // Check for at least one uppercase letter
        if (!/[A-Z]/.test(value)) {
            return { isValid: false, message: "Le mot de passe doit contenir au moins une lettre majuscule." };
        }

        // Check for at least one lowercase letter
        if (!/[a-z]/.test(value)) {
            return { isValid: false, message: "Le mot de passe doit contenir au moins une lettre minuscule." };
        }

        // Check for at least one number
        if (!/\d/.test(value)) {
            return { isValid: false, message: "Le mot de passe doit contenir au moins un chiffre." };
        }

        // Check for at least one special character
        if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) {
            return { isValid: false, message: "Le mot de passe doit contenir au moins un caractère spécial." };
        }

        return { isValid: true, message: "" };
    }

    validatePasswordConfirm(value, form) {
        const passwordField = form.querySelector('input[name="password"]');
        const passwordValue = passwordField ? passwordField.value : "";

        if (!value || value.length === 0) {
            return { isValid: false, message: "La confirmation du mot de passe est obligatoire." };
        }

        if (value !== passwordValue) {
            return { isValid: false, message: "Les mots de passe ne correspondent pas." };
        }

        return { isValid: true, message: "" };
    }

    // UI Helper methods
    showFieldError(field, message) {
        const fieldContainer = field.closest(".field");
        const errorElement = fieldContainer.querySelector(".field__error");

        if (fieldContainer && errorElement) {
            fieldContainer.classList.add("field--error");
            errorElement.textContent = message;
            errorElement.style.display = "block";
        }
    }

    hideFieldError(field) {
        const fieldContainer = field.closest(".field");
        const errorElement = fieldContainer.querySelector(".field__error");

        if (fieldContainer && errorElement) {
            fieldContainer.classList.remove("field--error");
            errorElement.style.display = "none";
        }
    }

    clearFieldError(field) {
        this.hideFieldError(field);
    }

    updateSubmitButton(form, submitButton) {
        if (!submitButton) return;

        const isFormValid = this.isFormCurrentlyValid(form);

        if (isFormValid) {
            submitButton.disabled = false;
            submitButton.classList.remove("button--disabled");
        } else {
            submitButton.disabled = true;
            submitButton.classList.add("button--disabled");
        }
    }

    isFormCurrentlyValid(form) {
        const fields = form.querySelectorAll("input[name]");

        // Check if all required fields have values and no error classes
        for (const field of fields) {
            const fieldContainer = field.closest(".field");

            // If field has error class, form is invalid
            if (fieldContainer && fieldContainer.classList.contains("field--error")) {
                return false;
            }

            // If required field is empty, form is invalid
            if (field.hasAttribute("required") && (!field.value || field.value.trim().length === 0)) {
                return false;
            }

            // Run actual validation
            const fieldName = field.name;
            const validator = this.validators[fieldName];
            if (validator && !validator(field.value, form).isValid) {
                return false;
            }
        }

        return true;
    }

    focusFirstErrorField(form) {
        const firstErrorField = form.querySelector(".field--error input");
        if (firstErrorField) {
            firstErrorField.focus();
        }
    }
}

// Export for module systems
if (typeof module !== "undefined" && module.exports) {
    module.exports = AuthValidator;
}
