<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Binary Paths
    |--------------------------------------------------------------------------
    |
    | The paths to the pdftohtml and pdfinfo binaries.
    | If null, the package will attempt to auto-discover them.
    |
    */
    'pdftohtml_path' => env('PDFTOHTML_PATH', null),
    'pdfinfo_path' => env('PDFINFO_PATH', null),

    /*
    |--------------------------------------------------------------------------
    | Output Directory
    |--------------------------------------------------------------------------
    |
    | The default output directory for generated HTML files.
    | If empty, a temporary directory will be used.
    |
    */
    'output_dir' => env('PDFTOHTML_OUTPUT_DIR', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    |
    | Default options for the conversion process.
    |
    */
    'options' => [
        'singlePage' => false,
        'imageJpeg' => false,
        'ignoreImages' => false,
        'zoom' => 1.5,
        'noFrames' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | HTML Options
    |--------------------------------------------------------------------------
    */
    'html' => [
        'inlineCss' => true,
        'inlineImages' => true,
        'onlyContent' => false,
    ],
];
?>