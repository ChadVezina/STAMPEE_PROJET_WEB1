-- Migration to add role system to users
-- This will add a role column to the user table

-- Add role column to user table
ALTER TABLE `user` ADD COLUMN `role` ENUM('user', 'admin', 'super_admin') DEFAULT 'user' AFTER `password`;

-- Update user with ID 1 to be super admin (The Lord)
UPDATE `user` SET `role` = 'super_admin' WHERE `id` = 1;

-- Add index for better performance
CREATE INDEX `idx_user_role` ON `user`(`role`);
