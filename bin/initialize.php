<?php

require dirname(__DIR__).DIRECTORY_SEPARATOR.'sauce.php';
require __DIR__.DIRECTORY_SEPARATOR.'functions.php';

\Sauce\Base::bind(function ($bootstrap) {
    die($bootstrap());
  });

run(function () {
    $xargs = flags();
    $first = key($xargs);

    $cmd = array_shift($xargs);
    $set = array(path(__DIR__, 'tasks'));

    is_dir($app_tasks = path(APP_PATH, 'tasks')) && $set []= $app_tasks;

    \IO\Dir::open($set, function ($file) {
        if (is_dir($file)) {
          require path($file, 'initialize.php');
        } else {
          require $file;
        }
      });


    if ( ! $cmd) {
      help();
    } elseif ($first === 'help') {
      help($first);
    } else {
      try {
        \Sauce\Shell\Task::exec($cmd, $xargs);
      } catch (\Exception $e) {
        \Sauce\Shell\CLI::error("\bred({$e->getMessage()})\b");
      }
    }

  });
