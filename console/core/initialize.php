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

  $cmd = array_shift($args);
  @list($module, $action) = explode(':', $cmd);

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
      $test =
      $params = array();

      foreach ($args as $key => $val)
      {
        if (is_numeric($key))
        {
          $test []= $val;
        }
        else
        {
          $params[$key] = $val;
        }
      }

      $module::$action($test, $params);
    }
  }
  else
  {
    help();
  }

});
