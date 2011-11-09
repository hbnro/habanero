<?php

require dirname(__DIR__).'/framework/initialize.php';
require __DIR__.DS.'app_generator'.EXT;
require __DIR__.DS.'functions'.EXT;

run(function () {
  import('console');

  define('getcwd()', realpath(getcwd()));

  i18n::load_path(__DIR__.DS.'locale');

  config(getcwd().DS.'config'.EXT);
  config(getcwd().DS.'config'.DS.'application'.EXT);
  config(getcwd().DS.'config'.DS.'environments'.DS.option('environment').EXT);


  $args = cli::args();

  is_file($mod_file = __DIR__.DS.'scripts'.DS.key($args).EXT) && die(require $mod_file);

  foreach (option('import_path') as $path) {
    foreach (findfile($path, 'generator'.EXT, TRUE) as $gen_file) {
      /**
       * @ignore
       */
      require $gen_file;
    }
  }


  $test = array();
  $cmd  = array_shift($args);

  foreach ($args as $key => $val) {
    is_numeric($key) && $test []= $val;
  }

  is_string($cmd) ? app_generator::exec($cmd, $test) : help();
});

/* EOF: ./stack/initialize.php */
