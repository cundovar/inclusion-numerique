<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Entity\Category;
use App\Entity\CourseMedia;
use App\Entity\Menus;
use App\Entity\NiveauCours;
use App\Entity\PageContent;

/**
 * Classement éditorial des cours dans les modules (chapitres) et correction d'une image, y compris sur
 * un cours publié : ni le texte, ni le statut, ni la relecture ne changent.
 */
final class CourseClassificationConnectorTest extends McpTestCase
{
    private Category $parcours;
    private Menus $ordinateur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parcours = (new Category())->setName('Premiers gestes numériques')->setAudience(Category::AUDIENCE_APPRENDRE);
        $this->ordinateur = (new Menus())->setLabel('Ordinateur : premiers gestes')->setCategory($this->parcours);
        $this->entityManager->persist($this->parcours);
        $this->entityManager->persist($this->ordinateur);
        $this->entityManager->flush();
    }

    public function testPublishedCoursesAreClassifiedWithoutTouchingTheirContent(): void
    {
        $souris = $this->createCourse('souris', PageContent::STATUS_PUBLISHED);
        $clavier = $this->createCourse('clavier', PageContent::STATUS_DRAFT);
        $other = (new Category())->setName('Accompagner')->setAudience(Category::AUDIENCE_ACCOMPAGNER);
        $foreign = (new Menus())->setLabel('Posture')->setCategory($other);
        $this->entityManager->persist($other);
        $this->entityManager->persist($foreign);
        $this->entityManager->flush();

        $result = $this->tool(self::CLAUDE, 'classer_cours', ['coursIds' => [$souris, $clavier, 999999], 'moduleId' => $this->ordinateur->getId()]);
        self::assertFalse($result['error'], $result['text']);
        self::assertSame('Ordinateur : premiers gestes', $result['data']['module']['nom']);
        self::assertSame([$souris, $clavier], array_column($result['data']['classes'], 'id'));
        self::assertNull($result['data']['classes'][0]['ancienModuleId']);
        self::assertSame([['id' => 999999, 'raison' => 'Cours introuvable.']], $result['data']['refus']);

        $this->entityManager->clear();
        $course = $this->entityManager->find(PageContent::class, $souris);
        self::assertSame($this->ordinateur->getId(), $course->getMenu()?->getId());
        self::assertSame($this->ordinateur->getId(), $course->getPage()?->getMenus()?->getId());
        self::assertSame([PageContent::STATUS_PUBLISHED, '<p>Texte</p>', 'favorable'], [$course->getStatus(), $course->getCode(), $course->getReviewReport()['avis'] ?? null]);

        // Une seconde demande ne change rien ; un module d'un autre parcours est refusé, cours par cours
        self::assertSame([$souris], $this->tool(self::CLAUDE, 'classer_cours', ['coursIds' => [$souris], 'moduleId' => $this->ordinateur->getId()])['data']['dejaDansCeModule']);
        $refused = $this->tool(self::CLAUDE, 'classer_cours', ['coursIds' => [$souris], 'moduleId' => $foreign->getId()])['data'];
        self::assertSame([], $refused['classes']);
        self::assertStringContainsString('autre parcours', $refused['refus'][0]['raison']);

        // null retire explicitement du module
        $removed = $this->tool(self::CLAUDE, 'classer_cours', ['coursIds' => [$clavier], 'moduleId' => null])['data'];
        self::assertSame($this->ordinateur->getId(), $removed['classes'][0]['ancienModuleId']);
        self::assertTrue($this->tool(self::CLAUDE, 'classer_cours', ['coursIds' => [$clavier], 'moduleId' => 999999])['error']);
    }

    public function testOrderOfTheIdsBecomesTheOrderOfTheCoursesInTheModule(): void
    {
        $premier = $this->createCourse('premier', PageContent::STATUS_PUBLISHED);
        $deuxieme = $this->createCourse('deuxieme', PageContent::STATUS_PUBLISHED);
        $troisieme = $this->createCourse('troisieme', PageContent::STATUS_PUBLISHED);

        $first = $this->tool(self::CLAUDE, 'classer_cours', ['coursIds' => [$troisieme, $premier, $deuxieme], 'moduleId' => $this->ordinateur->getId()])['data'];
        self::assertSame([$troisieme => 1, $premier => 2, $deuxieme => 3], $first['ordres']);

        // Cours déjà dans le module : seul leur rang change
        $again = $this->tool(self::CLAUDE, 'classer_cours', ['coursIds' => [$premier, $deuxieme, $troisieme], 'moduleId' => $this->ordinateur->getId()])['data'];
        self::assertSame([], $again['classes']);
        self::assertSame([$premier, $deuxieme, $troisieme], $again['dejaDansCeModule']);

        $this->entityManager->clear();
        $ranks = array_map(fn (int $id) => $this->entityManager->find(PageContent::class, $id)->getOrdre(), [$premier, $deuxieme, $troisieme]);
        self::assertSame([1, 2, 3], $ranks);

        // Retirer du module efface le rang
        $this->tool(self::CLAUDE, 'classer_cours', ['coursIds' => [$deuxieme], 'moduleId' => null]);
        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(PageContent::class, $deuxieme)->getOrdre());
    }

    public function testParcoursAreOrderedByTheOrderOfTheIds(): void
    {
        $protection = (new Category())->setName('Se protéger sur Internet')->setAudience(Category::AUDIENCE_APPRENDRE);
        $demarches = (new Category())->setName('Faire des démarches en ligne')->setAudience(Category::AUDIENCE_APPRENDRE);
        $this->entityManager->persist($protection);
        $this->entityManager->persist($demarches);
        $this->entityManager->flush();

        $result = $this->tool(self::CLAUDE, 'ordonner_parcours', ['parcoursIds' => [$this->parcours->getId(), $protection->getId(), $demarches->getId(), 999999]]);
        self::assertFalse($result['error'], $result['text']);
        self::assertSame([1, 2, 3], array_column($result['data']['ordres'], 'ordre'));
        self::assertSame([['id' => 999999, 'raison' => 'Parcours introuvable (voir lister_catalogue).']], $result['data']['refus']);

        $this->entityManager->clear();
        self::assertSame(3, $this->entityManager->find(Category::class, $demarches->getId())->getOrdre());
        $catalogue = $this->tool(self::CLAUDE, 'lister_catalogue', [])['data']['parcours'];
        self::assertSame(1, array_values(array_filter($catalogue, fn (array $p) => $p['id'] === $this->parcours->getId()))[0]['ordre']);
    }

    public function testParcoursThumbnailMustBeAnIllustrationAndCanBeRemoved(): void
    {
        $illustration = $this->createMedia('miniature', CourseMedia::KIND_ILLUSTRATION);
        $capture = $this->createMedia('page-caf', CourseMedia::KIND_CAPTURE);

        $result = $this->tool(self::CLAUDE, 'definir_illustration_parcours', ['parcoursId' => $this->parcours->getId(), 'illustrationId' => $illustration->getId()]);
        self::assertFalse($result['error'], $result['text']);
        self::assertSame($illustration->getId(), $result['data']['illustration']['id']);

        $catalogue = $this->tool(self::CLAUDE, 'lister_catalogue', [])['data']['parcours'];
        $entry = array_values(array_filter($catalogue, fn (array $p) => $p['id'] === $this->parcours->getId()))[0];
        self::assertSame($illustration->getId(), $entry['illustrationId']);
        self::assertSame($illustration->getUrl(), $entry['illustrationUrl']);

        // Une capture de page de service, un parcours ou une image inconnus sont refusés
        self::assertStringContainsString('Seule une illustration', $this->tool(self::CLAUDE, 'definir_illustration_parcours', ['parcoursId' => $this->parcours->getId(), 'illustrationId' => $capture->getId()])['text']);
        self::assertTrue($this->tool(self::CLAUDE, 'definir_illustration_parcours', ['parcoursId' => $this->parcours->getId(), 'illustrationId' => 999999])['error']);
        self::assertTrue($this->tool(self::CLAUDE, 'definir_illustration_parcours', ['parcoursId' => 999999, 'illustrationId' => $illustration->getId()])['error']);

        // null retire la miniature
        $removed = $this->tool(self::CLAUDE, 'definir_illustration_parcours', ['parcoursId' => $this->parcours->getId(), 'illustrationId' => null]);
        self::assertFalse($removed['error'], $removed['text']);
        self::assertNull($removed['data']['illustration']);
        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(Category::class, $this->parcours->getId())->getIllustration());
    }

    public function testMissingModuleIsReportedBeforePublishing(): void
    {
        $id = $this->createCourse('sans-module', PageContent::STATUS_DRAFT);

        $codes = array_column($this->tool(self::CLAUDE, 'verifier_brouillon', ['id' => $id])['data']['controles'], 'code');
        self::assertContains('MODULE_MANQUANT', $codes);

        $this->tool(self::CLAUDE, 'classer_cours', ['coursIds' => [$id], 'moduleId' => $this->ordinateur->getId()]);
        $published = $this->tool(self::CLAUDE, 'publier_cours', ['id' => $id]);
        self::assertFalse($published['error'], $published['text']);
    }

    public function testImageOfAPublishedCourseIsReplacedWhenTheChecksStillPass(): void
    {
        $old = $this->createMedia('ancienne', CourseMedia::KIND_ILLUSTRATION);
        $new = $this->createMedia('nouvelle', CourseMedia::KIND_ILLUSTRATION);
        $real = $this->createMedia('caf', CourseMedia::KIND_CAPTURE);
        $id = $this->createCourse('souris', PageContent::STATUS_PUBLISHED, sprintf('<p>Texte</p><p>[capture:%d]</p><p>Encore [capture:%1$d]</p>', $old->getId()));

        $result = $this->tool(self::CLAUDE, 'remplacer_capture', ['coursId' => $id, 'ancienneCaptureId' => $old->getId(), 'nouvelleCaptureId' => $new->getId()]);
        self::assertFalse($result['error'], $result['text']);
        self::assertSame(2, $result['data']['remplacements']);
        self::assertSame($new->getMarker(), $result['data']['nouvelleCapture']['marqueur']);

        $this->entityManager->clear();
        $course = $this->entityManager->find(PageContent::class, $id);
        self::assertSame(sprintf('<p>Texte</p><p>[capture:%d]</p><p>Encore [capture:%1$d]</p>', $new->getId()), $course->getCode());
        self::assertSame([PageContent::STATUS_PUBLISHED, 'favorable'], [$course->getStatus(), $course->getReviewReport()['avis'] ?? null]);

        // Une vraie capture demande une source officielle que ce cours n'a pas : rien n'est changé
        $blocked = $this->tool(self::CLAUDE, 'remplacer_capture', ['coursId' => $id, 'ancienneCaptureId' => $new->getId(), 'nouvelleCaptureId' => $real->getId()]);
        self::assertTrue($blocked['error']);
        self::assertStringContainsString('SOURCE_MANQUANTE', $blocked['text']);
        $this->entityManager->clear();
        self::assertStringContainsString('[capture:'.$new->getId().']', (string) $this->entityManager->find(PageContent::class, $id)->getCode());

        self::assertStringContainsString('ne contient pas', $this->tool(self::CLAUDE, 'remplacer_capture', ['coursId' => $id, 'ancienneCaptureId' => $old->getId(), 'nouvelleCaptureId' => $new->getId()])['text']);
        self::assertTrue($this->tool(self::CLAUDE, 'remplacer_capture', ['coursId' => $id, 'ancienneCaptureId' => $new->getId(), 'nouvelleCaptureId' => 999999])['error']);

        $inReview = $this->createCourse('en-revue', PageContent::STATUS_IN_REVIEW, sprintf('<p>[capture:%d]</p>', $old->getId()));
        self::assertStringContainsString('relecture humaine', $this->tool(self::CLAUDE, 'remplacer_capture', ['coursId' => $inReview, 'ancienneCaptureId' => $old->getId(), 'nouvelleCaptureId' => $new->getId()])['text']);
    }

    public function testPublishedCoursesGetALevelWithoutLosingTheirReview(): void
    {
        $souris = $this->createCourse('souris', PageContent::STATUS_PUBLISHED);
        $decouvre = (new NiveauCours())->setName('Je découvre')->setOrdre(1);
        $this->entityManager->persist($decouvre);
        $this->entityManager->flush();

        $result = $this->tool(self::CLAUDE, 'classer_niveau', ['coursIds' => [$souris, 999999], 'niveauId' => $decouvre->getId()]);
        self::assertFalse($result['error'], $result['text']);
        self::assertSame('Je découvre', $result['data']['niveau']['nom']);
        self::assertSame([$souris], array_column($result['data']['classes'], 'id'));
        self::assertNull($result['data']['classes'][0]['ancienNiveauId']);
        self::assertSame([['id' => 999999, 'raison' => 'Cours introuvable.']], $result['data']['refus']);

        $this->entityManager->clear();
        $course = $this->entityManager->find(PageContent::class, $souris);
        self::assertSame($decouvre->getId(), $course->getNiveauCours()?->getId());
        self::assertSame([PageContent::STATUS_PUBLISHED, '<p>Texte</p>', 'favorable'], [$course->getStatus(), $course->getCode(), $course->getReviewReport()['avis'] ?? null]);

        // Une seconde demande ne change rien ; null retire le niveau ; un niveau inconnu est refusé
        self::assertSame([$souris], $this->tool(self::CLAUDE, 'classer_niveau', ['coursIds' => [$souris], 'niveauId' => $decouvre->getId()])['data']['dejaACeNiveau']);
        $removed = $this->tool(self::CLAUDE, 'classer_niveau', ['coursIds' => [$souris], 'niveauId' => null])['data'];
        self::assertSame($decouvre->getId(), $removed['classes'][0]['ancienNiveauId']);
        self::assertTrue($this->tool(self::CLAUDE, 'classer_niveau', ['coursIds' => [$souris], 'niveauId' => 999999])['error']);
    }

    /**
     * Cours relu favorablement par l'autre connecteur, puis mis au statut voulu.
     */
    private function createCourse(string $slug, string $status, string $content = '<p>Texte</p>'): int
    {
        $result = $this->tool(self::CLAUDE, 'creer_brouillon', ['titre' => 'Cours '.$slug, 'slug' => $slug, 'parcoursId' => $this->parcours->getId(), 'contenu' => $content]);
        self::assertFalse($result['error'], $result['text']);
        $id = (int) $result['data']['id'];
        $review = $this->tool(self::CHATGPT, 'enregistrer_relecture', ['id' => $id, 'avis' => 'favorable', 'resume' => 'Prêt.', 'points' => []]);
        self::assertFalse($review['error'], $review['text']);

        $this->entityManager->find(PageContent::class, $id)->setStatus($status);
        $this->entityManager->flush();

        return $id;
    }

    private function createMedia(string $name, string $kind): CourseMedia
    {
        $media = (new CourseMedia())->setKind($kind)->setFilename($name.'.png')->setCaption($name)->setAltText($name);
        if ($kind === CourseMedia::KIND_CAPTURE) {
            $media->setSourceUrl('https://www.caf.fr/')->setCapturedAt(new \DateTimeImmutable('2026-10-15'));
        }
        $this->entityManager->persist($media);
        $this->entityManager->flush();

        return $media;
    }
}
