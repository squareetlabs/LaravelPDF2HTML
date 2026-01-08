<?php
declare(strict_types=1);

namespace Squareetlabs\LaravelPdfToHtml\Services;

use DOMDocument;
use DOMXPath;
use DOMElement;

/**
 * Simple CSS inliner implementation.
 */
class CssInliner
{
    /** @var string */
    private $html;

    /** @var array */
    private $rules = [];

    private function __construct(string $html)
    {
        $this->html = $html;
    }

    /**
     * Factory method.
     */
    public static function fromHtml(string $html): self
    {
        return new self($html);
    }

    /**
     * Parse <style> tags and collect CSS rules.
     */
    private function parseStyles(): void
    {
        // Extract content of <style> tags.
        if (preg_match_all('/<style[^>]*>(.*?)<\/style>/si', $this->html, $matches)) {
            foreach ($matches[1] as $cssBlock) {
                $this->extractRules($cssBlock);
            }
        }
        // Remove the <style> tags from HTML.
        $this->html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $this->html) ?? $this->html;
    }

    /**
     * Very naive CSS rule extractor.
     */
    private function extractRules(string $cssBlock): void
    {
        // Remove comments and whitespace.
        $cssBlock = preg_replace('/\/\*.*?\*\//s', '', $cssBlock) ?? '';
        $cssBlock = trim($cssBlock);
        if ($cssBlock === '') {
            return;
        }
        // Split into individual rule blocks.
        $rules = preg_split('/}\s*/', $cssBlock);
        if (!$rules)
            return;

        foreach ($rules as $rule) {
            if (strpos($rule, '{') === false) {
                continue;
            }
            [$selectorPart, $declarationPart] = explode('{', $rule, 2);
            $selectors = array_map('trim', explode(',', $selectorPart));
            $declarations = trim($declarationPart);
            foreach ($selectors as $selector) {
                $this->rules[] = [
                    'selector' => $selector,
                    'declarations' => $declarations,
                ];
            }
        }
    }

    /**
     * Apply collected CSS rules as inline styles.
     */
    private function applyRules(): void
    {
        if (empty($this->rules)) {
            return;
        }
        $dom = new DOMDocument();
        // Suppress warnings due to malformed HTML fragments.
        @$dom->loadHTML($this->html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        foreach ($this->rules as $rule) {
            $selector = $rule['selector'];
            $declarations = $rule['declarations'];
            // Convert simple selectors to XPath.
            $xpathQuery = $this->selectorToXPath($selector);
            if ($xpathQuery === null) {
                continue; // Unsupported selector.
            }

            // Query might fail if selector is weird
            $nodes = @$xpath->query($xpathQuery);

            if ($nodes) {
                foreach ($nodes as $node) {
                    if ($node instanceof DOMElement) {
                        $existing = $node->getAttribute('style');
                        $merged = trim($existing . (empty($existing) ? '' : '; ') . $declarations);
                        $node->setAttribute('style', $merged);
                    }
                }
            }
        }
        $this->html = $dom->saveHTML() ?: $this->html;
    }

    /**
     * Convert a limited set of CSS selectors to XPath.
     */
    private function selectorToXPath(string $selector): ?string
    {
        $selector = trim($selector);
        if ($selector === '') {
            return null;
        }
        // ID selector: #my-id
        if (strpos($selector, '#') === 0) {
            $id = substr($selector, 1);
            return "//*[@id='$id']";
        }
        // Class selector: .my-class
        if (strpos($selector, '.') === 0) {
            $class = substr($selector, 1);
            return "//*[contains(concat(' ', normalize-space(@class), ' '), ' $class ') ]";
        }
        // Element selector: div, p, span, etc.
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $selector)) {
            return "//{$selector}";
        }
        return null;
    }

    public function inlineCss(): self
    {
        $this->parseStyles();
        $this->applyRules();
        return $this;
    }

    public function render(): string
    {
        return $this->html;
    }
}
?>