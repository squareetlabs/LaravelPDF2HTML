<?php

namespace Squareetlabs\LaravelPdfToHtml\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Squareetlabs\LaravelPdfToHtml\Providers\PdfToHtmlServiceProvider;

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