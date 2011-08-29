<?php

require dirname(dirname(__DIR__)).'/library/initialize.php';
require __DIR__.DS.'functions'.EXT;

run(function()
{ 
  import('tetl/console');
  
  i18n::load_path(__DIR__.DS.'locale');
  
  
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
      's' => 'interactive',
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
    
    $mod_file = __DIR__.DS.$module.EXT;
    
    
    if ( ! is_file($mod_file))
    {
      red(ln('file_not_exists', array('name' => $mod_file)));
      exit;
    }
    
    
    require $mod_file;
    
    if ( ! class_exists($module))
    {
      red(ln('class_not_exists', array('name' => $module)));
      exit;
    }
    
    
    if (empty($action))
    {
      $module::help();
    }
    else
    {
      if ( ! $module::defined($action))
      {
        red(ln('method_missing', array('class' => $module, 'name' => $action)));
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
  }
  else
  {
    help();
  }
  
});
