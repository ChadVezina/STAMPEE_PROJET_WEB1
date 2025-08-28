# Système d'Enchères Stampee - Documentation

## Vue d'ensemble

Le système d'enchères de Stampee permet aux utilisateurs de miser sur des timbres mis en vente par d'autres collectionneurs. Le système implémente une logique d'enchère robuste avec validation côté serveur et client.

## Fonctionnalités principales

### 1. Placement d'offres

-   **Validation en temps réel** : Vérification du montant avant soumission
-   **Suggestions de montants** : Propositions de montants logiques
-   **Prévention des doublons** : Un utilisateur ne peut avoir qu'une offre active par enchère
-   **Validation des règles métier** :
    -   L'offre doit être supérieure au prix minimum
    -   L'offre doit être supérieure à la meilleure offre actuelle
    -   L'utilisateur ne peut pas miser sur sa propre enchère
    -   L'enchère doit être active (dans la période définie)

### 2. Gestion des offres

-   **Retrait d'offres** : Possibilité de retirer une offre tant que l'enchère est active
-   **Historique personnel** : Visualisation de toutes les offres placées
-   **Statut en temps réel** : Indication si l'utilisateur est en tête

### 3. Interface utilisateur avancée

-   **Validation AJAX** : Vérification instantanée de la validité des montants
-   **Actualisation automatique** : Mise à jour des statistiques toutes les 30 secondes
-   **Responsive design** : Interface adaptée mobile et desktop
-   **Feedback visuel** : Indications claires du statut des offres

## Architecture technique

### Modèles

#### Bid.php

-   `create()` : Création d'une nouvelle offre avec validation
-   `validateBid()` : Validation des règles métier
-   `findHighestByAuction()` : Récupération de la meilleure offre
-   `withdraw()` : Retrait d'une offre (suppression logique)

#### Auction.php

-   Gestion des enchères et de leur statut
-   Vérification des périodes d'activité

### Services

#### BidService.php

-   `placeBid()` : Logique complète de placement d'offre
-   `getAuctionStats()` : Statistiques détaillées des enchères
-   `canUserBid()` : Vérification des permissions
-   `getUserWinningAuctions()` : Enchères où l'utilisateur est en tête

#### AuctionService.php

-   `isActive()` : Vérification du statut d'une enchère
-   `getCurrentThreshold()` : Calcul du montant minimum requis

### Contrôleurs

#### BidController.php

-   `store()` : Traitement des nouvelles offres
-   `delete()` : Retrait d'offres
-   `canBid()` : API de vérification des permissions
-   `auctionStats()` : API des statistiques
-   `validateBidAmount()` : Validation AJAX

### Base de données

#### Table Bid

```sql
CREATE TABLE `Bid` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `auction_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `bid_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `is_active` BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (`auction_id`) REFERENCES `Auction`(`id`),
    FOREIGN KEY (`user_id`) REFERENCES `User`(`id`)
);
```

#### Index optimisés

-   Performance des requêtes par enchère
-   Tri par prix décroissant
-   Filtrage par statut actif

### Frontend

#### JavaScript (bid-manager.js)

-   **BidManager** : Classe principale de gestion des enchères
-   **BidSuggestions** : Génération de suggestions de montants
-   **Validation temps réel** : Vérification avant soumission
-   **Actualisation automatique** : Mise à jour des données

#### CSS (bid-system.css)

-   Styles pour les formulaires d'enchères
-   Interface responsive
-   États visuels (validation, erreurs, succès)
-   Animations et transitions

## Règles de validation

### Côté serveur (PHP)

1. **Montant positif** : `price > 0`
2. **Enchère active** : `now BETWEEN auction_start AND auction_end`
3. **Pas le vendeur** : `user_id != seller_id`
4. **Montant suffisant** : `price > getCurrentThreshold()`
5. **Utilisateur authentifié** : Session valide

### Côté client (JavaScript)

1. **Format numérique** : Validation du format de saisie
2. **Montant minimum** : Vérification du seuil
3. **Délai de validation** : Debounce pour éviter les appels excessifs
4. **États visuels** : Feedback immédiat

## Sécurité

### Protection CSRF

-   Token CSRF obligatoire pour toutes les actions de modification
-   Validation du token côté serveur

### Validation des données

-   Sanitisation des entrées utilisateur
-   Typage strict des paramètres
-   Validation des contraintes métier

### Permissions

-   Vérification de l'authentification
-   Contrôle des autorisations (propriétaire de l'offre)
-   Prévention des actions non autorisées

## API Endpoints

### GET /bid/can-bid

Vérifie si un utilisateur peut miser sur une enchère

```json
{
    "can_bid": true,
    "errors": [],
    "minimum_bid": 25.5
}
```

### GET /bid/auction-stats

Récupère les statistiques d'une enchère

```json
{
    "stats": {
        "total_bids": 5,
        "unique_bidders": 3,
        "highest_bid": 45.0,
        "average_bid": 35.2
    },
    "is_active": true,
    "minimum_bid": 45.01
}
```

### POST /bid/validate

Valide un montant d'offre

```json
{
    "valid": false,
    "errors": ["Votre offre doit être supérieure à 45.01 $ CAD"],
    "minimum_bid": 45.01
}
```

## Utilisation

### Pour placer une offre

1. L'utilisateur saisit un montant
2. Validation en temps réel via JavaScript
3. Soumission du formulaire si valide
4. Traitement côté serveur avec validations
5. Redirection avec message de confirmation/erreur

### Pour consulter l'historique

1. Accès via `/bid/history`
2. Affichage des offres actives et passées
3. Indication du statut (en tête, surenchéri, terminé)
4. Actions possibles (voir enchère, retirer offre)

## Optimisations

### Performance

-   Index optimisés sur les colonnes fréquemment utilisées
-   Requêtes SQL optimisées avec JOINs appropriés
-   Mise en cache côté client des données statiques

### Expérience utilisateur

-   Validation temps réel pour éviter les erreurs
-   Suggestions de montants intelligentes
-   Actualisation automatique des données
-   Interface responsive et accessible

## Extensibilité

Le système est conçu pour être facilement extensible :

### Fonctionnalités futures possibles

-   **Enchères automatiques** : Offres automatiques jusqu'à un montant limite
-   **Notifications push** : Alertes en temps réel sur les surenchères
-   **Historique détaillé** : Export des données d'enchères
-   **Statistiques avancées** : Analyses de comportement d'enchères
-   **Enchères groupées** : Lots de timbres
-   **Enchères réservées** : Prix de réserve non publics

### Points d'extension

-   Interface `BidStrategyInterface` pour différents types d'enchères
-   Système d'événements pour déclencher des actions
-   API REST complète pour applications mobiles
-   Intégration avec systèmes de paiement
