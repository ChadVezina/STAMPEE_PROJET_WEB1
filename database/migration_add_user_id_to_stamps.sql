-- Migration: Add user_id to Stamp table
-- This links each stamp to the user who created it

-- Add user_id column to Stamp table
ALTER TABLE `Stamp` ADD COLUMN `user_id` INT NOT NULL DEFAULT 1;

-- Add foreign key constraint
ALTER TABLE `Stamp` ADD CONSTRAINT `fk_stamp_user` 
FOREIGN KEY (`user_id`) REFERENCES `User`(`id`) ON DELETE CASCADE;

-- Add index for performance
CREATE INDEX `idx_stamp_user` ON `Stamp`(`user_id`);

-- Update existing stamps to be owned by user ID 1 (admin) if exists
-- This is a safe default for existing data
UPDATE `Stamp` SET `user_id` = 1 WHERE `user_id` = 1;
