<?php

/**
 * Web development framework for php5.3+
 *
 * @author  Alvaro Cabrera (@pateketrueke)
 * @link    https://github.com/pateketrueke/tetlphp
 */

// constants tricky to raise
$_ENV['CONSTANTS_LENGTH'] = sizeof(get_defined_constants());


// framework version
define('VER', '1.0.0');


// filename extension
define('EXT', substr(__FILE__, strrpos(__FILE__, '.')));


// system root
define('LIB', __DIR__);


// -dumb-
define('DS', DIRECTORY_SEPARATOR);



// core libraries
require LIB.DS.'core'.DS.'application'.EXT;
require LIB.DS.'core'.DS.'conditions'.EXT;
require LIB.DS.'core'.DS.'filesystem'.EXT;

require LIB.DS.'core'.DS.'request'.EXT;
require LIB.DS.'core'.DS.'response'.EXT;
require LIB.DS.'core'.DS.'utilities'.EXT;



// prototyping class
class prototype
{
  
  /**#@+
   * @ignore
   */
  
  // public function stack
  protected static $_public = array();
  
  // avoid constructor
  private function __construct()
  {
  }
  
  // public method callback
  public static function __callStatic($method, $arguments = array())
  {
    if ( ! isset(self::$_public[get_called_class()][$method]))
    {
      raise(ln('method_missing', array('class' => get_called_class(), 'name' => $method)));
    }
    return call_user_func_array(self::$_public[get_called_class()][$method], $arguments);
  }
  
  /**#@-*/


  /**
   * Internal method definition
   *
   * @param  string Method name
   * @param  mixed  Closure function
   */
  public static function method($name, Closure $lambda)
  {
    self::$_public[get_called_class()][$name] = $lambda;
  }
  
  
  /**
   * Is specified method defined?
   *
   * @param  string  Method name
   * @return boolean
   */
  public static function defined($name)
  {
    if (isset(self::$_public[get_called_class()][$name]))
    {
      return TRUE;
    }
    return method_exists(get_called_class(), $name);
  }
  
}



// do!
lambda(function()
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
  define('IS_CLI', ! isset($_SERVER['HTTP_HOST']) && isset($GLOBALS['argc']));


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
});

/* EOF: ./initialize.php */
