<?php

namespace Squareetlabs\LaravelPdfToHtml\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Squareetlabs\LaravelPdfToHtml\Support\Services\PdfToHtmlService;

/**
 * @method static \Squareetlabs\LaravelPdfToHtml\Support\Pdf load(string $file, array $options = [])
 *
 * @see PdfToHtmlService
 */
class PdfToHtml extends Facade
{
    /**
     * Gets the facade name.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return PdfToHtmlService::class;
    }
}
