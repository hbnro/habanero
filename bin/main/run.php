<?php

declare(ticks = 1);

error_reporting(-1);
date_default_timezone_set('America/Mexico_City');

call_user_func(function () use ($argv) {
  $fixed_vendors = [];
  $habanero_dir = dirname(dirname(__DIR__));
  $autoload = require join(DIRECTORY_SEPARATOR, [$habanero_dir, 'vendor', 'autoload.php']);

  if (getcwd() !== $habanero_dir) {
    array_push($fixed_vendors, getcwd());
  }

  array_map(function($vendor_dir) use (&$autoload) {
    $vendor_dir = join(DIRECTORY_SEPARATOR, [$vendor_dir, 'vendor', 'composer']);
    $classmap_file = $vendor_dir.DIRECTORY_SEPARATOR.'autoload_classmap.php';
    $namespaces_file = $vendor_dir.DIRECTORY_SEPARATOR.'autoload_namespaces.php';

    if (is_file($classmap_file)) {
      $autoload->addClassMap(require $classmap_file);
    }

    if (is_file($namespaces_file) && ($test = require $namespaces_file)) {
      foreach ($test as $key => $val) {
        $autoload->add($key, $val);
      }
    }
  }, $fixed_vendors);

  $cli = new \Clipper\Shell($argv);

  $cli->colors->alias('debug', 'c:brown');

  $cli->params->parse([
    'showHelp' => ['h', 'help', \Clipper\Params::PARAM_NO_VALUE, 'Display this help'],
  ], true);

  if ($cli->params->showHelp || (sizeof($argv) <= 1)) {
    $cmd = $cli->params->getCommand();
    $cmd = $cmd === 'bin/hs' ? $cmd : 'hs';
    $cli->writeln("\nUsage: $cmd [options] <folders|files>\n");
    $cli->writeln($cli->params->usage());
    $cli->writeln();
    exit(1);
  }

  \Sauce\Shell\Tasks::initialize($cli);

  require join(DIRECTORY_SEPARATOR, [dirname(dirname(__DIR__)), 'inc', 'runtime.php']);

  require __DIR__.DIRECTORY_SEPARATOR.'functions.php';
  require __DIR__.DIRECTORY_SEPARATOR.'initialize.php';
});
