# Migration Guide
 
Use this guide to migrate from `tonchiktm/pdf-to-html` (or the previous version of this package) to `squareetlabs/laravel-pdf-to-html`.
 
## Namespace Changes
 
The namespace has changed from `TonchikTm\PdfToHtml` to `Squareetlabs\LaravelPdfToHtml`.
 
**Find and Replace:**
 
- Old: `TonchikTm\PdfToHtml`
- New: `Squareetlabs\LaravelPdfToHtml`
 
## Class Changes
 
### `Squareetlabs\LaravelPdfToHtml\Pdf`
 
- **Constructor**: Now enforces types.
  - Old: `new Pdf($file, $options)`
  - New: `new Pdf(string $file, array $options = [])` (Strict types)
- **Method Return Types**: Methods now have return types (e.g., `countPages()` returns `int`, `getInfo()` returns `?array`).
- **Exceptions**: The package now throws specific exceptions instead of generic errors or silent failures.
  - `Squareetlabs\LaravelPdfToHtml\Exceptions\PdfNotFoundException`: Thrown if PDF file does not exist.
  - `Squareetlabs\LaravelPdfToHtml\Exceptions\BinaryNotFoundException`: Thrown if `pdftohtml` or `pdfinfo` cannot be found.
 
### `Squareetlabs\LaravelPdfToHtml\Html`
 
- **Methods**: Added strict type hints and return types.
 
## Configuration
 
The configuration file structure has been improved. If you were publishing configs, please republish:
 
```bash
php artisan vendor:publish --provider="Squareetlabs\LaravelPdfToHtml\Providers\PdfToHtmlServiceProvider"
```

## Dependencies

The package no longer depends on `pelago/emogrifier`. It uses an internal lightweight CSS inliner.
