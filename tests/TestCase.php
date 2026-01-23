<?php

namespace Squareetlabs\LaravelPdfToHtml\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Squareetlabs\LaravelPdfToHtml\PdfToHtmlServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PdfToHtmlServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'PdfToHtml' => \Squareetlabs\LaravelPdfToHtml\Support\Facades\PdfToHtml::class,
        ];
    }
}
?>
