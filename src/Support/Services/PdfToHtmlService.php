<?php

namespace Squareetlabs\LaravelPdfToHtml\Support\Services;

use Squareetlabs\LaravelPdfToHtml\Support\Pdf;

class PdfToHtmlService
{
    /**
     * Load a PDF file and return a Pdf instance.
     *
     * @param string $file
     * @param array $options
     * @return Pdf
     */
    public function load(string $file, array $options = []): Pdf
    {
        // Build options from config
        $defaultOptions = [
            'pdftohtml_path' => config('pdf-to-html.pdftohtml_path'),
            'pdfinfo_path' => config('pdf-to-html.pdfinfo_path'),
            'outputDir' => config('pdf-to-html.output_dir', ''),
            'generate' => config('pdf-to-html.options', []),
            'html' => config('pdf-to-html.html', []),
        ];

        // Merge with provided options
        $mergedOptions = array_replace_recursive($defaultOptions, $options);

        return new Pdf($file, $mergedOptions);
    }
}
