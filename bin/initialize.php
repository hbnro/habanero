<?php

$autoload = require getcwd().DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

require dirname(__DIR__).DIRECTORY_SEPARATOR.'sauce.php';
require __DIR__.DIRECTORY_SEPARATOR.'functions.php';


\Sauce\Base::bind(function ($bootstrap) {
    die($bootstrap());
  });

run(function () {
    \Sauce\Shell\CLI::initialize();

    $xargs = \Sauce\Shell\CLI::values();
    $command = array_shift($xargs);

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
      try {
        \Sauce\Shell\Task::exec($command, $xargs);
      } catch (\Exception $e) {
        \Sauce\Shell\CLI::error("\n  \cred,black({$e->getMessage()})\c\n");
      }
    }

  });
