<?php

namespace Squareetlabs\PdfToHtml\Tests;

use Squareetlabs\PdfToHtml\Pdf;
use Squareetlabs\PdfToHtml\Exceptions\PdfNotFoundException;

class PdfTest extends TestCase
{
    public function testItThrowsExceptionIfPdfNotFound()
    {
        $this->expectException(PdfNotFoundException::class);
        $pdf = new Pdf('/path/to/non/existent/file.pdf');
    }
}
?>