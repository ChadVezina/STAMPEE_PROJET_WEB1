/**
 * Stamp Show Page JavaScript
 * Handles image switching and countdown functionality for stamp detail pages
 */

// Image switching functionality
function switchMainImage(imageUrl) {
    const mainImage = document.querySelector(".main-image");
    if (mainImage) {
        mainImage.src = imageUrl;
    }

    // Update active thumbnail
    document.querySelectorAll(".thumbnail").forEach((thumb) => {
        thumb.classList.remove("thumbnail--active");
    });
    event.target.closest(".thumbnail").classList.add("thumbnail--active");
}

document.addEventListener("DOMContentLoaded", function () {
    const thumbnails = document.querySelectorAll(".stamp-detail__thumbnail");
    const mainImage = document.querySelector(".stamp-detail__main-image img");

    thumbnails.forEach((thumb) => {
        thumb.addEventListener("click", function () {
            const img = this.querySelector("img");
            if (mainImage && img) {
                mainImage.src = img.src;

                // Update active state
                thumbnails.forEach((t) => t.classList.remove("stamp-detail__thumbnail--active"));
                this.classList.add("stamp-detail__thumbnail--active");
            }
        });
    });
});

// Countdown Timer Functionality
function updateCountdown() {
    const countdownElement = document.querySelector(".countdown-timer");
    if (!countdownElement) return;

    const timeRemaining = countdownElement.closest(".time-remaining");
    const endTime = new Date(timeRemaining.getAttribute("data-end-time")).getTime();
    const now = new Date().getTime();
    const distance = endTime - now;

    if (distance < 0) {
        countdownElement.innerHTML = "Enchère terminée";
        countdownElement.classList.add("urgent");
        return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    let timeString = "";
    if (days > 0) {
        timeString = `${days}j ${hours}h ${minutes}m ${seconds}s`;
    } else if (hours > 0) {
        timeString = `${hours}h ${minutes}m ${seconds}s`;
    } else if (minutes > 0) {
        timeString = `${minutes}m ${seconds}s`;
    } else {
        timeString = `${seconds}s`;
        countdownElement.classList.add("urgent");
    }

    // Add urgent class when less than 1 hour remains
    if (distance < 3600000) {
        // 1 hour in milliseconds
        countdownElement.classList.add("urgent");
    }

    countdownElement.innerHTML = timeString;
}

// Start countdown if there's an active auction
if (document.querySelector(".countdown-timer")) {
    updateCountdown(); // Initial update
    setInterval(updateCountdown, 1000); // Update every second
}
