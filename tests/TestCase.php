<?php

namespace Squareetlabs\PdfToHtml\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Squareetlabs\PdfToHtml\Providers\PdfToHtmlServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PdfToHtmlServiceProvider::class,
        ];
    }
}
?>