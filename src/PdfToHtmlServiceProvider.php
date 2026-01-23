<?php

namespace Squareetlabs\LaravelPdfToHtml;

use Illuminate\Support\ServiceProvider;
use Squareetlabs\LaravelPdfToHtml\Support\Services\PdfToHtmlService;

class PdfToHtmlServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/pdf-to-html.php', 'pdf-to-html');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePublishing();
        $this->registerFacades();
    }

    /**
     * Configure publishing for the package.
     */
    protected function configurePublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/pdf-to-html.php' => config_path('pdf-to-html.php')
        ], 'pdf-to-html-config');
    }

    /**
     * Register the facades offered by the application.
     */
    protected function registerFacades(): void
    {
        $this->app->singleton('pdf-to-html', static function () {
            return new PdfToHtmlService();
        });
    }
}
