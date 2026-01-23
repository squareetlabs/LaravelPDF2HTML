# Changelog - Implementación del Facade

## Cambios Realizados

### 1. Nuevo Facade `PdfToHtml`

**Archivo creado:** `src/Facades/PdfToHtml.php`

- Facade que proporciona una interfaz estática para acceder al servicio de conversión PDF a HTML
- Método principal: `load(string $file, array $options = [])`
- Incluye documentación PHPDoc para autocompletado en IDEs

### 2. Service Provider Actualizado

**Archivo modificado:** `src/PdfToHtmlServiceProvider.php`

Cambios realizados:
- Agregado binding singleton para `'pdf-to-html'` en el contenedor de servicios
- Implementado patrón Factory para crear instancias de `Pdf`
- Integración automática con la configuración del paquete
- Merge automático de opciones de configuración con opciones personalizadas

### 3. Composer.json Actualizado

**Archivo modificado:** `composer.json`

- Agregado alias del facade en la sección `extra.laravel.aliases`
- El alias `PdfToHtml` se registra automáticamente en Laravel

### 4. Documentación Actualizada

**Archivo modificado:** `README.md`

- Agregada sección "Using Facade (Recommended)"
- Ejemplos de uso con el Facade
- Documentación de opciones avanzadas con Facade

**Archivo creado:** `FACADE_USAGE.md`

Guía completa de uso que incluye:
- Configuración inicial
- 9 ejemplos prácticos de uso
- Uso en controladores
- Uso en comandos Artisan
- Uso en Jobs de cola
- Manejo de excepciones
- Mejores prácticas

### 5. Tests Actualizados

**Archivo creado:** `tests/FacadeTest.php`

Tests implementados:
- `test_facade_is_registered()` - Verifica que el facade está registrado
- `test_facade_can_load_pdf()` - Verifica que puede cargar archivos PDF
- `test_facade_throws_exception_for_non_existent_file()` - Verifica manejo de errores
- `test_facade_can_load_pdf_with_options()` - Verifica opciones personalizadas
- `test_facade_merges_config_options()` - Verifica merge de configuración

**Archivo modificado:** `tests/TestCase.php`

- Agregado método `getPackageAliases()` para registrar el alias del facade en tests

## Uso del Facade

### Sintaxis Básica

```php
use PdfToHtml;

$pdf = PdfToHtml::load('/path/to/document.pdf');
$html = $pdf->getHtml();
```

### Con Opciones

```php
$pdf = PdfToHtml::load('/path/to/document.pdf', [
    'generate' => [
        'zoom' => 2.0,
        'singlePage' => true,
    ],
    'html' => [
        'inlineImages' => true,
    ]
]);
```

## Ventajas del Facade

1. **Sintaxis más limpia y Laravel-ística**
2. **Integración automática con la configuración**
3. **Autocompletado en IDEs** gracias a las anotaciones PHPDoc
4. **Fácil de mockear en tests**
5. **Acceso estático conveniente**
6. **Compatible con dependency injection**

## Compatibilidad

- **Retrocompatible:** El uso directo de la clase `Pdf` sigue funcionando
- **Laravel:** Compatible con Laravel 6.x y superior
- **PHP:** Requiere PHP 8.1 o superior

## Migración

Si ya estabas usando el paquete, puedes seguir usando la clase directamente:

```php
// Método anterior (aún funciona)
use Squareetlabs\LaravelPdfToHtml\Support\Pdf;
$pdf = new Pdf('/path/to/file.pdf');

// Nuevo método con Facade (recomendado)
use PdfToHtml;
$pdf = PdfToHtml::load('/path/to/file.pdf');
```

## Testing

Para ejecutar los tests del facade:

```bash
composer test tests/FacadeTest.php
```

Para ejecutar todos los tests:

```bash
composer test
```

## Configuración

El facade utiliza automáticamente la configuración del archivo `config/pdf-to-html.php`. 

Para publicar la configuración:

```bash
php artisan vendor:publish --provider="Squareetlabs\LaravelPdfToHtml\PdfToHtmlServiceProvider" --tag="config"
```

## Soporte

Si encuentras algún problema o tienes sugerencias:
- GitHub Issues: https://github.com/squareetlabs/laravel-pdf-to-html/issues
- Email: alberto@squareet.com, jacobo@squareet.com
