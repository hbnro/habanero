<?php

/**
 * Web development framework for php5.3+
 *
 * @author  Alvaro Cabrera (@pateketrueke)
 * @link    https://github.com/pateketrueke/tetlphp
 */

// do!
call_user_func(function () {
  // filename extension
  define('EXT', '.php');


  // system root
  define('LIB', __DIR__);


  // -dumb-
  define('DS', DIRECTORY_SEPARATOR);


  // the root-script

  $trace = debug_backtrace(FALSE);
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
  require LIB.DS.'core'.DS.'configure'.EXT;
  /**#@-*/


  // filters
  configure::filter('import_path', function ($value) {
    $value = array_merge((array) $value, option('import_path', array()));
    $value = array_unique($value);

    return $value;
  });


  // global
  config(getcwd().DS.'config'.EXT);

  // local
  if ( ! empty($GLOBALS['config'])) {
    config($GLOBALS['config']);
  }


  // lazy loading
  spl_autoload_register(function ($class) {
    foreach (rescue() as $test) {
      is_closure($test) && $test($class);
      is_array($test) && ! empty($test[$class]) && require $test[$class];
    }
  });
});

/* EOF: ./framework/initialize.php */
