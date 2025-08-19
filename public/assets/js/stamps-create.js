/**
 * Stamps Create Page JavaScript
 * Uses shared form utilities for modularity with required auctions
 */

document.addEventListener("DOMContentLoaded", function () {
    // Initialize form handlers using shared utilities
    FormUtils.initializeStampForm({
        imageUpload: {
            inputId: "stamp_images",
            previewId: "image-preview",
            previewSelector: ".preview-container",
            maxFiles: 5,
            maxSize: 5 * 1024 * 1024, // 5MB
        },
        auctionForm: {
            fieldsId: "auction-fields",
            formSelector: ".form--stamps-create",
        },
    });

    // Set minimum datetime for auction inputs
    FormUtils.setMinDateTime('input[name="auction_start"]', 1);
});
