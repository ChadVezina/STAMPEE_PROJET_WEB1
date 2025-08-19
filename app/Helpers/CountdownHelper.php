<?php

/**
 * Countdown Timer Helper Functions
 * 
 * Provides utility functions for generating countdown timer HTML
 */

/**
 * Generate countdown timer HTML for auction cards
 * 
 * @param string $auctionEndTime - The auction end time (ISO format or MySQL datetime)
 * @param string $cssClass - Optional additional CSS class
 * @return string HTML structure for countdown timer
 */
function generateCardCountdown($auctionEndTime, $cssClass = '')
{
    if (empty($auctionEndTime)) {
        return '';
    }

    $endTimeISO = date('c', strtotime($auctionEndTime));
    $additionalClass = $cssClass ? ' ' . htmlspecialchars($cssClass) : '';

    return '
        <div class="card__time-remaining' . $additionalClass . '" data-end-time="' . htmlspecialchars($endTimeISO) . '">
            <span class="card__countdown">Calcul en cours...</span>
        </div>
    ';
}

/**
 * Generate countdown timer HTML for auction detail pages
 * 
 * @param string $auctionEndTime - The auction end time (ISO format or MySQL datetime)
 * @param string $title - Title for the countdown section (default: "Temps restant")
 * @param string $cssClass - Optional additional CSS class
 * @return string HTML structure for countdown timer
 */
function generateAuctionCountdown($auctionEndTime, $title = 'Temps restant', $cssClass = '')
{
    if (empty($auctionEndTime)) {
        return '';
    }

    $endTimeISO = date('c', strtotime($auctionEndTime));
    $additionalClass = $cssClass ? ' ' . htmlspecialchars($cssClass) : '';

    return '
        <div class="auction-countdown' . $additionalClass . '">
            <h3>' . htmlspecialchars($title) . '</h3>
            <div class="countdown-display" data-end-time="' . htmlspecialchars($endTimeISO) . '">
                <span class="countdown-timer">Calcul en cours...</span>
            </div>
        </div>
    ';
}

/**
 * Generate inline countdown timer
 * 
 * @param string $auctionEndTime - The auction end time (ISO format or MySQL datetime)
 * @param string $cssClass - Optional additional CSS class
 * @return string HTML structure for inline countdown timer
 */
function generateInlineCountdown($auctionEndTime, $cssClass = '')
{
    if (empty($auctionEndTime)) {
        return '<span class="countdown-timer ended">Aucune échéance</span>';
    }

    $endTimeISO = date('c', strtotime($auctionEndTime));
    $additionalClass = $cssClass ? ' ' . htmlspecialchars($cssClass) : '';

    return '
        <span class="countdown-container' . $additionalClass . '" data-end-time="' . htmlspecialchars($endTimeISO) . '">
            <span class="countdown-timer">Calcul en cours...</span>
        </span>
    ';
}

/**
 * Check if an auction is currently active
 * 
 * @param string $auctionStart - The auction start time
 * @param string $auctionEnd - The auction end time
 * @return bool True if auction is active
 */
function isAuctionActive($auctionStart, $auctionEnd)
{
    $now = time();
    $start = strtotime($auctionStart);
    $end = strtotime($auctionEnd);

    return $now >= $start && $now <= $end;
}

/**
 * Get auction status as a string
 * 
 * @param string $auctionStart - The auction start time
 * @param string $auctionEnd - The auction end time
 * @return string Status: 'upcoming', 'active', or 'ended'
 */
function getAuctionStatus($auctionStart, $auctionEnd)
{
    $now = time();
    $start = strtotime($auctionStart);
    $end = strtotime($auctionEnd);

    if ($now < $start) {
        return 'upcoming';
    } elseif ($now <= $end) {
        return 'active';
    } else {
        return 'ended';
    }
}

/**
 * Format time remaining as human-readable string
 * 
 * @param string $auctionEndTime - The auction end time
 * @return string Human-readable time remaining
 */
function getTimeRemaining($auctionEndTime)
{
    $endTime = strtotime($auctionEndTime);
    $now = time();
    $distance = $endTime - $now;

    if ($distance <= 0) {
        return 'Terminée';
    }

    $days = floor($distance / (60 * 60 * 24));
    $hours = floor(($distance % (60 * 60 * 24)) / (60 * 60));
    $minutes = floor(($distance % (60 * 60)) / 60);
    $seconds = $distance % 60;

    if ($days > 0) {
        return "{$days} jour" . ($days > 1 ? 's' : '') . ", {$hours}h {$minutes}m";
    } elseif ($hours > 0) {
        return "{$hours}h {$minutes}m {$seconds}s";
    } elseif ($minutes > 0) {
        return "{$minutes}m {$seconds}s";
    } else {
        return "{$seconds}s";
    }
}
