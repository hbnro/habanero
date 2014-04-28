<?php

error_reporting(-1);
date_default_timezone_set('America/Mexico_City');

call_user_func(function () {
  $vendor_dirs = array(
    getcwd(),
    dirname(__DIR__),
    dirname(dirname(__DIR__)),
    dirname(dirname(dirname(__DIR__))),
  );


  foreach ($vendor_dirs as $path) {
    $vendor_file = join(array($path, 'vendor', 'autoload.php'), DIRECTORY_SEPARATOR);
    if (is_file($vendor_file)) {
      $autoload = require $vendor_file;
      break;
    }
  }

  if (!function_exists('run')) {
    require dirname(__DIR__).DIRECTORY_SEPARATOR.'sauce.php';
  }

  require __DIR__.DIRECTORY_SEPARATOR.'functions.php';
});


\Sauce\Base::bind(function ($bootstrap) {
    die($bootstrap());
  });

run(function () {
    \Sauce\Shell\CLI::initialize(dirname(__DIR__));

    $xargs = \Sauce\Shell\CLI::values();
    $command = array_shift($xargs);
    $status = 0;

    $paths = array();
    $paths []= path(__DIR__, 'tasks');
    is_dir($app_tasks = path(APP_PATH, 'tasks')) && $paths []= $app_tasks;

    \IO\Dir::open($paths, function ($file) {
        if (is_dir($file)) {
          require path($file, 'initialize.php');
        } else {
          require $file;
        }
      });

    if (! $command) {
      help(arg('help'));
    } else {
      writeln(colorize(sprintf('Environment: \cyellow(%s)\c', APP_ENV)));

      try {
        \Sauce\Shell\Task::exec($command, $xargs);

        success('Done, without errors.');
      } catch (\Exception $e) {
        $status = 1;

        error("\n  \cred,black({$e->getMessage()})\c\n");
        notice('Aborted due warnings.');
      }
    }

    exit($status);
  });
