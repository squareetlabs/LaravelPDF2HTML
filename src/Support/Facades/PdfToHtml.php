<?php
declare(strict_types=1);

namespace Squareetlabs\LaravelPdfToHtml\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Squareetlabs\LaravelPdfToHtml\Support\Pdf load(string $file, array $options = [])
 * 
 * @see \Squareetlabs\LaravelPdfToHtml\Support\Pdf
 */
class PdfToHtml extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pdf-to-html';
    }
}
