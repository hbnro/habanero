<?php

require dirname(dirname(__DIR__)).'/framework/initialize.php';
require __DIR__.DS.'functions'.EXT;

run(function () {
  import('tetl/console');

  i18n::load_path(__DIR__.DS.'locale', 'tetl');


  if (cli::flag('s')) {
    // TODO: random famoushipsterious-cite picker?
    cli::writeln(ln('tetl.hello_people'));
    exit;
  }

  $path = getcwd();
  $args = cli::args();

  define('CWD', realpath($path));

  config(CWD.DS.'config'.EXT);
  config(CWD.DS.'config'.DS.'application'.EXT);
  config(CWD.DS.'config'.DS.'environments'.DS.option('environment').EXT);


  $extra    = key($args);
  $mod_file = dirname(__DIR__).DS.'mods'.DS."_$extra".EXT;

  is_file($mod_file) && die(require $mod_file);


  $cmd   = array_shift($args);

  @list($module, $action) = explode('.', $cmd);

  $test = dir2arr(dirname(__DIR__).DS.'mods', '*');

  if ( ! empty($module)) {
    $mod_file = dirname(__DIR__).DS.'mods'.DS.$module.DS.'generator'.EXT;
    $mod_class = "{$module}_generator";

    is_file($mod_file) && require $mod_file;

    if ( ! class_exists($mod_class)) {
      if (is_file($mod_file)) {
        $mod_file = dirname(__DIR__).DS.'mods'.DS.$module.EXT;

        is_file($mod_file) && die(require $mod_file);
      }
      return help($test);
    }


    $mod_class::implement('help', function ()
      use($module) {
      $help = ln("$module.generator_usage");

      cli::write(cli::format("$help\n"));
    });

    if (empty($action) OR ! $mod_class::defined($action)) {
      $mod_class::help();
    } else {
      $test   =
      $params = array();

      foreach ($args as $key => $val) {
        is_numeric($key) ? $test []= $val : $params[$key] = $val;
      }

      call_user_func_array("$mod_class::$action", $test);
    }
  } else {
    help($test);
  }
});

/* EOF: ./stack/console/initialize.php */
