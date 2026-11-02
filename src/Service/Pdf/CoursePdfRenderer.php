<?php

declare(strict_types=1);

namespace App\Service\Pdf;

use App\Domain\SiteConfiguration\Repository\SiteConfigurationRepositoryInterface;
use App\Entity\Menus;
use App\Entity\NiveauCours;
use App\Entity\PageContent;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

/**
 * PDF d'un cours, ou d'un module (fiche d'atelier : tous ses cours à la suite), avec leurs captures.
 *
 * Le PDF est gardé sur le disque sous l'empreinte de tout ce qu'il affiche : il n'est recalculé que
 * lorsqu'un texte, un niveau ou une capture change. Le moteur ne lit que les images du site et la police
 * du dépôt ; il ne va jamais sur le réseau et n'exécute ni PHP ni JavaScript.
 */
final class CoursePdfRenderer
{
    // À augmenter quand le gabarit change, pour régénérer les PDF déjà gardés
    private const TEMPLATE_VERSION = 1;
    private const FONT = 'Atkinson Hyperlegible';

    public function __construct(
        private Environment $twig,
        private CourseHtmlBuilder $builder,
        private SiteConfigurationRepositoryInterface $siteConfiguration,
        private EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        #[Autowire('%kernel.project_dir%/var/pdf')]
        private string $storageDir,
    ) {}

    /**
     * @param string $siteUrl adresse du site (schéma et hôte), pour écrire l'adresse du cours sur le papier
     */
    public function course(PageContent $course, string $siteUrl): PdfDocument
    {
        $slug = ltrim((string) $course->getPage()?->getSlug(), '/');

        return $this->render(
            'cours-'.$course->getId(),
            $slug.'.pdf',
            [
                'title' => (string) $course->getTitle(),
                'context' => $this->context($course),
                'courses' => [$this->courseView($course, $siteUrl)],
            ],
            [$this->courseFingerprint($course)],
            $siteUrl,
        );
    }

    /**
     * @param list<PageContent> $courses cours visibles du module, déjà triés
     */
    public function module(Menus $module, array $courses, string $siteUrl): PdfDocument
    {
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $this->ascii((string) $module->getLabel())), '-') ?: 'module';

        return $this->render(
            'module-'.$module->getId(),
            'module-'.$slug.'.pdf',
            [
                'title' => (string) $module->getLabel(),
                'context' => $module->getCategory()?->getName() ? 'Parcours : '.ucfirst((string) $module->getCategory()->getName()) : null,
                'courses' => array_map(fn (PageContent $course) => $this->courseView($course, $siteUrl), $courses),
                'isModule' => true,
            ],
            [$module->getLabel(), ...array_map(fn (PageContent $course) => $this->courseFingerprint($course), $courses)],
            $siteUrl,
        );
    }

    /**
     * Ordre des cours d'un module, comme sur le site (front/src/utils/niveaux.js) : par niveau (les cours
     * sans niveau après), puis par rang éditorial, puis par ancienneté.
     *
     * @param list<PageContent> $courses
     * @return list<PageContent>
     */
    public static function sortForModule(array $courses): array
    {
        usort($courses, static function (PageContent $a, PageContent $b): int {
            $level = static fn (PageContent $c) => $c->getNiveauCours()?->getName() ? (int) $c->getNiveauCours()->getOrdre() : \PHP_INT_MAX;

            return [$level($a), $a->getOrdre() ?? \PHP_INT_MAX, $a->getId()] <=> [$level($b), $b->getOrdre() ?? \PHP_INT_MAX, $b->getId()];
        });

        return $courses;
    }

    /**
     * @param array<string, mixed> $view
     * @param list<mixed> $content tout ce que le PDF affiche
     */
    private function render(string $key, string $filename, array $view, array $content, string $siteUrl): PdfDocument
    {
        $siteName = (string) ($this->siteConfiguration->getDefaultSettings()['siteName'] ?? 'Médiation numérique');
        $fingerprint = sha1((string) json_encode([self::TEMPLATE_VERSION, $siteName, $siteUrl, $content]));
        $path = sprintf('%s/%s-%s.pdf', $this->storageDir, $key, $fingerprint);
        $document = new PdfDocument($path, $fingerprint, $filename);
        if (is_file($path)) {
            return $document;
        }

        $html = $this->twig->render('pdf/document.html.twig', $view + [
            'siteName' => $siteName,
            'fontDir' => $this->projectDir.'/resources/fonts',
            'generatedAt' => new \DateTimeImmutable(),
            'isModule' => false,
        ]);

        $dompdf = new Dompdf($this->options());
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        // Numéro de page en bas à droite, sur chaque page
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont(self::FONT);
        $canvas->page_text($canvas->get_width() - 120, $canvas->get_height() - 34, 'Page {PAGE_NUM} sur {PAGE_COUNT}', $font, 9, [0.2, 0.2, 0.2]);

        // Écriture puis renommage : un téléchargement simultané ne lit jamais un fichier à moitié écrit
        $this->ensureDir($this->storageDir);
        $tmp = $path.'.'.bin2hex(random_bytes(4)).'.tmp';
        file_put_contents($tmp, (string) $dompdf->output());
        rename($tmp, $path);

        // Les versions précédentes de ce document ne servent plus
        foreach (glob(sprintf('%s/%s-*.pdf', $this->storageDir, $key)) ?: [] as $old) {
            if ($old !== $path) {
                @unlink($old);
            }
        }

        return $document;
    }

    private function options(): Options
    {
        $fontCache = $this->storageDir.'/fonts';
        $this->ensureDir($fontCache);

        return (new Options())
            ->setIsRemoteEnabled(false)
            ->setIsPhpEnabled(false)
            ->setIsJavascriptEnabled(false)
            ->setChroot([$this->projectDir.'/public', $this->projectDir.'/resources/fonts', $this->storageDir.'/images'])
            ->setFontDir($fontCache)
            ->setFontCache($fontCache)
            ->setDefaultFont(self::FONT)
            ->setDefaultPaperSize('a4')
            ->setDpi(96);
    }

    /**
     * @return array<string, mixed>
     */
    private function courseView(PageContent $course, string $siteUrl): array
    {
        return [
            'title' => (string) $course->getTitle(),
            'niveau' => $this->levelLabel($course->getNiveauCours()),
            'html' => $this->builder->build($course),
            'url' => rtrim($siteUrl, '/').'/spa/pages/'.ltrim((string) $course->getPage()?->getSlug(), '/'),
        ];
    }

    // « Niveau 1 sur 3 : Je découvre », comme le badge du site : la place suit l'ordre des niveaux
    private function levelLabel(?NiveauCours $level): ?string
    {
        if ($level?->getName() === null) {
            return null;
        }
        $levels = $this->entityManager->getRepository(NiveauCours::class)->findBy([], ['ordre' => 'ASC', 'id' => 'ASC']);
        $rank = array_search($level->getId(), array_map(static fn (NiveauCours $n) => $n->getId(), $levels), true);

        return $rank === false ? $level->getName() : sprintf('Niveau %d sur %d : %s', $rank + 1, \count($levels), $level->getName());
    }

    private function context(PageContent $course): ?string
    {
        $parts = array_filter([
            $course->getCategory()?->getName() ? 'Parcours : '.ucfirst((string) $course->getCategory()->getName()) : null,
            $course->getMenu()?->getLabel() ? 'Module : '.$course->getMenu()->getLabel() : null,
        ]);

        return $parts === [] ? null : implode(' · ', $parts);
    }

    /**
     * @return list<mixed>
     */
    private function courseFingerprint(PageContent $course): array
    {
        $captures = [];
        foreach ($this->builder->captures((string) $course->getCode()) as $id => $capture) {
            $captures[] = [$id, $capture->getFilename(), $capture->getCaption(), $capture->getAltText(), $capture->getKind(), $capture->getService(), $capture->getSourceUrl(), $capture->getCapturedAt()?->format('Y-m-d')];
        }

        return [
            $course->getId(),
            $course->getTitle(),
            $course->getCode(),
            $this->levelLabel($course->getNiveauCours()),
            $course->getCategory()?->getName(),
            $course->getMenu()?->getLabel(),
            $course->getPage()?->getSlug(),
            $captures,
        ];
    }

    private function ascii(string $text): string
    {
        $text = \Normalizer::normalize($text, \Normalizer::FORM_D) ?: $text;

        return mb_strtolower((string) preg_replace('/[\x{0300}-\x{036f}]/u', '', $text));
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Dossier des PDF impossible à créer : %s', $dir));
        }
    }
}
