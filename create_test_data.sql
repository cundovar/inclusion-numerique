-- Script pour créer des données de test pour les analyses IA
-- Exécutez ceci après avoir appliqué la migration

-- Insérer quelques analyses de test
INSERT INTO course_analysis (
    page_content_id, 
    analysis, 
    summary, 
    quality_score, 
    difficulty_level, 
    estimated_reading_time, 
    suggestions, 
    analyzed_at, 
    created_at
) VALUES 
(
    2, -- ID cours "les tableaux et formulaires"
    '{"strengths": ["Structure claire", "Exemples pratiques"], "weaknesses": ["Manque d\'exercices"], "concepts_covered": ["Variables", "Fonctions"]}',
    'Cours bien structuré avec des exemples pratiques mais manque d\'exercices interactifs.',
    7.5,
    'intermediate',
    15,
    '[{"type": "exercises", "priority": "high", "title": "Ajouter des exercices", "description": "Inclure des exercices pratiques pour renforcer l\'apprentissage"}]',
    NOW(),
    NOW()
),
(
    3, -- ID cours "structure d'une page html"  
    '{"strengths": ["Progression logique", "Code bien commenté"], "weaknesses": ["Trop théorique"], "concepts_covered": ["Classes", "Objets"]}',
    'Cours théorique solide mais nécessite plus d\'exemples concrets.',
    6.8,
    'advanced',
    20,
    '[{"type": "examples", "priority": "medium", "title": "Ajouter des exemples", "description": "Inclure plus d\'exemples concrets d\'utilisation"}]',
    NOW(),
    NOW()
);

-- Insérer quelques recommandations de test
INSERT INTO content_recommendation (
    page_content_id,
    type,
    priority,
    title,
    description,
    suggested_content,
    status,
    created_at
) VALUES 
(
    2,
    'exercises',
    'high',
    'Ajouter des exercices pratiques',
    'Ce cours manque d\'exercices interactifs pour consolider l\'apprentissage des concepts.',
    'Créer 3-4 exercices progressifs avec correction automatique.',
    'pending',
    NOW()
),
(
    3,
    'examples',
    'medium',
    'Enrichir avec des exemples concrets',
    'Ajouter des exemples du monde réel pour illustrer les concepts théoriques.',
    'Intégrer des cas d\'usage pratiques dans des projets réels.',
    'pending',
    NOW()
);

-- Insérer un parcours d\'apprentissage de test
INSERT INTO learning_path (
    title,
    description,
    difficulty_level,
    estimated_duration,
    course_sequence,
    learning_objectives,
    status,
    created_at
) VALUES (
    'Parcours Débutant Programmation',
    'Parcours complet pour débuter en programmation avec progression adaptée.',
    'beginner',
    480, -- 8 heures
    '[1, 2, 3]', -- IDs des cours dans l\'ordre
    '["Comprendre les variables", "Maîtriser les fonctions", "Créer des objets"]',
    'active',
    NOW()
);

-- Insérer quelques analytics utilisateur de test (remplacez user_id par un ID existant)
INSERT INTO user_learning_analytics (
    user_id,
    page_content_id,
    event_type,
    time_spent,
    comprehension_score,
    interaction_data,
    event_date,
    created_at
) VALUES 
(
    1, -- Remplacez par un ID d\'utilisateur existant
    1,
    'complete',
    1200, -- 20 minutes
    85.5,
    '{"exercises_completed": 3, "attempts": 1}',
    NOW(),
    NOW()
),
(
    1,
    2,
    'view',
    600, -- 10 minutes
    null,
    '{"scroll_depth": 75, "time_on_section": [120, 180, 300]}',
    NOW() - INTERVAL 1 DAY,
    NOW() - INTERVAL 1 DAY
);