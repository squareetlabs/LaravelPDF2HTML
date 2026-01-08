<?php
declare(strict_types=1);

namespace Squareetlabs\PdfToHtml\Providers;

use Illuminate\Support\ServiceProvider;

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