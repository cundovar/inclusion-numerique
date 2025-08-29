-- Script pour vérifier les prérequis avant de créer des analyses IA

-- Vérifier qu'il y a des PageContent
SELECT 
    'PageContent' as table_name,
    COUNT(*) as count,
    'Cours disponibles pour analyse' as description
FROM page_content;

-- Vérifier qu'il y a des utilisateurs  
SELECT 
    'Users' as table_name,
    COUNT(*) as count,
    'Utilisateurs pour les analytics' as description
FROM `appy_User`;

-- Vérifier qu'il y a des catégories
SELECT 
    'Categories' as table_name,
    COUNT(*) as count,
    'Catégories pour organiser les parcours' as description  
FROM category;

-- Lister quelques cours existants pour référence
SELECT 
    id,
    title,
    LEFT(content, 100) as content_preview
FROM page_content 
LIMIT 5;