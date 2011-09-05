<?php

/**
 * Web development framework for php5.3+
 *
 * @author  Alvaro Cabrera (@pateketrueke)
 * @link    https://github.com/pateketrueke/tetlphp
 */

// framework version
define('VER', '1.0.20');


// filename extension
define('EXT', substr(__FILE__, strrpos(__FILE__, '.')));


// system root
define('LIB', __DIR__);


// -dumb-
define('DS', DIRECTORY_SEPARATOR);



// core libraries
/**#@+
 * @ignore
 */
require LIB.DS.'core'.DS.'runtime'.EXT;
require LIB.DS.'core'.DS.'utilities'.EXT;

require LIB.DS.'core'.DS.'prototype'.EXT;
require LIB.DS.'core'.DS.'bootstrap'.EXT;

require LIB.DS.'core'.DS.'filesystem'.EXT;
require LIB.DS.'core'.DS.'conditions'.EXT;
/**#@-*/



// do!
call_user_func(function()
{
  // the root-script

  $trace = debug_backtrace();
  $trace = array_pop($trace);

  define('APP_LOADER', realpath($trace['file']));


  // the root-directory
  define('APP_PATH', dirname(APP_LOADER));


  // the root-script name
  define('INDEX', basename(APP_LOADER));



  // PCRE+Unicode
  error_reporting(0);
  ini_set('log_errors', 0);
  ini_set('display_errors', 0);
  define('IS_UNICODE', @preg_match('/\pL/u', 'ñ') > 0);

  error_reporting(E_ALL |~E_STRICT);
  ini_set('display_errors', 1);
  ini_set('log_errors', 1);


  // useful constants
  define('IS_WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
  define('IS_CLI', (bool) defined('STDIN'));


  // global
  $config_set = array(
    LIB.DS.'config'.EXT,
    APP_PATH.DS.'config'.EXT,
  );

  foreach ($config_set as $config_file)
  {
    if (is_file($config_file))
    {
      config($config_file);
    }
  }


  // local
  if ( ! empty($GLOBALS['config']))
  {
    config($GLOBALS['config']);
  }


  // lazy loading
  spl_autoload_register(function($class)
  {
    foreach (rescue() as $callback)
    {
      $callback($class);
    }

    ! class_exists($class) && raise(ln('class_not_exists', array('name' => $class)));
  }, TRUE, TRUE);
});

/* EOF: ./initialize.php */
