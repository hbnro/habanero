<?php

/**
 * Web development framework for php5.3+
 *
 * @author Alvaro Cabrera (@pateketrueke)
 * @link   https://github.com/pateketrueke/habanero
 */

// bundled full-stack
$autoload = require __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

// local vendors
$vendor_dir = APP_PATH.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer';
$classmap_file = $vendor_dir.DIRECTORY_SEPARATOR.'autoload_classmap.php';
$namespaces_file = $vendor_dir.DIRECTORY_SEPARATOR.'autoload_namespaces.php';

is_file($classmap_file) && $autoload->addClassMap(require $classmap_file);

if (is_file($namespaces_file) && ($test = require $namespaces_file)) {
  foreach ($test as $key => $val) {
    $autoload->add($key, $val);
  }
}

\Sauce\Base::$autoload = $autoload;

foreach (array('library', path('app', 'models')) as $one) {
  $autoload->add('', path(APP_PATH, $one));
}

return $autoload;
