<?php

require dirname(__DIR__).'/framework/initialize.php';

i18n::load_path(__DIR__.DS.'locale');

require __DIR__.DS.'app_generator'.EXT;
require __DIR__.DS.'functions'.EXT;

run(function () {
  cli::clear();

  $args     = cli::args();
  $mod_file = FALSE;

  foreach ($args as $key => $val) {
    $mod_file = __DIR__.DS.'scripts'.DS.$key.EXT;
    if ( ! is_numeric($key)) {
      break;
    }
  }

  $test = array();
  $cmd  = array_shift($args);

  foreach ($args as $key => $val) {
    is_numeric($key) && $test []= $val;
  }


  array_map('app_generator::task', dir2arr(mkpath(APP_PATH.DS.'tasks'), '*'.EXT));

  if ($set = findfile(APP_PATH.DS.'tasks', 'initialize'.EXT, TRUE)) {
    foreach ($set as $task_file) {
      /**
       * @ignore
       */
      require $task_file;
    }
  }


  if (is_file($mod_file)) {
    ! is_bool($cmd) && array_unshift($test, $cmd);
    call_user_func(function ($args) {
      require func_get_arg(1);
    }, $test, $mod_file);
  } else {
    foreach (array(dirname(LIB), APP_PATH) as $path) {
      if ($test = findfile($path.DS.'library', 'generator'.EXT, TRUE)) {
        foreach ($test as $gen_file) {
          /**
           * @ignore
           */
          require $gen_file;
        }
      }
    }


    if (cli::flag('help')) {
      cli::write(cli::format(app_generator::help($cmd)));
    } else {
      is_string($cmd) ? app_generator::exec($cmd, $test) : help();
    }
  }
});

/* EOF: ./stack/initialize.php */
