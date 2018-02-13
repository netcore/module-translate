<?php

return [
    'menu'              => [
        'translations' => true, // seed translation menu
        'languages'    => false // seed languages menu
    ],
    'translations_file' => 'translations', // translations file name in "resources/seed_translation",

    // True means that import command will import default laravel translations which are stored in "resources/lang"
    'import_default_translations' => true,
];
