<?php

declare(strict_types=1);

namespace App\Service\Pdf;

use App\Entity\CourseMedia;
use App\Entity\PageContent;
use App\Service\HtmlSanitizerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * HTML d'un cours prêt pour le PDF, comme sur le site (front/src/utils/captures.js et courseSections.js) :
 * chaque marqueur [capture:ID] devient l'image avec sa légende, sa source et sa date, et les titres des
 * gabarits (étape, attention, à vous…) reçoivent une classe. Les images sont lues sur le disque du site,
 * jamais par une adresse : le moteur PDF ne va pas sur le réseau.
 */
final class CourseHtmlBuilder
{
    public const MARKER = '/\[capture:(\d+)\]/';

    // Surface utile d'une page A4 (marges du gabarit), en pixels CSS à 96 ppp
    private const MAX_WIDTH = 640;
    private const MAX_HEIGHT = 560;
    // Au-delà, l'image est réduite pour le PDF (deux fois la largeur affichée : nette à l'impression)
    private const PDF_IMAGE_WIDTH = 1280;

    // Intitulés des gabarits, comparés sans accents ni majuscules (voir courseSections.js)
    private const KINDS = [
        'attention' => '/^(attention|points? de vigilance)\b/',
        'exercice' => '/^a vous\b/',
        'aide' => '/^(si ca ne marche pas|quand orienter)\b/',
        'repere' => '/^(avant de commencer|ce que vous allez apprendre|ce qu.il vous faut|objectif|pour qui|a quoi sert)\b/',
    ];
    private const STEP = '/^\s*(?:(etape)\s+\d+|\d+\s*[.)])/u';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private HtmlSanitizerService $sanitizer,
        #[Autowire('%kernel.project_dir%/public')]
        private string $publicDir,
        #[Autowire('%kernel.project_dir%/var/pdf/images')]
        private string $imageDir,
    ) {}

    public function build(PageContent $course): string
    {
        $html = $this->sanitizer->sanitize($course->getCode());
        if ($html === '') {
            return '';
        }

        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="cours">'.$html.'</div>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $root = $doc->getElementById('cours') ?? throw new \LogicException('Contenu du cours illisible.');

        $this->expandCaptures($doc, $this->captures((string) $course->getCode()));
        $this->markSections($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    /**
     * Captures citées dans un cours, par identifiant.
     *
     * @return array<int, CourseMedia>
     */
    public function captures(string $code): array
    {
        preg_match_all(self::MARKER, $code, $matches);
        $ids = array_values(array_unique(array_map('intval', $matches[1])));
        if ($ids === []) {
            return [];
        }

        $captures = [];
        foreach ($this->entityManager->getRepository(CourseMedia::class)->findBy(['id' => $ids]) as $capture) {
            $captures[(int) $capture->getId()] = $capture;
        }

        return $captures;
    }

    /**
     * @param array<int, CourseMedia> $captures
     */
    private function expandCaptures(\DOMDocument $doc, array $captures): void
    {
        $xpath = new \DOMXPath($doc);
        $nodes = iterator_to_array($xpath->query('//text()[contains(., "[capture:")]') ?: []);

        foreach ($nodes as $node) {
            $figures = [];
            $text = (string) preg_replace_callback(self::MARKER, function (array $match) use ($doc, $captures, &$figures): string {
                $capture = $captures[(int) $match[1]] ?? null;
                $figure = $capture === null ? null : $this->figure($doc, $capture);
                if ($figure !== null) {
                    $figures[] = $figure;
                }

                return '';
            }, (string) $node->nodeValue);

            // Une figure ne peut pas être dans un paragraphe : elle le suit, et un marqueur seul remplace le paragraphe
            $paragraph = $this->closestParagraph($node);
            $node->nodeValue = $text;
            $anchor = $paragraph ?? $node;
            foreach (array_reverse($figures) as $figure) {
                $anchor->parentNode?->insertBefore($figure, $anchor->nextSibling);
            }
            if ($paragraph !== null && trim($paragraph->textContent) === '' && $paragraph->getElementsByTagName('img')->length === 0) {
                $paragraph->parentNode?->removeChild($paragraph);
            }
        }
    }

    // Construite avec l'API DOM : aucune donnée de la capture n'est interprétée comme du HTML
    private function figure(\DOMDocument $doc, CourseMedia $capture): ?\DOMElement
    {
        $path = $this->publicDir.CourseMedia::PUBLIC_DIR.'/'.$capture->getFilename();
        if ($capture->getFilename() === null || !is_file($path)) {
            return null;
        }

        $figure = $doc->createElement('figure');
        $figure->setAttribute('class', 'capture-figure');

        $img = $doc->createElement('img');
        $img->setAttribute('src', $this->lightImage($path));
        $img->setAttribute('alt', $capture->getAltText());
        [$width, $height] = $this->displaySize($capture, $path);
        $img->setAttribute('style', sprintf('width: %dpx; height: %dpx;', $width, $height));
        $figure->appendChild($img);

        $caption = $doc->createElement('figcaption');
        $legend = $doc->createElement('p');
        $legend->setAttribute('class', 'capture-caption');
        $legend->appendChild($doc->createTextNode($capture->getCaption()));
        $caption->appendChild($legend);

        $source = $doc->createElement('p');
        $source->setAttribute('class', 'capture-source');
        if ($capture->isIllustration()) {
            $source->appendChild($doc->createTextNode("Illustration pédagogique : ce n'est pas l'écran réel du service."));
        } else {
            $service = $capture->getService() ? ' '.$capture->getService() : '';
            $date = $capture->getCapturedAt()?->format('d/m/Y');
            $text = sprintf('Capture du site%s%s.', $service, $date ? ', le '.$date : '');
            // Sur papier, un lien ne se clique pas : on écrit l'adresse
            if ($capture->getSourceUrl()) {
                $text .= ' Page officielle : '.$capture->getSourceUrl();
            }
            $source->appendChild($doc->createTextNode($text));
        }
        $caption->appendChild($source);
        $figure->appendChild($caption);

        return $figure;
    }

    /**
     * Taille affichée : l'image garde ses proportions et tient dans la page, sans être agrandie.
     *
     * @return array{int, int}
     */
    private function displaySize(CourseMedia $capture, string $path): array
    {
        $width = $capture->getWidth();
        $height = $capture->getHeight();
        if (!$width || !$height) {
            $size = @getimagesize($path);
            [$width, $height] = $size === false ? [self::MAX_WIDTH, (int) (self::MAX_WIDTH * 0.6)] : [$size[0], $size[1]];
        }
        $ratio = min(1, self::MAX_WIDTH / $width, self::MAX_HEIGHT / $height);

        return [max(1, (int) round($width * $ratio)), max(1, (int) round($height * $ratio))];
    }

    /**
     * Copie allégée d'une grande image (JPEG, au plus PDF_IMAGE_WIDTH pixels de large), gardée tant que
     * l'original ne change pas : le PDF reste léger pour une connexion faible. L'original sert sinon.
     */
    private function lightImage(string $path): string
    {
        $size = @getimagesize($path);
        if ($size === false || $size[0] <= self::PDF_IMAGE_WIDTH || !\in_array($size[2], [\IMAGETYPE_PNG, \IMAGETYPE_JPEG], true)) {
            return $path;
        }

        $light = sprintf('%s/%s.jpg', $this->imageDir, sha1($path.'|'.filemtime($path).'|'.filesize($path)));
        if (is_file($light)) {
            return $light;
        }

        $source = $size[2] === \IMAGETYPE_PNG ? @imagecreatefrompng($path) : @imagecreatefromjpeg($path);
        if ($source === false) {
            return $path;
        }
        $height = (int) round($size[1] * self::PDF_IMAGE_WIDTH / $size[0]);
        $target = imagecreatetruecolor(self::PDF_IMAGE_WIDTH, $height);
        // Fond blanc sous les zones transparentes, comme sur le papier
        imagefill($target, 0, 0, (int) imagecolorallocate($target, 255, 255, 255));
        imagecopyresampled($target, $source, 0, 0, 0, 0, self::PDF_IMAGE_WIDTH, $height, $size[0], $size[1]);

        if (!is_dir($this->imageDir) && !@mkdir($this->imageDir, 0775, true) && !is_dir($this->imageDir)) {
            return $path;
        }
        $tmp = $light.'.'.bin2hex(random_bytes(4)).'.tmp';
        $written = imagejpeg($target, $tmp, 82);
        if (!$written || !rename($tmp, $light)) {
            @unlink($tmp);

            return $path;
        }

        return $light;
    }

    private function closestParagraph(\DOMNode $node): ?\DOMElement
    {
        for ($parent = $node->parentNode; $parent instanceof \DOMElement; $parent = $parent->parentNode) {
            if ($parent->tagName === 'p') {
                return $parent;
            }
        }

        return null;
    }

    private function markSections(\DOMElement $root): void
    {
        foreach ($root->childNodes as $child) {
            if (!$child instanceof \DOMElement || $child->tagName !== 'h2') {
                continue;
            }
            $kind = $this->sectionKind($child->textContent);
            $child->setAttribute('class', trim($child->getAttribute('class').' cours-titre cours-titre--'.$kind));
        }
    }

    private function sectionKind(string $title): string
    {
        $text = $this->normalize($title);
        if (preg_match(self::STEP, $text) === 1) {
            return 'etape';
        }
        foreach (self::KINDS as $kind => $pattern) {
            if (preg_match($pattern, $text) === 1) {
                return $kind;
            }
        }

        return 'simple';
    }

    private function normalize(string $text): string
    {
        $text = str_replace('’', "'", trim($text));
        $text = (string) preg_replace('/[\x{0300}-\x{036f}]/u', '', \Normalizer::normalize($text, \Normalizer::FORM_D) ?: $text);

        return mb_strtolower($text);
    }
}
