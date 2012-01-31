<?php

require dirname(__DIR__).'/framework/initialize.php';

import('console');

i18n::load_path(__DIR__.DS.'locale');

require __DIR__.DS.'app_generator'.EXT;
require __DIR__.DS.'functions'.EXT;

run(function () {
  cli::clear();

  $args = cli::args();

  $mod_file = __DIR__.DS.'scripts'.DS.key($args).EXT;

  if (is_file($mod_file)) {
    require $mod_file;
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


    $test = array();
    $cmd  = array_shift($args);

    foreach ($args as $key => $val) {
      is_numeric($key) && $test []= $val;
    }

    is_string($cmd) ? app_generator::exec($cmd, $test) : help();
  }
});

/* EOF: ./stack/initialize.php */
