<?php

require dirname(dirname(__DIR__)).'/framework/initialize.php';
require __DIR__.DS.'functions'.EXT;

run(function () {
  import('console');

  define('CWD', realpath(getcwd()));

  i18n::load_path(__DIR__.DS.'locale', 'tetl');

  config(CWD.DS.'config'.EXT);
  config(CWD.DS.'config'.DS.'application'.EXT);
  config(CWD.DS.'config'.DS.'environments'.DS.option('environment').EXT);


  $args = cli::args();

  is_file($mod_file = __DIR__.DS.'scripts'.DS.key($args).EXT) && die(require $mod_file);


  $cmd = array_shift($args);

  @list($module, $action) = explode('.', $cmd);

  if ( ! empty($module)) {
    $mod_file = dirname(__DIR__).DS.'mods'.DS.$module.DS.'generator'.EXT;
    $mod_class = "{$module}_generator";

    is_file($mod_file) && require $mod_file;

    if ( ! class_exists($mod_class)) {
      if (is_file($mod_file)) {
        $mod_file = dirname(__DIR__).DS.'mods'.DS.$module.EXT;

        is_file($mod_file) && die(require $mod_file);
      }
      return help();
    }


    $mod_class::implement('help', function ()
      use($module) {
      $help = ln("$module.generator_usage");

      cli::write(cli::format("$help\n"));
    });

    if (empty($action)) {
      $mod_class::help();
    } else {
      $test   =
      $params = array();

      foreach ($args as $key => $val) {
        is_numeric($key) ? $test []= $val : $params[$key] = $val;
      }
      $mod_class::apply($action, $test);
    }
  } else {
    help();
  }
});

/* EOF: ./stack/console/initialize.php */
