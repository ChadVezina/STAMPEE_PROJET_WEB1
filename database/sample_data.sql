-- Sample data for testing the Stampee application
-- Run this after creating the database schema

-- Sample stamps data
INSERT INTO `Stamp` (`name`, `created_at`, `country_code`, `width_mm`, `height_mm`, `current_state`, `nbr_stamps`, `dimensions`, `certified`) VALUES
('Timbre Érable Canadien 1965', '1965-07-01', 'CA', 24.00, 40.00, 'Excellente', 1000000, '24 x 40 mm', TRUE),
('Centenaire Confédération', '1967-01-01', 'CA', 30.00, 25.00, 'Parfaite', 500000, '30 x 25 mm', TRUE),
('Marianne République Française', '1982-03-15', 'FR', 22.00, 36.00, 'Bonne', 2000000, '22 x 36 mm', FALSE),
('Queen Elizabeth II Coronation', '1953-06-02', 'GB', 25.50, 30.00, 'Moyenne', 750000, '25.5 x 30 mm', TRUE),
('Liberty Bell Bicentennial', '1976-07-04', 'US', 26.00, 31.00, 'Excellente', 1200000, '26 x 31 mm', FALSE);

-- Sample stamp images (you would need to upload actual images)
-- These are placeholder URLs - replace with actual uploaded image paths
INSERT INTO `StampImage` (`stamp_id`, `url`, `is_main`) VALUES
(1, '/public/uploads/stamps/stamp_1_main.jpg', TRUE),
(1, '/public/uploads/stamps/stamp_1_detail.jpg', FALSE),
(2, '/public/uploads/stamps/stamp_2_main.jpg', TRUE),
(3, '/public/uploads/stamps/stamp_3_main.jpg', TRUE),
(3, '/public/uploads/stamps/stamp_3_back.jpg', FALSE),
(4, '/public/uploads/stamps/stamp_4_main.jpg', TRUE),
(5, '/public/uploads/stamps/stamp_5_main.jpg', TRUE);

-- Sample auctions (assuming you have users with IDs 1, 2, 3)
-- You'll need to adjust seller_id values based on your actual user data
INSERT INTO `Auction` (`stamp_id`, `seller_id`, `auction_start`, `auction_end`, `min_price`, `favorite`) VALUES
(1, 1, '2025-08-20 10:00:00', '2025-08-27 18:00:00', 25.00, FALSE),
(2, 2, '2025-08-21 09:00:00', '2025-08-28 21:00:00', 75.00, TRUE),
(3, 1, '2025-08-19 14:00:00', '2025-08-26 20:00:00', 15.00, FALSE),
(4, 3, '2025-08-22 11:00:00', '2025-08-29 17:00:00', 45.00, FALSE);

