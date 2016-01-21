<?php

/**
 * Web development framework for php5.3+
 *
 * @author Alvaro Cabrera (@pateketrueke)
 * @link   https://github.com/pateketrueke/habanero
 */

$autoload = require getcwd().DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

if (! isset($autoload)) {
  throw new \Exception("The object \$autoload is missing from the scope");
} elseif (! ($autoload instanceof \Composer\Autoload\ClassLoader)) {
  throw new \Exception("The \$autoload is not a \\Composer\\Autoload\\ClassLoader");
}

call_user_func(function ()
  use ($autoload) {

    // local vendors
    $vendor_dir = __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer';
    $classmap_file = $vendor_dir.DIRECTORY_SEPARATOR.'autoload_classmap.php';
    $namespaces_file = $vendor_dir.DIRECTORY_SEPARATOR.'autoload_namespaces.php';

    is_file($classmap_file) && $autoload->addClassMap(require $classmap_file);

    if (is_file($namespaces_file) && ($test = require $namespaces_file)) {
      foreach ($test as $key => $val) {
        $autoload->add($key, $val);
      }
    }


    if (! empty($_SERVER['argv'])) {
      $params = new \Clipper\Params;
      $params->parse(array(
        'env' => array('e', 'env'),
      ));

      $params['env'] && define('ENV', $params['env']);
    }


    // bundled full-stack
    require __DIR__.DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'initialize.php';
  });

return $autoload;
