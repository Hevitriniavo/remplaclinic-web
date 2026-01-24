<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'contact' => [
        'path' => './assets/pages/contact.js',
        'entrypoint' => true,
    ],
    'signup' => [
        'path' => './assets/pages/signup.js',
        'entrypoint' => true,
    ],
    'signup-replacement' => [
        'path' => './assets/pages/signup-replacement.js',
        'entrypoint' => true,
    ],
    'request-new' => [
        'path' => './assets/pages/request-new.js',
        'entrypoint' => true,
    ],
    'search-replacement' => [
        'path' => './assets/pages/search-replacement.js',
        'entrypoint' => true,
    ],
    'mes-demandes' => [
        'path' => './assets/pages/mes-demandes.js',
        'entrypoint' => true,
    ],
    'admin-app' => [
        'path' => './assets/admin-app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'axios' => [
        'version' => '1.13.2',
    ],
    'vanillajs-datepicker' => [
        'version' => '1.3.4',
    ],
    'vanillajs-datepicker/dist/css/datepicker-bulma.min.css' => [
        'version' => '1.3.4',
        'type' => 'css',
    ],
    'datatables.net-dt' => [
        'version' => '2.3.6',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    'datatables.net' => [
        'version' => '2.3.6',
    ],
    'datatables.net-dt/css/dataTables.dataTables.min.css' => [
        'version' => '2.3.6',
        'type' => 'css',
    ],
];
