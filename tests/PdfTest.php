<?php

namespace Squareetlabs\LaravelPdfToHtml\Tests;

use Squareetlabs\LaravelPdfToHtml\Exceptions\PdfNotFoundException;
use Squareetlabs\LaravelPdfToHtml\Support\Pdf;

class PdfTest extends TestCase
{
    public function testItThrowsExceptionIfPdfNotFound()
    {
        $this->expectException(PdfNotFoundException::class);
        $pdf = new Pdf('/path/to/non/existent/file.pdf');
    }
}
?>
