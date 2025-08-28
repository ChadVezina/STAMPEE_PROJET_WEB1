

/**
 * Initialize countdown timers for elements with countdown classes
 */
function initializeCountdowns() {
    const countdownElements = document.querySelectorAll(".countdown-timer, .card__countdown");

    if (countdownElements.length === 0) return;

    function updateCountdowns() {
        countdownElements.forEach((element) => {
            const container = element.closest("[data-end-time]");
            if (!container) return;

            const endTime = new Date(container.getAttribute("data-end-time")).getTime();
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                element.innerHTML = "Terminée";
                element.classList.add("urgent", "ended");
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let timeString = "";
            const isCardCountdown = element.classList.contains("card__countdown");

            if (days > 0) {
                timeString = isCardCountdown ? `${days}j ${hours}h` : `${days} jour${days > 1 ? "s" : ""}, ${hours}h ${minutes}m ${seconds}s`;
            } else if (hours > 0) {
                timeString = isCardCountdown ? `${hours}h ${minutes}m` : `${hours}h ${minutes}m ${seconds}s`;
            } else if (minutes > 0) {
                timeString = `${minutes}m ${seconds}s`;
            } else {
                timeString = `${seconds}s`;
                element.classList.add("urgent");
            }

            // Add urgent class when less than 1 hour remains
            if (distance < 3600000) {
                // 1 hour in milliseconds
                element.classList.add("urgent");
            }

            element.innerHTML = timeString;
        });
    }

    // Initial update and set interval
    updateCountdowns();
    const intervalId = setInterval(updateCountdowns, 1000);

    // Cleanup function (can be called when component is destroyed)
    return function cleanup() {
        clearInterval(intervalId);
    };
}

// Auto-initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    initializeCountdowns();
});

// Export for manual initialization if needed
window.initializeCountdowns = initializeCountdowns;
