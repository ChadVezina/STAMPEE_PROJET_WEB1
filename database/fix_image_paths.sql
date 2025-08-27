-- Fix image paths in the database
-- Remove the '/public' prefix from image URLs
UPDATE `StampImage` 
SET `url` = REPLACE(`url`, '/public/uploads/', '/uploads/') 
WHERE `url` LIKE '/public/uploads/%';
