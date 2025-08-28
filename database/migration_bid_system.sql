-- Migration pour créer la table Bid et améliorer le système d'enchères
-- Exécuter ce script pour mettre à jour la base de données

-- 1. Créer la table Bid si elle n'existe pas
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

-- 2. Créer les index pour optimiser les performances
CREATE INDEX IF NOT EXISTS `idx_bid_auction` ON `Bid`(`auction_id`);
CREATE INDEX IF NOT EXISTS `idx_bid_user` ON `Bid`(`user_id`);
CREATE INDEX IF NOT EXISTS `idx_bid_price` ON `Bid`(`auction_id`, `price` DESC);
CREATE INDEX IF NOT EXISTS `idx_bid_time` ON `Bid`(`auction_id`, `bid_at` DESC);
CREATE INDEX IF NOT EXISTS `idx_bid_active` ON `Bid`(`is_active`, `auction_id`);

-- 3. Ajouter des contraintes pour améliorer l'intégrité des données
-- Note: Cette contrainte unique empêche un utilisateur d'avoir plusieurs offres actives sur la même enchère
-- Si cette contrainte pose problème, la commenter
-- CREATE UNIQUE INDEX IF NOT EXISTS `idx_bid_user_auction_active` ON `Bid`(`auction_id`, `user_id`) WHERE `is_active` = 1;

-- 4. Mettre à jour la table User si nécessaire (ajouter nom complet)
ALTER TABLE `User` ADD COLUMN IF NOT EXISTS `nom` VARCHAR(100) 
    GENERATED ALWAYS AS (CONCAT(`first_name`, ' ', `last_name`)) STORED;

-- 5. Ajouter des contraintes supplémentaires à la table Auction pour plus de sécurité
ALTER TABLE `Auction` ADD CONSTRAINT IF NOT EXISTS `chk_auction_min_price` CHECK (`min_price` > 0);
ALTER TABLE `Auction` ADD CONSTRAINT IF NOT EXISTS `chk_auction_dates` CHECK (`auction_end` > `auction_start`);

-- 6. Insérer des données de test pour les offres (optionnel - adapter selon vos besoins)
-- Attention: Vérifiez que les IDs d'utilisateurs et d'enchères existent avant d'exécuter ces INSERT

-- INSERT INTO `Bid` (`auction_id`, `user_id`, `price`, `bid_at`, `is_active`) VALUES
-- (1, 2, 30.00, '2025-08-21 10:30:00', 1),
-- (1, 3, 35.00, '2025-08-21 14:15:00', 1),
-- (1, 2, 40.00, '2025-08-21 16:45:00', 0), -- Ancienne offre de l'utilisateur 2
-- (1, 2, 42.50, '2025-08-21 17:00:00', 1), -- Nouvelle offre de l'utilisateur 2
-- (2, 1, 80.00, '2025-08-22 09:00:00', 1),
-- (2, 3, 85.00, '2025-08-22 11:30:00', 1),
-- (3, 2, 20.00, '2025-08-20 15:00:00', 1),
-- (4, 1, 50.00, '2025-08-23 10:00:00', 1);

-- 7. Créer une vue pour faciliter les requêtes sur les enchères actives avec offres
CREATE OR REPLACE VIEW `ActiveAuctionsWithBids` AS
SELECT 
    a.id AS auction_id,
    a.stamp_id,
    a.seller_id,
    a.auction_start,
    a.auction_end,
    a.min_price,
    a.favorite,
    s.name AS stamp_name,
    u.nom AS seller_name,
    COALESCE(MAX(b.price), a.min_price) AS current_price,
    COUNT(DISTINCT CASE WHEN b.is_active = 1 THEN b.user_id END) AS active_bidders,
    COUNT(CASE WHEN b.is_active = 1 THEN b.id END) AS total_active_bids
FROM `Auction` a
JOIN `Stamp` s ON s.id = a.stamp_id
JOIN `User` u ON u.id = a.seller_id
LEFT JOIN `Bid` b ON b.auction_id = a.id AND b.is_active = 1
WHERE NOW() BETWEEN a.auction_start AND a.auction_end
GROUP BY a.id, a.stamp_id, a.seller_id, a.auction_start, a.auction_end, a.min_price, a.favorite, s.name, u.nom;

-- 8. Créer une fonction pour calculer le seuil minimum pour une nouvelle offre
DELIMITER $$
CREATE FUNCTION IF NOT EXISTS GetMinimumBid(auction_id INT) 
RETURNS DECIMAL(10,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE min_price DECIMAL(10,2);
    DECLARE max_bid DECIMAL(10,2);
    
    -- Récupérer le prix minimum de l'enchère
    SELECT a.min_price INTO min_price 
    FROM `Auction` a 
    WHERE a.id = auction_id;
    
    -- Récupérer la plus haute offre active
    SELECT MAX(b.price) INTO max_bid 
    FROM `Bid` b 
    WHERE b.auction_id = auction_id AND b.is_active = 1;
    
    -- Retourner le maximum entre le prix minimum et la plus haute offre + 0.01
    RETURN GREATEST(IFNULL(min_price, 0), IFNULL(max_bid, 0)) + 0.01;
END$$
DELIMITER ;

-- 9. Créer une procédure stockée pour placer une offre (validation côté DB)
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS PlaceBid(
    IN p_auction_id INT,
    IN p_user_id INT,
    IN p_price DECIMAL(10,2),
    OUT p_success BOOLEAN,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_seller_id INT;
    DECLARE v_auction_start DATETIME;
    DECLARE v_auction_end DATETIME;
    DECLARE v_min_required DECIMAL(10,2);
    DECLARE v_existing_bid_count INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_success = FALSE;
        SET p_message = 'Erreur lors du placement de l\'offre';
    END;
    
    START TRANSACTION;
    
    -- Vérifier que l'enchère existe et récupérer les informations
    SELECT seller_id, auction_start, auction_end 
    INTO v_seller_id, v_auction_start, v_auction_end
    FROM `Auction` 
    WHERE id = p_auction_id;
    
    IF v_seller_id IS NULL THEN
        SET p_success = FALSE;
        SET p_message = 'Enchère introuvable';
        ROLLBACK;
    ELSEIF v_seller_id = p_user_id THEN
        SET p_success = FALSE;
        SET p_message = 'Vous ne pouvez pas miser sur votre propre enchère';
        ROLLBACK;
    ELSEIF NOW() < v_auction_start THEN
        SET p_success = FALSE;
        SET p_message = 'L\'enchère n\'a pas encore commencé';
        ROLLBACK;
    ELSEIF NOW() > v_auction_end THEN
        SET p_success = FALSE;
        SET p_message = 'L\'enchère est terminée';
        ROLLBACK;
    ELSE
        -- Calculer le montant minimum requis
        SET v_min_required = GetMinimumBid(p_auction_id);
        
        IF p_price < v_min_required THEN
            SET p_success = FALSE;
            SET p_message = CONCAT('Offre insuffisante. Minimum requis: ', v_min_required, ' $ CAD');
            ROLLBACK;
        ELSE
            -- Désactiver les offres précédentes de cet utilisateur sur cette enchère
            UPDATE `Bid` 
            SET is_active = 0 
            WHERE auction_id = p_auction_id AND user_id = p_user_id AND is_active = 1;
            
            -- Insérer la nouvelle offre
            INSERT INTO `Bid` (auction_id, user_id, price, bid_at, is_active)
            VALUES (p_auction_id, p_user_id, p_price, NOW(), 1);
            
            SET p_success = TRUE;
            SET p_message = 'Offre placée avec succès';
            COMMIT;
        END IF;
    END IF;
END$$
DELIMITER ;

-- 10. Ajouter des triggers pour maintenir l'intégrité des données
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `bid_before_insert` 
BEFORE INSERT ON `Bid`
FOR EACH ROW
BEGIN
    -- Vérifier que le prix est positif
    IF NEW.price <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Le prix de l\'offre doit être positif';
    END IF;
    
    -- Vérifier que l'utilisateur n'est pas le vendeur
    IF EXISTS (
        SELECT 1 FROM `Auction` a 
        WHERE a.id = NEW.auction_id AND a.seller_id = NEW.user_id
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Le vendeur ne peut pas miser sur sa propre enchère';
    END IF;
END$$
DELIMITER ;
