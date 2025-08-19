/**
 * Shared Form Utilities
 * Modular JavaScript functions for form handling
 */

// Image Upload Handler Class
class ImageUploadHandler {
    constructor(options = {}) {
        this.imageInput = document.getElementById(options.inputId || "stamp_images");
        this.imagePreview = document.getElementById(options.previewId || "image-preview");
        this.previewContainer = document.querySelector(options.previewSelector || ".preview-container");
        this.maxFiles = options.maxFiles || 5;
        this.maxSize = options.maxSize || 5 * 1024 * 1024; // 5MB
        this.allowedExt = options.allowedExt || ["jpg", "jpeg", "png", "gif", "webp", "jfif", "bmp", "tiff", "tif", "svg", "ico", "avif"];
        this.allowedTypes = options.allowedTypes || [
            "image/jpeg",
            "image/png",
            "image/gif",
            "image/webp",
            "image/bmp",
            "image/tiff",
            "image/svg+xml",
            "image/x-icon",
            "image/avif",
        ];

        this.selectedFiles = [];
        this.init();
    }

    init() {
        if (!this.imageInput) return;

        this.imageInput.addEventListener("change", (e) => this.handleFileSelection(e));

        // Make removeImage globally available
        window.removeImage = (index) => this.removeImage(index);
    }

    handleFileSelection(event) {
        const files = Array.from(event.target.files);

        // Check if adding these files would exceed the maximum
        if (this.selectedFiles.length + files.length > this.maxFiles) {
            alert(`Vous ne pouvez avoir que ${this.maxFiles} images maximum. Vous avez déjà ${this.selectedFiles.length} image(s).`);
            event.target.value = "";
            return;
        }

        const validFiles = [];
        for (let file of files) {
            const ext = file.name.split(".").pop().toLowerCase();
            if (!this.allowedExt.includes(ext) || !this.allowedTypes.includes(file.type)) {
                alert(`L'image "${file.name}" n'est pas un format pris en charge.`);
                continue;
            }
            if (file.size > this.maxSize) {
                alert(`L'image "${file.name}" est trop volumineuse (max ${this.formatFileSize(this.maxSize)}).`);
                continue;
            }
            validFiles.push(file);
        }

        if (validFiles.length === 0) {
            event.target.value = "";
            return;
        }

        // Add new files to existing selection instead of replacing
        this.selectedFiles = [...this.selectedFiles, ...validFiles];

        // Show feedback about the selection
        if (validFiles.length > 0) {
            const totalFiles = this.selectedFiles.length;
            console.log(`${validFiles.length} image(s) ajoutée(s). Total: ${totalFiles}/${this.maxFiles}`);
        }

        // Update the file input to include all selected files
        this.updateFileInput();
        this.displayPreviews();
    }

    displayPreviews() {
        if (!this.imagePreview || !this.previewContainer) return;

        // Update counter
        this.updateImageCounter();

        if (this.selectedFiles.length === 0) {
            this.imagePreview.style.display = "none";
            return;
        }

        this.imagePreview.style.display = "block";
        this.previewContainer.innerHTML = "";

        this.selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const previewItem = document.createElement("div");
                previewItem.className = "preview-item" + (index === 0 ? " main-image" : "");
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Aperçu ${index + 1}">
                    ${index === 0 ? '<span class="badge">Principale</span>' : ""}
                    <button type="button" class="remove-btn" onclick="removeImage(${index})">×</button>
                `;
                this.previewContainer.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });
    }

    updateImageCounter() {
        const counter = document.getElementById("image-counter");
        if (counter) {
            counter.textContent = `(${this.selectedFiles.length}/${this.maxFiles})`;
            counter.className = this.selectedFiles.length >= this.maxFiles ? "text-warning" : "text-muted";
        }
    }

    removeImage(index) {
        this.selectedFiles.splice(index, 1);
        this.updateFileInput();
        this.displayPreviews();

        // Clear file input value if no files left to allow re-selection of same files
        if (this.selectedFiles.length === 0 && this.imageInput) {
            this.imageInput.value = "";
        }
    }

    updateFileInput() {
        // Update file input with current selected files
        const dt = new DataTransfer();
        this.selectedFiles.forEach((file) => dt.items.add(file));
        if (this.imageInput) this.imageInput.files = dt.files;
    }

    formatFileSize(bytes) {
        if (bytes === 0) return "0 Bytes";
        const k = 1024;
        const sizes = ["Bytes", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    }
}

// Auction Form Handler Class - For required auction fields
class AuctionFormHandler {
    constructor(options = {}) {
        this.fieldsId = options.fieldsId || "auction-fields";
        this.formSelector = options.formSelector || ".form";

        this.fields = document.getElementById(this.fieldsId);
        this.form = document.querySelector(this.formSelector);

        this.init();
    }

    init() {
        if (!this.fields) return;

        // Set default dates on page load since auction is always required
        this.setDefaultDates();

        if (this.form) {
            this.form.addEventListener("submit", (e) => this.validateForm(e));
        }
    }

    setDefaultDates() {
        const now = new Date();
        const start = new Date(now.getTime() + 24 * 60 * 60 * 1000); // Tomorrow
        const end = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000); // Next week

        const startInput = document.querySelector('input[name="auction_start"]');
        const endInput = document.querySelector('input[name="auction_end"]');

        if (startInput && !startInput.value) {
            // Format for datetime-local input (YYYY-MM-DDTHH:MM) in local timezone
            startInput.value = this.formatDateTimeLocal(start);
        }
        if (endInput && !endInput.value) {
            endInput.value = this.formatDateTimeLocal(end);
        }
    }

    // Helper function to format date for datetime-local input in local timezone
    formatDateTimeLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");
        const hours = String(date.getHours()).padStart(2, "0");
        const minutes = String(date.getMinutes()).padStart(2, "0");
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    validateForm(event) {
        const startInput = document.querySelector('input[name="auction_start"]');
        const endInput = document.querySelector('input[name="auction_end"]');
        const minPriceInput = document.querySelector('input[name="min_price"]');

        // All auction fields are required now
        if (startInput && endInput) {
            const startDate = new Date(startInput.value);
            const endDate = new Date(endInput.value);
            const now = new Date();

            if (startDate.getTime() < now.getTime() + 60 * 1000) {
                event.preventDefault();
                alert("La date de début doit être dans le futur.");
                startInput.focus();
                return false;
            }

            if (endDate <= startDate) {
                event.preventDefault();
                alert("La date de fin doit être postérieure à la date de début de l'enchère.");
                endInput.focus();
                return false;
            }
        }

        if (minPriceInput && (!minPriceInput.value || parseFloat(minPriceInput.value) <= 0)) {
            event.preventDefault();
            alert("Le prix de départ doit être supérieur à 0.");
            minPriceInput.focus();
            return false;
        }

        return true;
    }
}

// Form Utilities
const FormUtils = {
    // Initialize common form handlers
    initializeStampForm: function (options = {}) {
        // Initialize image upload handler
        new ImageUploadHandler(options.imageUpload || {});

        // Initialize auction form handler (required fields)
        new AuctionFormHandler(options.auctionForm || {});
    },

    // Utility to set minimum datetime for inputs
    setMinDateTime: function (selector, minutesFromNow = 1) {
        const input = document.querySelector(selector);
        if (input) {
            const now = new Date();
            const minDate = new Date(now.getTime() + minutesFromNow * 60 * 1000);
            // Use local time formatting instead of UTC
            const year = minDate.getFullYear();
            const month = String(minDate.getMonth() + 1).padStart(2, "0");
            const day = String(minDate.getDate()).padStart(2, "0");
            const hours = String(minDate.getHours()).padStart(2, "0");
            const minutes = String(minDate.getMinutes()).padStart(2, "0");
            input.min = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    },

    // Utility to validate required fields
    validateRequiredFields: function (selectors) {
        for (let selector of selectors) {
            const field = document.querySelector(selector);
            if (field && !field.value.trim()) {
                field.focus();
                return false;
            }
        }
        return true;
    },
};

// Export for module usage
if (typeof module !== "undefined" && module.exports) {
    module.exports = { ImageUploadHandler, AuctionFormHandler, FormUtils };
}
