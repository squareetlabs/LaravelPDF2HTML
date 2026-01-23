<?php
declare(strict_types=1);

namespace Squareetlabs\LaravelPdfToHtml\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Squareetlabs\LaravelPdfToHtml\Services\CssInliner;

/**
 * Class Html
 * @package Squareetlabs\PdfToHtml
 */
class Html extends Base
{
    /** @var int */
    private $pages = 0;

    /** @var array */
    private $content = [];

    /** @var array */
    private $defaultOptions = [
        'inlineCss' => true,
        'inlineImages' => true,
        'onlyContent' => false,
        'outputDir' => ''
    ];

    /**
     * Html constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions(array_replace_recursive($this->defaultOptions, $options));
    }

    /**
     * Add page to collection with the conversion, according to options.
     * @param int $number
     * @param string $content
     * @return $this
     */
    public function addPage(int $number, string $content): self
    {
        if ($this->getOptions('inlineCss')) {
            $content = $this->setInlineCss($content);
        }

        if ($this->getOptions('inlineImages')) {
            $content = $this->setInlineImages($content);
        }

        if ($this->getOptions('onlyContent')) {
            $content = $this->setOnlyContent($content);
        }

        $this->content[$number] = $content;
        $this->pages = count($this->content);
        return $this;
    }

    /**
     * @param int $number
     * @return string|null
     */
    public function getPage(int $number): ?string
    {
        return $this->content[$number] ?? null;
    }

    /**
     * @return array
     */
    public function getAllPages(): array
    {
        return $this->content;
    }

    /**
     * The method replaces css class to inline css rules.
     * @param string $content
     * @return string
     */
    private function setInlineCss(string $content): string
    {
        $content = str_replace(['<!--', '-->'], '', $content);
        return CssInliner::fromHtml($content)->inlineCss()->render();
    }

    /**
     * The method looks for images in html and replaces the src attribute to base64 hash.
     * @param string $content
     * @return string
     */
    private function setInlineImages(string $content): string
    {
        // Suppress warnings for malformed HTML
        $dom = new DOMDocument();
        @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        // Sometimes images are namespaced or not, just search widely
        $images = $xpath->query("//img");

        $modified = false;

        foreach ($images as $img) {
            /** @var DOMElement $img */
            $attrImage = $img->getAttribute('src');
            if (!$attrImage)
                continue;

            $imagePath = $this->getOutputDir() . '/' . basename($attrImage);

            if (file_exists($imagePath)) {
                $imageData = base64_encode(file_get_contents($imagePath));
                $mime = mime_content_type($imagePath);
                $src = 'data: ' . $mime . ';base64,' . $imageData;

                // Update DOM directly instead of str_replace which might be risky with duplicate strings
                $img->setAttribute('src', $src);
                $modified = true;
            }
        }

        if ($modified) {
            return $dom->saveHTML();
        }

        return $content;
    }

    /**
     * The method takes from html body content only.
     * @param string $content
     * @return string
     */
    private function setOnlyContent(string $content): string
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        $html = '';
        $body = $xpath->query("//body")->item(0);

        if ($body) {
            foreach ($body->childNodes as $node) {
                $html .= $dom->saveHTML($node);
            }
        } else {
            // Fallback if no body tag found, return everything inside?
            // Or maybe the content IS the body content if it was partial?
            // If we loaded whole HTML, body should exist.
            return $content; // Return original if parsing failed to find body
        }

        return trim($html);
    }
}
?>
