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


  $mod = generators();

  if ( ! empty($mod[$module])) {
    $mod = (object) $mod[$module];

    /**
     * @ignore
     */
    require $mod->script;

    $mod_class = "{$module}_generator";

    $mod_class::implement('help', function ()
      use($mod) {
      cli::clear();
      cli::write(cli::format("{$mod->help}\n"));
    });

    if ( ! $action) {
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
