<?php

/**#@+
 * @ignore
 */

require dirname(__DIR__).'/framework/initialize.php';

i18n::load_path(__DIR__.DS.'locale');

require __DIR__.DS.'app_generator'.EXT;
require __DIR__.DS.'functions'.EXT;


$set = (array) config('cli_imports');
$set = array_filter($set);
$set && import($set);


run(function () {
  # cli::clear();

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


  if (is_dir($path = APP_PATH.DS.'tasks')) {
    array_map('app_generator::task', dir2arr($path, '*'.EXT));

    $tasks = array_filter(dir2arr(APP_PATH.DS.'tasks', '*'), 'is_dir');

    foreach ($tasks as $one) {
      require $one.DS.'initialize'.EXT;
    }
  }


  if (is_file($mod_file)) {
    ! is_bool($cmd) && array_unshift($test, $cmd);
    call_user_func(function ($args) {
      require func_get_arg(1);
    }, $test, $mod_file);
  } else {
    $scripts = array_filter(dir2arr(__DIR__.DS.'scripts', '*'), 'is_dir');

    foreach ($scripts as $one) {
      require $one.DS.'initialize'.EXT;
    }

    if (cli::flag('help')) {
      cli::write(cli::format(app_generator::help($cmd)));
    } else {
      is_string($cmd) ? app_generator::exec($cmd, $test) : help();
    }
  }
});

/**#@-*/

/* EOF: ./stack/initialize.php */
