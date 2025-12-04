<?php

namespace App\Service;

class CustomizationValidator
{
    /**
     * Whitelist des classes Tailwind autorisées
     */
    private const ALLOWED_CLASSES = [
        // Couleurs de fond
        'bg-blue-100', 'bg-blue-200', 'bg-blue-300', 'bg-blue-400', 'bg-blue-500', 'bg-blue-600', 'bg-blue-700', 'bg-blue-800', 'bg-blue-900',
        'bg-gray-100', 'bg-gray-200', 'bg-gray-300', 'bg-gray-400', 'bg-gray-500', 'bg-gray-600', 'bg-gray-700', 'bg-gray-800', 'bg-gray-900',
        'bg-red-100', 'bg-red-200', 'bg-red-300', 'bg-red-400', 'bg-red-500', 'bg-red-600', 'bg-red-700', 'bg-red-800', 'bg-red-900',
        'bg-green-100', 'bg-green-200', 'bg-green-300', 'bg-green-400', 'bg-green-500', 'bg-green-600', 'bg-green-700', 'bg-green-800', 'bg-green-900',
        'bg-yellow-100', 'bg-yellow-200', 'bg-yellow-300', 'bg-yellow-400', 'bg-yellow-500', 'bg-yellow-600', 'bg-yellow-700', 'bg-yellow-800', 'bg-yellow-900',
        'bg-purple-100', 'bg-purple-200', 'bg-purple-300', 'bg-purple-400', 'bg-purple-500', 'bg-purple-600', 'bg-purple-700', 'bg-purple-800', 'bg-purple-900',
        'bg-pink-100', 'bg-pink-200', 'bg-pink-300', 'bg-pink-400', 'bg-pink-500', 'bg-pink-600', 'bg-pink-700', 'bg-pink-800', 'bg-pink-900',
        'bg-indigo-100', 'bg-indigo-200', 'bg-indigo-300', 'bg-indigo-400', 'bg-indigo-500', 'bg-indigo-600', 'bg-indigo-700', 'bg-indigo-800', 'bg-indigo-900',

        // Couleurs de texte
        'text-white', 'text-black',
        'text-gray-100', 'text-gray-200', 'text-gray-300', 'text-gray-400', 'text-gray-500', 'text-gray-600', 'text-gray-700', 'text-gray-800', 'text-gray-900',
        'text-blue-100', 'text-blue-200', 'text-blue-300', 'text-blue-400', 'text-blue-500', 'text-blue-600', 'text-blue-700', 'text-blue-800', 'text-blue-900',
        'text-red-100', 'text-red-200', 'text-red-300', 'text-red-400', 'text-red-500', 'text-red-600', 'text-red-700', 'text-red-800', 'text-red-900',
        'text-green-100', 'text-green-200', 'text-green-300', 'text-green-400', 'text-green-500', 'text-green-600', 'text-green-700', 'text-green-800', 'text-green-900',
        'text-yellow-100', 'text-yellow-200', 'text-yellow-300', 'text-yellow-400', 'text-yellow-500', 'text-yellow-600', 'text-yellow-700', 'text-yellow-800', 'text-yellow-900',
        'text-purple-100', 'text-purple-200', 'text-purple-300', 'text-purple-400', 'text-purple-500', 'text-purple-600', 'text-purple-700', 'text-purple-800', 'text-purple-900',

        // Tailles de texte
        'text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl', 'text-4xl', 'text-5xl',

        // Hover couleurs de fond
        'hover:bg-blue-100', 'hover:bg-blue-200', 'hover:bg-blue-300', 'hover:bg-blue-400', 'hover:bg-blue-500', 'hover:bg-blue-600', 'hover:bg-blue-700',
        'hover:bg-gray-100', 'hover:bg-gray-200', 'hover:bg-gray-300', 'hover:bg-gray-400', 'hover:bg-gray-500', 'hover:bg-gray-600', 'hover:bg-gray-700',
        'hover:bg-red-100', 'hover:bg-red-200', 'hover:bg-red-300', 'hover:bg-red-400', 'hover:bg-red-500', 'hover:bg-red-600',
        'hover:bg-green-100', 'hover:bg-green-200', 'hover:bg-green-300', 'hover:bg-green-400', 'hover:bg-green-500', 'hover:bg-green-600',
        'hover:bg-purple-100', 'hover:bg-purple-200', 'hover:bg-purple-300', 'hover:bg-purple-400', 'hover:bg-purple-500',
        'hover:bg-indigo-100', 'hover:bg-indigo-200', 'hover:bg-indigo-300', 'hover:bg-indigo-400', 'hover:bg-indigo-500',

        // Hover couleurs de texte
        'hover:text-blue-500', 'hover:text-blue-600', 'hover:text-blue-700',
        'hover:text-gray-500', 'hover:text-gray-600', 'hover:text-gray-700', 'hover:text-gray-800',
        'hover:text-fuchsia-900', 'hover:text-red-500', 'hover:text-green-500',
    ];

    /**
     * Structure attendue des settings
     */
    private const EXPECTED_STRUCTURE = [
        'header' => [
            'bgColor',
            'textColor',
            'hoverColor',
        ],
        'body' => [
            'bgColor',
            'textColor',
        ],
        'menuGauche' => [
            'categoryBgColor',
            'categoryTextColor',
            'categoryTextSize',
            'categoryHoverColor',
            'menuItemBgColor',
            'menuItemTextColor',
            'menuItemHoverBgColor',
        ],
        'menuDroit' => [
            'categoryBgColor',
            'categoryTextColor',
            'categoryTextSize',
            'categoryHoverColor',
        ],
    ];

    /**
     * Valider et nettoyer les paramètres fournis
     */
    public function validate(array $settings): array
    {
        $validated = [];

        // Valider le nom du site (si fourni)
        if (isset($settings['siteName']) && is_string($settings['siteName'])) {
            $siteName = trim($settings['siteName']);
            // Limiter à 50 caractères et nettoyer
            if (strlen($siteName) > 0 && strlen($siteName) <= 50) {
                $validated['siteName'] = htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8');
            }
        }

        foreach (self::EXPECTED_STRUCTURE as $section => $fields) {
            if (!isset($settings[$section]) || !is_array($settings[$section])) {
                continue;
            }

            $validated[$section] = [];

            foreach ($fields as $field) {
                if (isset($settings[$section][$field])) {
                    $value = $settings[$section][$field];

                    // Vérifier que la classe est dans la whitelist
                    if ($this->isClassAllowed($value)) {
                        $validated[$section][$field] = $value;
                    }
                }
            }
        }

        return $validated;
    }

    /**
     * Vérifier si une classe est autorisée
     */
    private function isClassAllowed(string $class): bool
    {
        return in_array($class, self::ALLOWED_CLASSES, true);
    }

    /**
     * Obtenir toutes les classes autorisées
     */
    public function getAllowedClasses(): array
    {
        return self::ALLOWED_CLASSES;
    }

    /**
     * Obtenir les classes autorisées par catégorie
     */
    public function getAllowedClassesByCategory(): array
    {
        $categorized = [
            'bgColors' => [],
            'textColors' => [],
            'textSizes' => [],
            'hoverBgColors' => [],
            'hoverTextColors' => [],
        ];

        foreach (self::ALLOWED_CLASSES as $class) {
            if (str_starts_with($class, 'bg-')) {
                $categorized['bgColors'][] = $class;
            } elseif (str_starts_with($class, 'text-') && !str_contains($class, 'hover')) {
                if (preg_match('/text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl)/', $class)) {
                    $categorized['textSizes'][] = $class;
                } else {
                    $categorized['textColors'][] = $class;
                }
            } elseif (str_starts_with($class, 'hover:bg-')) {
                $categorized['hoverBgColors'][] = $class;
            } elseif (str_starts_with($class, 'hover:text-')) {
                $categorized['hoverTextColors'][] = $class;
            }
        }

        return $categorized;
    }
}
