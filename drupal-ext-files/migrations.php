<?php
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

$fileSrc = DRUPAL_ROOT . '/migrations-src/' . $_GET['q'] . '.php';

if (file_exists($fileSrc)) {
    require_once $fileSrc;
} else {
    $migrationFiles = array_diff(scandir(dirname($fileSrc)), array('.', '..'));
    foreach($migrationFiles as $migrationFile) {
        echo $migrationFile . '<br>';
    }
}
