<?php
declare(strict_types=1);

namespace Squareetlabs\LaravelPdfToHtml\Tests;

use PdfToHtml;
use Squareetlabs\LaravelPdfToHtml\Support\Pdf;
use Squareetlabs\LaravelPdfToHtml\Exceptions\PdfNotFoundException;

class FacadeTest extends TestCase
{
    /**
     * Test that the facade is properly registered
     */
    public function test_facade_is_registered()
    {
        $this->assertTrue(class_exists('PdfToHtml'));
    }

    /**
     * Test facade can load a PDF file
     */
    public function test_facade_can_load_pdf()
    {
        $pdfPath = __DIR__ . '/fixtures/sample.pdf';
        
        if (!file_exists($pdfPath)) {
            $this->markTestSkipped('Sample PDF file not found for testing.');
        }

        $pdf = PdfToHtml::load($pdfPath);
        
        $this->assertInstanceOf(Pdf::class, $pdf);
    }

    /**
     * Test facade throws exception for non-existent file
     */
    public function test_facade_throws_exception_for_non_existent_file()
    {
        $this->expectException(PdfNotFoundException::class);
        
        PdfToHtml::load('/non/existent/file.pdf');
    }

    /**
     * Test facade can load PDF with options
     */
    public function test_facade_can_load_pdf_with_options()
    {
        $pdfPath = __DIR__ . '/fixtures/sample.pdf';
        
        if (!file_exists($pdfPath)) {
            $this->markTestSkipped('Sample PDF file not found for testing.');
        }

        $options = [
            'generate' => [
                'zoom' => 2.0,
                'singlePage' => true,
            ],
        ];

        $pdf = PdfToHtml::load($pdfPath, $options);
        
        $this->assertInstanceOf(Pdf::class, $pdf);
    }

    /**
     * Test facade merges config options
     */
    public function test_facade_merges_config_options()
    {
        config([
            'pdf-to-html.options.zoom' => 1.5,
            'pdf-to-html.html.inlineImages' => true,
        ]);

        $pdfPath = __DIR__ . '/fixtures/sample.pdf';
        
        if (!file_exists($pdfPath)) {
            $this->markTestSkipped('Sample PDF file not found for testing.');
        }

        $pdf = PdfToHtml::load($pdfPath, [
            'generate' => [
                'zoom' => 2.0, // Override config
            ],
        ]);

        $this->assertInstanceOf(Pdf::class, $pdf);
    }
}
