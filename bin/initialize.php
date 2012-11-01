<?php

require dirname(__DIR__).DIRECTORY_SEPARATOR.'sauce.php';
require __DIR__.DIRECTORY_SEPARATOR.'functions.php';

Sauce\Base::bind(function ($bootstrap) {
    die($bootstrap());
  });

run(function () {
    $xargs = flags();
    $mod_file = FALSE;

    foreach ($xargs as $key => $val) {
      $mod_file = path(__DIR__, 'scripts', "$key.php");
      if ( ! is_numeric($key)) {
        break;
      }
    }

    $test = array();
    $cmd  = array_shift($xargs);

    foreach ($xargs as $key => $val) {
      is_numeric($key) && $test []= $val;
    }


    if (is_file($mod_file)) {
      is_string($cmd) && array_unshift($test, $cmd);
      call_user_func(function ($xargs) {
          require func_get_arg(1);
        }, $test, $mod_file);
    } else {
      $dir = path(__DIR__, 'scripts');
      IO\Dir::open($dir, function ($file) {
          if (is_dir($file)) {
            $init_file = path($test, 'initialize.php');
            require $init_file;
          }
        });

      if (arg('help')) {
        help($cmd);
      } else {
        $cmd ? Sauce\Shell\Task::exec($cmd, $test) : help();
      }
    }

  });
