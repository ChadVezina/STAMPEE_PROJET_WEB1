-- Stampee Database Schema
-- Create the database tables based on your MySQL model

-- Create Country table first (referenced by Stamp)
CREATE TABLE IF NOT EXISTS `Country` (
    `iso2` CHAR(2) PRIMARY KEY,
    `name_fr` VARCHAR(100) NOT NULL,
    `name_en` VARCHAR(100) NOT NULL
);

-- Insert full list of ISO 3166-1 alpha-2 countries
-- Note: name_fr mirrors the English name when a French translation is not provided.


-- Create User table (if not exists)
CREATE TABLE IF NOT EXISTS `User` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Stamp table
CREATE TABLE IF NOT EXISTS `Stamp` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `user_id` INT NOT NULL,
    `created_at` DATE NULL,
    `country_code` CHAR(2) NULL,
    `width_mm` DECIMAL(6,2) NULL,
    `height_mm` DECIMAL(6,2) NULL,
    `current_state` ENUM('Parfaite','Excellente','Bonne','Moyenne','Endommagée') NULL,
    `nbr_stamps` INT NULL,
    `dimensions` VARCHAR(50) NULL,
    `certified` BOOLEAN DEFAULT FALSE,
    CONSTRAINT `fk_stamp_country` FOREIGN KEY (`country_code`) REFERENCES `Country`(`iso2`) ON DELETE SET NULL,
    CONSTRAINT `fk_stamp_user` FOREIGN KEY (`user_id`) REFERENCES `User`(`id`) ON DELETE CASCADE
);

-- Create StampImage table
CREATE TABLE IF NOT EXISTS `StampImage` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `stamp_id` INT NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `is_main` BOOLEAN DEFAULT FALSE,
    CONSTRAINT `fk_stampimage_stamp` FOREIGN KEY (`stamp_id`) REFERENCES `Stamp`(`id`) ON DELETE CASCADE
);

-- Create Auction table
CREATE TABLE IF NOT EXISTS `Auction` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `stamp_id` INT NOT NULL,
    `seller_id` INT NOT NULL,
    `auction_start` DATETIME NOT NULL,
    `auction_end` DATETIME NOT NULL,
    `min_price` DECIMAL(10,2) NOT NULL,
    `favorite` BOOLEAN DEFAULT FALSE,
    CHECK (`auction_end` > `auction_start`),
    CONSTRAINT `fk_auction_stamp` FOREIGN KEY (`stamp_id`) REFERENCES `Stamp`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_auction_seller` FOREIGN KEY (`seller_id`) REFERENCES `User`(`id`) ON DELETE CASCADE
);



-- Create indexes for better performance
CREATE INDEX `idx_stamp_country` ON `Stamp`(`country_code`);
CREATE INDEX `idx_stamp_state` ON `Stamp`(`current_state`);
CREATE INDEX `idx_stamp_user` ON `Stamp`(`user_id`);
CREATE INDEX `idx_stampimage_stamp` ON `StampImage`(`stamp_id`);
CREATE INDEX `idx_stampimage_main` ON `StampImage`(`stamp_id`, `is_main`);
CREATE INDEX `idx_auction_stamp` ON `Auction`(`stamp_id`);
CREATE INDEX `idx_auction_seller` ON `Auction`(`seller_id`);
CREATE INDEX `idx_auction_dates` ON `Auction`(`auction_start`, `auction_end`);