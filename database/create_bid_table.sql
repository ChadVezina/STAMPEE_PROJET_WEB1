-- Create Bid table for auction bids
-- This table stores all bids placed on auctions

CREATE TABLE IF NOT EXISTS `Bid` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `auction_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `bid_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `is_active` BOOLEAN DEFAULT TRUE,
    CONSTRAINT `fk_bid_auction` FOREIGN KEY (`auction_id`) REFERENCES `Auction`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bid_user` FOREIGN KEY (`user_id`) REFERENCES `User`(`id`) ON DELETE CASCADE,
    CONSTRAINT `chk_bid_price_positive` CHECK (`price` > 0)
);

-- Create indexes for better performance
CREATE INDEX `idx_bid_auction` ON `Bid`(`auction_id`);
CREATE INDEX `idx_bid_user` ON `Bid`(`user_id`);
CREATE INDEX `idx_bid_price` ON `Bid`(`auction_id`, `price` DESC);
CREATE INDEX `idx_bid_time` ON `Bid`(`auction_id`, `bid_at` DESC);

-- Ensure unique constraint: one user can only have one active bid per auction
CREATE UNIQUE INDEX `idx_bid_user_auction_active` ON `Bid`(`auction_id`, `user_id`, `is_active`);
