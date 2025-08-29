<?php

namespace App\Service;

use App\Entity\ContentRecommendation;
use App\Entity\PageContent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CourseModificationService
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * Applique une recommandation au cours
     */
    public function applyRecommendation(ContentRecommendation $recommendation): bool
    {
        $pageContent = $recommendation->getPageContent();
        if (!$pageContent) {
            return false;
        }

        $suggestedContent = $recommendation->getSuggestedContent();
        $modificationType = $recommendation->getType();

        try {
            switch ($modificationType) {
                case 'code_improvement':
                case 'css_enhancement':
                case 'seo_enhancement':
                    $this->integrateCodeImprovement($pageContent, $suggestedContent, $recommendation);
                    break;
                
                case 'structure':
                    $this->improveStructure($pageContent, $suggestedContent);
                    break;
                
                case 'real_world_examples':
                    $this->addRealWorldExamples($pageContent, $suggestedContent);
                    break;
                
                case 'browser_compatibility':
                    $this->addCompatibilityCode($pageContent, $suggestedContent);
                    break;
                
                default:
                    $this->appendSuggestion($pageContent, $suggestedContent);
                    break;
            }

            // Marquer la recommandation comme appliquée
            $recommendation->setStatus('applied');
            $recommendation->setAppliedBy($this->security->getUser());
            $recommendation->setAppliedAt(new \DateTime());

            $this->entityManager->flush();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Intègre les améliorations de code HTML/CSS
     */
    private function integrateCodeImprovement(PageContent $pageContent, string $suggestedContent, ContentRecommendation $recommendation): void
    {
        $currentContent = $pageContent->getContent();
        $title = $recommendation->getTitle();
        
        // Ajouter une section dédiée à l'amélioration
        $improvementSection = "\n\n<div class=\"ai-improvement\">\n";
        $improvementSection .= "<h3>✨ Amélioration IA : {$title}</h3>\n";
        $improvementSection .= "<div class=\"improvement-description\">\n";
        $improvementSection .= "<p><strong>Description:</strong> " . $recommendation->getDescription() . "</p>\n";
        $improvementSection .= "</div>\n";
        $improvementSection .= "<div class=\"code-example\">\n";
        $improvementSection .= "<h4>Code amélioré:</h4>\n";
        $improvementSection .= "<pre><code>" . htmlspecialchars($suggestedContent) . "</code></pre>\n";
        $improvementSection .= "</div>\n";
        $improvementSection .= "</div>\n";

        $pageContent->setContent($currentContent . $improvementSection);
    }

    /**
     * Améliore la structure du cours
     */
    private function improveStructure(PageContent $pageContent, string $suggestedContent): void
    {
        $currentContent = $pageContent->getContent();
        
        // Ajouter la structure améliorée à la fin
        $structureSection = "\n\n<div class=\"structure-improvement\">\n";
        $structureSection .= "<h3>🏗️ Structure améliorée</h3>\n";
        $structureSection .= $suggestedContent . "\n";
        $structureSection .= "</div>\n";

        $pageContent->setContent($currentContent . $structureSection);
    }

    /**
     * Ajoute des exemples concrets
     */
    private function addRealWorldExamples(PageContent $pageContent, string $suggestedContent): void
    {
        $currentContent = $pageContent->getContent();
        
        $exampleSection = "\n\n<div class=\"real-world-examples\">\n";
        $exampleSection .= "<h3>🌍 Exemples concrets</h3>\n";
        $exampleSection .= "<div class=\"examples-content\">\n";
        $exampleSection .= $suggestedContent . "\n";
        $exampleSection .= "</div>\n";
        $exampleSection .= "</div>\n";

        $pageContent->setContent($currentContent . $exampleSection);
    }

    /**
     * Ajoute le code de compatibilité navigateurs
     */
    private function addCompatibilityCode(PageContent $pageContent, string $suggestedContent): void
    {
        $currentContent = $pageContent->getContent();
        
        $compatibilitySection = "\n\n<div class=\"browser-compatibility\">\n";
        $compatibilitySection .= "<h3>🌐 Compatibilité navigateurs</h3>\n";
        $compatibilitySection .= "<div class=\"compatibility-info\">\n";
        $compatibilitySection .= "<p>Code compatible avec les anciens navigateurs :</p>\n";
        $compatibilitySection .= "<pre><code>" . htmlspecialchars($suggestedContent) . "</code></pre>\n";
        $compatibilitySection .= "</div>\n";
        $compatibilitySection .= "</div>\n";

        $pageContent->setContent($currentContent . $compatibilitySection);
    }

    /**
     * Ajoute simplement la suggestion à la fin du cours
     */
    private function appendSuggestion(PageContent $pageContent, string $suggestedContent): void
    {
        $currentContent = $pageContent->getContent();
        
        $suggestionSection = "\n\n<div class=\"ai-suggestion\">\n";
        $suggestionSection .= "<h3>💡 Suggestion IA</h3>\n";
        $suggestionSection .= $suggestedContent . "\n";
        $suggestionSection .= "</div>\n";

        $pageContent->setContent($currentContent . $suggestionSection);
    }

    /**
     * Prévisualise les modifications avant application
     */
    public function previewModification(ContentRecommendation $recommendation): string
    {
        $pageContent = $recommendation->getPageContent();
        if (!$pageContent) {
            return '';
        }

        $currentContent = $pageContent->getContent();
        $suggestedContent = $recommendation->getSuggestedContent();
        $title = $recommendation->getTitle();
        
        $preview = $currentContent . "\n\n" . str_repeat("=", 50) . "\n";
        $preview .= "APERÇU DES MODIFICATIONS À AJOUTER:\n";
        $preview .= str_repeat("=", 50) . "\n\n";
        $preview .= "Titre: {$title}\n";
        $preview .= "Description: " . $recommendation->getDescription() . "\n\n";
        $preview .= "Code à ajouter:\n";
        $preview .= $suggestedContent . "\n";
        $preview .= str_repeat("=", 50);

        return $preview;
    }
}