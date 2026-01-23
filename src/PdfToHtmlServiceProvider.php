<?php
declare(strict_types=1);

namespace Squareetlabs\LaravelPdfToHtml;

use Illuminate\Support\ServiceProvider;
use function Squareetlabs\LaravelPdfToHtml\Providers\config_path;

class PdfToHtmlServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/pdf-to-html.php',
            'pdf-to-html'
        );

        // Register the factory as singleton
        $this->app->singleton('pdf-to-html', function ($app) {
            return new class {
                public function load(string $file, array $options = [])
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
                    
                    return new \Squareetlabs\LaravelPdfToHtml\Support\Pdf($file, $mergedOptions);
                }
            };
        });
    }

    /**
     * Bootstrap any package services.
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/pdf-to-html.php' => config_path('pdf-to-html.php'),
        ], 'config');
    }
}
?>
