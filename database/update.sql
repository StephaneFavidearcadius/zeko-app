-- ============================================
-- Zeko.app - Mise à jour de la base de données
-- À exécuter dans phpMyAdmin
-- ============================================

-- 1. Ajouter la colonne token à la table downloads
ALTER TABLE downloads ADD COLUMN token VARCHAR(64) NULL AFTER user_agent;

-- 2. Ajouter la colonne expires_at à la table downloads
ALTER TABLE downloads ADD COLUMN expires_at DATETIME NULL AFTER token;

-- 3. Ajouter la colonne downloads_count à la table products
ALTER TABLE downloads ADD COLUMN downloads_count INT DEFAULT 0 AFTER user_agent;

-- 4. Ajouter la colonne downloads_count à la table products (si pas déjà présent)
ALTER TABLE products ADD COLUMN downloads_count INT DEFAULT 0 AFTER sales_count;

-- 5. Vérifier que la table downloads a toutes les colonnes nécessaires
-- Si vous voulez recréer la table complète, voici la structure :
-- (Mais normalement avec les ALTER ci-dessus c'est bon)

-- 6. Optionnel : Ajouter un index pour les performances
CREATE INDEX idx_downloads_product ON downloads(product_id);
CREATE INDEX idx_downloads_user ON downloads(user_id);
CREATE INDEX idx_downloads_date ON downloads(downloaded_at);