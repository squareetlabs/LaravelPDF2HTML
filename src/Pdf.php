<?php
declare(strict_types=1);

namespace Squareetlabs\PdfToHtml;

use Squareetlabs\PdfToHtml\Support\Base;
use Squareetlabs\PdfToHtml\Exceptions\BinaryNotFoundException;
use Squareetlabs\PdfToHtml\Exceptions\PdfNotFoundException;

/**
 * Class Pdf
 * @package Squareetlabs\PdfToHtml
 */
class Pdf extends Base
{
    /** @var string */
    private $file;

    /** @var array|null */
    private $info = null;

    /** @var Html|null */
    private $html = null;

    /** @var string|null */
    private $result = null;

    /** @var array */
    private $defaultOptions = [
        'pdftohtml_path' => null, // Will try to auto-discover
        'pdfinfo_path' => null,   // Will try to auto-discover
        'generate' => [
            'singlePage' => false,
            'imageJpeg' => false,
            'ignoreImages' => false,
            'zoom' => 1.5,
            'noFrames' => true,
        ],
        'outputDir' => '',
        'removeOutputDir' => false,
        'clearAfter' => true,
        'html' => [
            'inlineImages' => true,
        ]
    ];

    /**
     * Pdf constructor.
     * @param string $file
     * @param array $options
     * @throws PdfNotFoundException
     * @throws BinaryNotFoundException
     */
    public function __construct(string $file, array $options = [])
    {
        if (!file_exists($file)) {
            throw new PdfNotFoundException("File '{$file}' not found or not readable.");
        }

        $this->file = $file;
        $this->setOptions(array_replace_recursive($this->defaultOptions, $options));

        $this->resolveBinaries();

        $this->setInfoObject();
        $this->setHtmlObject();
    }

    /**
     * Try to resolve paths for pdftohtml and pdfinfo if not provided.
     * @throws BinaryNotFoundException
     */
    private function resolveBinaries(): void
    {
        if (empty($this->getOptions('pdftohtml_path'))) {
            $path = $this->findBinary('pdftohtml');
            if (!$path) {
                throw new BinaryNotFoundException("Could not find 'pdftohtml' binary.");
            }
            $this->setOptions('pdftohtml_path', $path);
        }

        if (empty($this->getOptions('pdfinfo_path'))) {
            $path = $this->findBinary('pdfinfo');
            if (!$path) {
                throw new BinaryNotFoundException("Could not find 'pdfinfo' binary.");
            }
            $this->setOptions('pdfinfo_path', $path);
        }
    }

    private function findBinary(string $binary): ?string
    {
        $output = null;
        $code = 0;

        // Try 'which' command on Linux/macOS
        exec("which {$binary}", $output, $code);
        if ($code === 0 && !empty($output[0])) {
            return $output[0];
        }

        // Try common locations
        $commonPaths = [
            "/usr/bin/{$binary}",
            "/usr/local/bin/{$binary}",
            "/opt/homebrew/bin/{$binary}", // Mac Apple Silicon
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return array|null
     */
    public function getInfo(): ?array
    {
        if ($this->info === null) {
            $this->setInfoObject();
        }
        return $this->info;
    }

    /**
     * @return int
     */
    public function countPages(): int
    {
        if ($this->info === null) {
            $this->setInfoObject();
        }
        return (int) ($this->info['pages'] ?? 0);
    }

    /**
     * @return Html
     */
    public function getHtml(): Html
    {
        if ($this->html === null) {
            $this->setHtmlObject();
        }
        // If content not yet loaded, try to load it
        if (empty($this->html->getAllPages())) {
            $this->getContent();
        }
        return $this->html;
    }

    /**
     * @param string $dir
     * @return $this
     */
    public function setOutputDir($dir)
    {
        if ($this->html) {
            $this->html->setOutputDir($dir);
        }
        return parent::setOutputDir($dir);
    }

    /**
     * @return $this
     */
    private function setInfoObject()
    {
        $cmd = escapeshellcmd($this->getOptions('pdfinfo_path')) . ' ' . escapeshellarg($this->file);
        $content = shell_exec($cmd);

        if (!$content) {
            return $this; // Or throw exception?
        }

        $options = explode("\n", $content);
        $info = [];
        foreach ($options as $item) {
            if (!empty($item) && strpos($item, ':') !== false) {
                list($key, $value) = explode(':', $item, 2);
                $info[str_replace([' '], ['_'], strtolower(trim($key)))] = trim($value);
            }
        }
        $this->info = $info;
        return $this;
    }

    /**
     * @return $this
     */
    private function setHtmlObject()
    {
        $this->html = new Html($this->getOptions('html'));
        return $this;
    }

    /**
     * Parse PDF and populate HTML object.
     */
    private function getContent(): void
    {
        $outputDir = $this->getOptions('outputDir');
        if (!$outputDir) {
            $outputDir = sys_get_temp_dir() . '/pdf2html_' . uniqid();
        }

        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // Ensure we update our output dir setting so we know where to look
        $this->setOutputDir($outputDir);

        $this->generate();

        $fileinfo = pathinfo($this->file);
        $baseName = $fileinfo['filename'];
        $basePath = $this->getOutputDir() . '/' . $baseName;

        $countPages = $this->countPages();

        if ($countPages > 0) {
            // Depending on pdftohtml output:
            // If single page: just filename.html
            // If complex/multiple pages: filename-1.html, filename-2.html, etc OR filename.html (index) + frames
            // The logic here assumes -c (complex) or -s (single).
            // Usually -c generates data-1.html, data-2.html etc.

            // Let's rely on checking files existence or count logic
            if ($countPages > 1) {
                for ($i = 1; $i <= $countPages; $i++) {
                    $pageFile = $basePath . '-' . $i . '.html';
                    if (file_exists($pageFile)) {
                        $content = file_get_contents($pageFile);
                        $this->html->addPage($i, $content);
                    }
                }
            } else {
                // Sometimes single page might have suffix -1.html too if using -c, but usually just .html with -s?
                // The original code handled >1 and else.
                $singleFile = $basePath . '.html';
                // Fallback to checking -1.html if .html doesn't exist?
                if (!file_exists($singleFile) && file_exists($basePath . '-1.html')) {
                    $singleFile = $basePath . '-1.html';
                }

                if (file_exists($singleFile)) {
                    $content = file_get_contents($singleFile);
                    $this->html->addPage(1, $content);
                }
            }
        }

        if ($this->getOptions('clearAfter')) {
            $this->clearOutputDir((bool) $this->getOptions('removeOutputDir'));
        }
    }

    /**
     * @return $this
     */
    private function generate()
    {
        $this->result = null;
        $command = $this->getCommand();
        // Redirect stderr to null to avoid cluttering or handle error
        exec($command . ' 2>&1', $output, $returnVar);
        $this->result = implode("\n", $output);

        return $this;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        // Force no frames if multiple pages unless specified otherwise, 
        // but typically we want raw HTML pages to parse.
        if ($this->countPages() > 1) {
            // The original code forced noFrames = false (meaning frames are generated?)
            // Actually -noframes means NO frames. false -> generate frames.
            // If we want to loop over pages, we probably want individual page files.
            // -c (complex) generates page-1.html etc.
            // Let's trust original logic but ensure efficiency.
            $this->setOptions(['generate' => ['noFrames' => false]]);
        }

        $outputFile = $this->getOutputDir() . '/' . preg_replace("/\.pdf$/", '', basename($this->file)) . '.html';
        $optionsStr = $this->generateOptions();

        return escapeshellcmd($this->getOptions('pdftohtml_path')) . ' ' . $optionsStr . ' ' . escapeshellarg($this->file) . ' ' . escapeshellarg($outputFile);
    }

    /**
     * @return string|null
     */
    public function getResult(): ?string
    {
        return $this->result;
    }

    /**
     * @return string
     */
    private function generateOptions(): string
    {
        $generated = [];
        $generateValue = $this->getOptions('generate');

        foreach ($generateValue as $key => $value) {
            switch ($key) {
                case 'singlePage':
                    $generated[] = $value ? '-s' : '-c'; // -s: single page, -c: complex (default)
                    break;
                case 'imageJpeg':
                    $generated[] = '-fmt ' . ($value ? 'jpg' : 'png');
                    break;
                case 'zoom':
                    if ($value)
                        $generated[] = '-zoom ' . $value;
                    break;
                case 'ignoreImages':
                    if ($value)
                        $generated[] = '-i'; // ignore images
                    break;
                case 'noFrames':
                    if ($value)
                        $generated[] = '-noframes';
                    break;
            }
        }

        return implode(' ', $generated);
    }
}
?>