-- Migration pour mettre à jour la table Bid existante
-- Ajouter les colonnes nécessaires pour le système d'enchères amélioré

USE Stampee;

-- 1. Ajouter la colonne is_active à la table Bid existante
ALTER TABLE `Bid` ADD COLUMN `is_active` BOOLEAN DEFAULT TRUE;

-- 2. Ajouter des index pour optimiser les performances
CREATE INDEX IF NOT EXISTS `idx_bid_auction` ON `Bid`(`auction_id`);
CREATE INDEX IF NOT EXISTS `idx_bid_user` ON `Bid`(`user_id`);
CREATE INDEX IF NOT EXISTS `idx_bid_price` ON `Bid`(`auction_id`, `price` DESC);
CREATE INDEX IF NOT EXISTS `idx_bid_time` ON `Bid`(`auction_id`, `bid_at` DESC);
CREATE INDEX IF NOT EXISTS `idx_bid_active` ON `Bid`(`is_active`, `auction_id`);

-- 3. Ajouter des contraintes pour améliorer l'intégrité des données
ALTER TABLE `Bid` ADD CONSTRAINT IF NOT EXISTS `chk_bid_price_positive` CHECK (`price` > 0);

-- 4. Ajouter la colonne user_id à la table Stamp si elle n'existe pas déjà
-- (Vérifiez d'abord si cette colonne existe)
-- ALTER TABLE `Stamp` ADD COLUMN IF NOT EXISTS `user_id` INT NOT NULL;
-- ALTER TABLE `Stamp` ADD CONSTRAINT IF NOT EXISTS `fk_stamp_user` FOREIGN KEY (`user_id`) REFERENCES `User`(`id`) ON DELETE CASCADE;

-- 5. Mettre à jour les données existantes (marquer toutes les offres comme actives)
UPDATE `Bid` SET `is_active` = TRUE WHERE `is_active` IS NULL;

-- 6. Ajouter des contraintes supplémentaires à la table Auction pour plus de sécurité
ALTER TABLE `Auction` ADD CONSTRAINT IF NOT EXISTS `chk_auction_min_price` CHECK (`min_price` > 0);

-- 7. Corriger le nom de la colonne dans la table User si nécessaire
-- Votre schéma utilise 'nom' au lieu de 'first_name' et 'last_name'
-- Assurons-nous que le code fonctionne avec votre structure

-- 8. Optionnel: Créer une vue pour faciliter les requêtes
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
