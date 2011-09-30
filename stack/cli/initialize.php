<?php

require dirname(dirname(__DIR__)).'/library/initialize.php';
require __DIR__.DS.'functions'.EXT;

run(function()
{
  import('tetl/console');

  i18n::load_path(__DIR__.DS.'locale', 'tetl');


  $path = getcwd();
  $args = cli::args();

  define('CWD', realpath($path));

  config(CWD.DS.'config'.DS.'application'.EXT);
  config(CWD.DS.'config'.DS.'database'.EXT);

  config(CWD.DS.'config'.DS.'environments'.DS.option('environment').EXT);

  option('dsn') && import('tetl/db');


  $helper_file = CWD.DS.'helpers'.EXT;

  is_file($helper_file) && require $helper_file;


  $cmd = array_shift($args);
  @list($module, $action) = explode('.', $cmd);

  if ( ! empty($module))
  {
    $mod_list = array(
      'app' => 'application',
      'db' => 'database',
    );


    foreach ($mod_list as $key => $val)
    {
      $test = explode(':', $key);

      if (in_array($module, $test))
      {
        $module = $val;
        break;
      }
    }

    $mod_file = __DIR__.DS.'mods'.DS.$module.EXT;

    is_file($mod_file) && require $mod_file;

    if ( ! class_exists($module))
    {
      help();
    }
    elseif (empty($action) OR ! $module::defined($action))
    {
      $module::help();
    }
    else
    {
      $test   =
      $params = array();

      foreach ($args as $key => $val)
      {
        is_numeric($key) ? $test []= $val : $params[$key] = $val;
      }
      apply("$module::$action", $test);
    }
  }
  else
  {
    help();
  }
});
