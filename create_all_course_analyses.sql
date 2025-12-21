-- Script pour créer des analyses IA complètes pour tous les cours avec suggestions concrètes de code

-- Supprimer les analyses existantes pour recommencer
DELETE FROM course_analysis;
DELETE FROM content_recommendation;

-- Analyses détaillées pour chaque cours avec suggestions concrètes de code

-- 1. Les tableaux et formulaires (ID: 2)
INSERT INTO course_analysis (
    page_content_id, analysis, summary, quality_score, difficulty_level, 
    estimated_reading_time, suggestions, analyzed_at, created_at
) VALUES (
    2,
    '{
        "strengths": ["Structure HTML claire", "Exemples de tableaux"],
        "weaknesses": ["Manque d\'exemples avancés", "Pas de CSS pour styling", "Accessibilité insuffisante"],
        "concepts_covered": ["table", "thead", "tbody", "tr", "td", "th"],
        "code_issues": ["Tableaux sans caption", "Headers non associés", "Pas de scope sur th"],
        "accessibility_score": 6.2,
        "seo_score": 7.1
    }',
    'Cours sur les tableaux HTML avec de bons exemples mais manque d\'accessibilité et de styling moderne.',
    7.3,
    'intermediate',
    12,
    '[
        {
            "type": "code_improvement",
            "priority": "high",
            "title": "Améliorer l\'accessibilité des tableaux",
            "description": "Ajouter des éléments caption et scope pour l\'accessibilité",
            "code_suggestion": "<table>\\n  <caption>Liste des étudiants et leurs notes</caption>\\n  <thead>\\n    <tr>\\n      <th scope=\\"col\\">Nom</th>\\n      <th scope=\\"col\\">Note</th>\\n    </tr>\\n  </thead>\\n  <tbody>\\n    <tr>\\n      <td>Martin</td>\\n      <td>15/20</td>\\n    </tr>\\n  </tbody>\\n</table>"
        },
        {
            "type": "css_enhancement",
            "priority": "medium", 
            "title": "Styling moderne des tableaux",
            "description": "Ajouter du CSS pour un rendu professionnel",
            "code_suggestion": "table {\\n  border-collapse: collapse;\\n  width: 100%;\\n  margin: 1rem 0;\\n}\\n\\nth, td {\\n  border: 1px solid #ddd;\\n  padding: 8px 12px;\\n  text-align: left;\\n}\\n\\nth {\\n  background-color: #f5f5f5;\\n  font-weight: bold;\\n}"
        }
    ]',
    NOW(),
    NOW()
),

-- 2. Structure d'une page HTML (ID: 3)  
(
    3,
    '{
        "strengths": ["Structure sémantique correcte", "Explications claires"],
        "weaknesses": ["Manque de meta tags", "Pas d\'exemples complets", "SEO non abordé"],
        "concepts_covered": ["html", "head", "body", "meta", "title"],
        "code_issues": ["DOCTYPE non mentionné", "Lang attribute manquant", "Meta viewport absent"],
        "accessibility_score": 7.8,
        "seo_score": 5.4
    }',
    'Bon cours sur la structure HTML mais manque d\'optimisation SEO et de bonnes pratiques modernes.',
    7.8,
    'beginner',
    10,
    '[
        {
            "type": "code_improvement",
            "priority": "high",
            "title": "Structure HTML5 complète",
            "description": "Ajouter DOCTYPE, lang et meta tags essentiels",
            "code_suggestion": "<!DOCTYPE html>\\n<html lang=\\"fr\\">\\n<head>\\n  <meta charset=\\"UTF-8\\">\\n  <meta name=\\"viewport\\" content=\\"width=device-width, initial-scale=1.0\\">\\n  <meta name=\\"description\\" content=\\"Description de la page\\">\\n  <title>Titre de la page</title>\\n</head>\\n<body>\\n  <header>\\n    <h1>Titre principal</h1>\\n  </header>\\n  <main>\\n    <section>\\n      <h2>Section principale</h2>\\n    </section>\\n  </main>\\n</body>\\n</html>"
        },
        {
            "type": "seo_enhancement",
            "priority": "medium",
            "title": "Optimisation SEO de base", 
            "description": "Ajouter les meta tags pour le référencement",
            "code_suggestion": "<!-- Meta tags SEO -->\\n<meta name=\\"description\\" content=\\"Apprenez la structure HTML avec ce cours complet\\">\\n<meta name=\\"keywords\\" content=\\"HTML, structure, développement web\\">\\n<meta property=\\"og:title\\" content=\\"Structure HTML\\">\\n<meta property=\\"og:description\\" content=\\"Guide complet sur la structure des pages HTML\\">\\n<meta property=\\"og:type\\" content=\\"article\\">"
        }
    ]',
    NOW(),
    NOW()
),

-- 3. Bonnes pratiques (ID: 4)
(
    4,
    '{
        "strengths": ["Conseils pertinents", "Approche pédagogique"],
        "weaknesses": ["Exemples pratiques insuffisants", "Pas assez spécifique", "Manque d\'exemples de code"],
        "concepts_covered": ["best practices", "code quality", "maintainability"],
        "code_issues": ["Pas d\'exemples concrets", "Théorie sans pratique"],
        "accessibility_score": 8.1,
        "seo_score": 7.5
    }',
    'Cours théorique sur les bonnes pratiques mais manque cruellement d\'exemples pratiques.',
    6.9,
    'intermediate',
    8,
    '[
        {
            "type": "practical_examples",
            "priority": "high",
            "title": "Ajouter des exemples concrets",
            "description": "Illustrer chaque bonne pratique avec du code réel",
            "code_suggestion": "<!-- ❌ Mauvais exemple -->\\n<div class=\\"btn\\">Cliquer</div>\\n\\n<!-- ✅ Bon exemple -->\\n<button type=\\"button\\" class=\\"btn btn-primary\\">\\n  Cliquer ici\\n</button>\\n\\n<!-- Pourquoi c\'est mieux : -->\\n<!-- - Sémantique correcte avec <button> -->\\n<!-- - Accessible au clavier -->\\n<!-- - Reconnu par les lecteurs d\'écran -->"
        }
    ]',
    NOW(),
    NOW()
),

-- 4. Les sélecteurs (ID: 5)
(
    5,
    '{
        "strengths": ["Couverture complète des sélecteurs", "Exemples variés"],
        "weaknesses": ["Performance non abordée", "Sélecteurs complexes manquants", "Pas de bonnes pratiques"],
        "concepts_covered": ["class", "id", "attribute", "pseudo-selectors"],
        "code_issues": ["Pas d\'optimisation des sélecteurs", "Spécificité non expliquée"],
        "accessibility_score": 7.2,
        "seo_score": 6.8
    }',
    'Bon cours sur les sélecteurs CSS mais manque d\'optimisation et de bonnes pratiques avancées.',
    7.6,
    'intermediate',
    15,
    '[
        {
            "type": "performance_optimization",
            "priority": "high",
            "title": "Optimisation des sélecteurs CSS",
            "description": "Enseigner les sélecteurs performants et la spécificité",
            "code_suggestion": "/* ❌ Sélecteurs lents et trop spécifiques */\\nbody div.container ul li a.link { color: blue; }\\n\\n/* ✅ Sélecteurs optimisés */\\n.nav-link { color: blue; }\\n\\n/* Règles de performance : */\\n/* 1. Éviter les sélecteurs universels (*) */\\n/* 2. Minimiser les niveaux de nesting */\\n/* 3. Utiliser des classes plutôt que des sélecteurs complexes */"
        },
        {
            "type": "advanced_selectors",
            "priority": "medium",
            "title": "Sélecteurs CSS3 avancés",
            "description": "Ajouter les pseudo-classes et attributs modernes",
            "code_suggestion": "/* Sélecteurs modernes utiles */\\n:is(.card, .panel) .title { font-weight: bold; }\\n:where(.btn) { padding: 0.5rem; }\\n:has(.error) { border-color: red; }\\n[data-theme=\\"dark\\"] { background: #333; }"
        }
    ]',
    NOW(),
    NOW()
),

-- 5. Flexbox (ID: 7)
(
    7,
    '{
        "strengths": ["Concepts Flexbox bien expliqués", "Exemples visuels"],
        "weaknesses": ["Cas d\'usage réels manquants", "Responsive design non abordé", "Fallbacks pour anciens navigateurs"],
        "concepts_covered": ["display: flex", "justify-content", "align-items", "flex-direction"],
        "code_issues": ["Pas de support IE", "Layouts complexes non montrés"],
        "accessibility_score": 8.0,
        "seo_score": 7.2
    }',
    'Excellent cours sur Flexbox mais manque d\'exemples de layouts réels et de responsive.',
    8.2,
    'intermediate',
    18,
    '[
        {
            "type": "real_world_examples",
            "priority": "high", 
            "title": "Layouts Flexbox complets",
            "description": "Ajouter des exemples de navigation, cards, et layouts réels",
            "code_suggestion": "/* Layout de navigation responsive */\\n.navbar {\\n  display: flex;\\n  justify-content: space-between;\\n  align-items: center;\\n  padding: 1rem;\\n}\\n\\n.nav-menu {\\n  display: flex;\\n  gap: 1rem;\\n}\\n\\n@media (max-width: 768px) {\\n  .navbar {\\n    flex-direction: column;\\n  }\\n  \\n  .nav-menu {\\n    width: 100%;\\n    justify-content: center;\\n  }\\n}"
        },
        {
            "type": "browser_compatibility",
            "priority": "medium",
            "title": "Support navigateurs et fallbacks",
            "description": "Ajouter les préfixes et alternatives pour compatibilité",
            "code_suggestion": "/* Flexbox avec préfixes pour compatibilité */\\n.flex-container {\\n  display: -webkit-box;      /* OLD - iOS 6-, Safari 3.1-6 */\\n  display: -webkit-flex;     /* NEW - Safari 6.1+ */\\n  display: -ms-flexbox;      /* TWEENER - IE 10 */\\n  display: flex;             /* NEW - Spec */\\n  \\n  -webkit-justify-content: center;\\n  -ms-flex-pack: center;\\n  justify-content: center;\\n}"
        }
    ]',
    NOW(),
    NOW()
);

-- Recommandations de contenu correspondantes
INSERT INTO content_recommendation (
    page_content_id, type, priority, title, description, 
    suggested_content, status, created_at
) VALUES
-- Recommandations pour tableaux (ID: 2)
(2, 'code_improvement', 'high', 'Accessibilité des tableaux', 
 'Améliorer l\'accessibilité en ajoutant caption et scope aux tableaux',
 'Ajouter <caption> pour décrire le tableau et scope="col/row" sur les <th>', 'pending', NOW()),

(2, 'css_enhancement', 'medium', 'Styling des tableaux',
 'Ajouter du CSS moderne pour améliorer l\'apparence des tableaux', 
 'CSS avec border-collapse, hover effects et styling responsive', 'pending', NOW()),

-- Recommandations pour structure HTML (ID: 3)  
(3, 'seo_enhancement', 'high', 'Optimisation SEO',
 'Ajouter les meta tags essentiels pour le référencement',
 'Meta description, Open Graph, structured data', 'pending', NOW()),

(3, 'code_improvement', 'high', 'HTML5 sémantique complet',
 'Compléter avec DOCTYPE, lang, viewport et structure sémantique',
 'Exemple complet avec header, main, section, aside, footer', 'pending', NOW()),

-- Recommandations pour Flexbox (ID: 7)
(7, 'real_world_examples', 'high', 'Layouts Flexbox réels', 
 'Ajouter des exemples de layouts complets (navigation, grids, cards)',
 'Exemples concrets : navbar responsive, grid de cards, footer sticky', 'pending', NOW()),

(7, 'browser_compatibility', 'medium', 'Compatibilité navigateurs',
 'Ajouter les préfixes CSS et fallbacks pour anciens navigateurs', 
 'Préfixes webkit, ms-flexbox et alternatives pour IE9-10', 'pending', NOW());