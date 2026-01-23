# Guía de Uso del Facade PdfToHtml

Este documento proporciona ejemplos detallados de cómo usar el Facade `PdfToHtml` en tu aplicación Laravel.

## Configuración Inicial

### Publicar la Configuración

```bash
php artisan vendor:publish --provider="Squareetlabs\LaravelPdfToHtml\PdfToHtmlServiceProvider" --tag="config"
```

### Archivo de Configuración

El archivo `config/pdf-to-html.php` contiene las opciones por defecto:

```php
return [
    'pdftohtml_path' => env('PDFTOHTML_PATH', null),
    'pdfinfo_path' => env('PDFINFO_PATH', null),
    'output_dir' => env('PDFTOHTML_OUTPUT_DIR', ''),
    
    'options' => [
        'singlePage' => false,
        'imageJpeg' => false,
        'ignoreImages' => false,
        'zoom' => 1.5,
        'noFrames' => true,
    ],
    
    'html' => [
        'inlineCss' => true,
        'inlineImages' => true,
        'onlyContent' => false,
    ],
];
```

## Ejemplos de Uso

### 1. Uso Básico

```php
use PdfToHtml;

// Convertir un PDF a HTML
$pdf = PdfToHtml::load(storage_path('pdfs/documento.pdf'));

// Obtener el objeto HTML
$html = $pdf->getHtml();

// Obtener todas las páginas
$pages = $html->getAllPages();

// Mostrar la primera página
echo $pages[1];
```

### 2. En un Controlador

```php
namespace App\Http\Controllers;

use PdfToHtml;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function convert(Request $request)
    {
        try {
            $pdfPath = $request->file('pdf')->store('pdfs');
            
            $pdf = PdfToHtml::load(storage_path('app/' . $pdfPath));
            $html = $pdf->getHtml();
            
            return response()->json([
                'success' => true,
                'pages' => $html->getAllPages(),
                'page_count' => $pdf->countPages()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

### 3. Con Opciones Personalizadas

```php
use PdfToHtml;

$pdf = PdfToHtml::load(storage_path('pdfs/documento.pdf'), [
    'generate' => [
        'singlePage' => true,      // Generar como una sola página
        'imageJpeg' => true,        // Usar JPEG para imágenes
        'zoom' => 2.0,              // Mayor zoom
    ],
    'html' => [
        'inlineCss' => true,        // CSS inline
        'inlineImages' => true,     // Imágenes en Base64
        'onlyContent' => true,      // Solo contenido del body
    ]
]);

$html = $pdf->getHtml();
```

### 4. Procesar Múltiples PDFs

```php
use PdfToHtml;
use Illuminate\Support\Facades\File;

$pdfFiles = File::files(storage_path('pdfs'));

foreach ($pdfFiles as $file) {
    try {
        $pdf = PdfToHtml::load($file->getPathname());
        $html = $pdf->getHtml();
        
        // Guardar el HTML generado
        $htmlPath = storage_path('html/' . $file->getFilename() . '.html');
        File::put($htmlPath, $html->getAllPagesAsString());
        
    } catch (\Exception $e) {
        \Log::error("Error procesando {$file->getFilename()}: {$e->getMessage()}");
    }
}
```

### 5. Obtener Información del PDF

```php
use PdfToHtml;

$pdf = PdfToHtml::load(storage_path('pdfs/documento.pdf'));

// Obtener información del PDF
$info = $pdf->getInfo();
// Array con: pages, size, file_size, form, tagged, etc.

// Contar páginas
$pageCount = $pdf->countPages();

echo "El PDF tiene {$pageCount} páginas";
```

### 6. Trabajar con Páginas Específicas

```php
use PdfToHtml;

$pdf = PdfToHtml::load(storage_path('pdfs/documento.pdf'));
$html = $pdf->getHtml();

// Obtener una página específica
$page3 = $html->getPage(3);

// Iterar sobre todas las páginas
foreach ($html->getAllPages() as $pageNumber => $pageContent) {
    echo "Página {$pageNumber}:\n";
    echo $pageContent;
    echo "\n---\n";
}
```

### 7. En un Comando Artisan

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use PdfToHtml;

class ConvertPdfCommand extends Command
{
    protected $signature = 'pdf:convert {file}';
    protected $description = 'Convierte un archivo PDF a HTML';

    public function handle()
    {
        $file = $this->argument('file');
        
        if (!file_exists($file)) {
            $this->error("El archivo {$file} no existe");
            return 1;
        }

        try {
            $this->info("Convirtiendo {$file}...");
            
            $pdf = PdfToHtml::load($file);
            $html = $pdf->getHtml();
            
            $outputFile = str_replace('.pdf', '.html', $file);
            file_put_contents($outputFile, $html->getAllPagesAsString());
            
            $this->info("Conversión completada: {$outputFile}");
            $this->info("Páginas procesadas: {$pdf->countPages()}");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }
}
```

### 8. Con Jobs en Cola

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PdfToHtml;

class ConvertPdfToHtmlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pdfPath;
    protected $outputPath;

    public function __construct($pdfPath, $outputPath)
    {
        $this->pdfPath = $pdfPath;
        $this->outputPath = $outputPath;
    }

    public function handle()
    {
        try {
            $pdf = PdfToHtml::load($this->pdfPath);
            $html = $pdf->getHtml();
            
            file_put_contents(
                $this->outputPath,
                $html->getAllPagesAsString()
            );
            
        } catch (\Exception $e) {
            \Log::error("Error en conversión PDF: {$e->getMessage()}");
            throw $e;
        }
    }
}
```

### 9. Variables de Entorno

Puedes configurar las rutas de los binarios en tu archivo `.env`:

```env
PDFTOHTML_PATH=/usr/local/bin/pdftohtml
PDFINFO_PATH=/usr/local/bin/pdfinfo
PDFTOHTML_OUTPUT_DIR=/tmp/pdf-conversions
```

## Excepciones

El paquete puede lanzar las siguientes excepciones:

- `BinaryNotFoundException`: No se encontró el binario `pdftohtml` o `pdfinfo`
- `PdfNotFoundException`: El archivo PDF no existe o no es legible
- `PdfToHtmlException`: Error general durante la conversión

### Manejo de Excepciones

```php
use PdfToHtml;
use Squareetlabs\LaravelPdfToHtml\Exceptions\BinaryNotFoundException;
use Squareetlabs\LaravelPdfToHtml\Exceptions\PdfNotFoundException;
use Squareetlabs\LaravelPdfToHtml\Exceptions\PdfToHtmlException;

try {
    $pdf = PdfToHtml::load($filePath);
    $html = $pdf->getHtml();
    
} catch (BinaryNotFoundException $e) {
    // El binario no está instalado
    \Log::error('pdftohtml no está instalado: ' . $e->getMessage());
    
} catch (PdfNotFoundException $e) {
    // El archivo PDF no existe
    \Log::error('Archivo no encontrado: ' . $e->getMessage());
    
} catch (PdfToHtmlException $e) {
    // Error durante la conversión
    \Log::error('Error en conversión: ' . $e->getMessage());
    
} catch (\Exception $e) {
    // Cualquier otro error
    \Log::error('Error inesperado: ' . $e->getMessage());
}
```

## Mejores Prácticas

1. **Siempre maneja excepciones**: La conversión puede fallar por varios motivos
2. **Usa colas para archivos grandes**: Los PDFs grandes pueden tardar en procesarse
3. **Limpia archivos temporales**: El paquete lo hace automáticamente, pero verifica tu espacio en disco
4. **Configura las rutas de binarios**: Si los binarios no están en PATH, configúralos en `.env`
5. **Prueba con diferentes opciones**: Cada PDF puede requerir diferentes configuraciones

## Soporte

Para más información, reportar bugs o contribuir:
- GitHub: https://github.com/squareetlabs/laravel-pdf-to-html
- Email: alberto@squareet.com, jacobo@squareet.com
