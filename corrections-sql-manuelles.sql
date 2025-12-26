-- ========================================
-- SCRIPT SQL POUR APPLIQUER LES PERMISSIONS MANUELLEMENT
-- Si les seeders ne fonctionnent pas
-- ========================================

-- Étape 1 : Ajouter les nouvelles permissions
INSERT INTO permissions (name, slug, description, module, created_at, updated_at) VALUES
('Voir les statistiques', 'view_statistics', 'Peut voir les statistiques et le dashboard', 'statistics', NOW(), NOW()),
('Gérer les bannières', 'manage_banners', 'Peut gérer les bannières et publicités', 'banners', NOW(), NOW()),
('Gérer le carousel', 'manage_carousel', 'Peut gérer le carousel principal', 'carousel', NOW(), NOW()),
('Gérer les marques', 'manage_brands', 'Peut gérer les marques', 'brands', NOW(), NOW()),
('Gérer les codes promo', 'manage_coupons', 'Peut gérer les codes promo', 'coupons', NOW(), NOW()),
('Gérer les sous-catégories', 'manage_subcategories', 'Peut gérer les sous-catégories', 'subcategories', NOW(), NOW()),
('Gérer les attributs', 'manage_attributes', 'Peut gérer les attributs de produits', 'attributes', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    description = VALUES(description),
    updated_at = NOW();

-- Étape 2 : Récupérer l'ID du rôle Super Admin
SET @super_admin_role_id = (SELECT id FROM roles WHERE slug = 'super-admin' LIMIT 1);
SET @moderator_role_id = (SELECT id FROM roles WHERE slug = 'moderator' LIMIT 1);
SET @support_role_id = (SELECT id FROM roles WHERE slug = 'support' LIMIT 1);

-- Étape 3 : Assigner TOUTES les permissions au Super Admin
INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT @super_admin_role_id, id FROM permissions;

-- Étape 4 : Assigner les permissions au Moderator
INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT @moderator_role_id, id FROM permissions
WHERE module IN (
    'users', 'products', 'orders', 'stores', 'categories', 'subcategories',
    'messages', 'statistics', 'banners', 'carousel', 'brands', 'coupons',
    'attributes', 'payments'
);

-- Étape 5 : Assigner les permissions au Support
INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT @support_role_id, id FROM permissions
WHERE module IN ('orders', 'messages', 'statistics');

-- Vérification
SELECT 
    r.name AS role_name,
    COUNT(rp.permission_id) AS permissions_count
FROM roles r
LEFT JOIN role_permission rp ON r.id = rp.role_id
GROUP BY r.id, r.name;

