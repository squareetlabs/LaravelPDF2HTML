<?php

namespace Squareetlabs\LaravelPdfToHtml\Tests;

use Squareetlabs\LaravelPdfToHtml\Pdf;
use Squareetlabs\LaravelPdfToHtml\Exceptions\PdfNotFoundException;

class PdfTest extends TestCase
{
    public function testItThrowsExceptionIfPdfNotFound()
    {
        $this->expectException(PdfNotFoundException::class);
        $pdf = new Pdf('/path/to/non/existent/file.pdf');
    }
}
?>