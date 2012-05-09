<?php

/**
 * Web development framework for php5.3+
 *
 * @author Alvaro Cabrera (@pateketrueke)
 * @link   https://github.com/pateketrueke/tetlphp
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


  // the root-directory (really)
  define('APP_PATH', realpath(getcwd()));


  // the default environment
  define('APP_ENV', getenv('ENV') ?: 'development');


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


  // lazy loading
  spl_autoload_register(function ($class) {
    if (is_file($core_path = __DIR__.DS.'class'.DS.$class.EXT)) {
      require $core_path;
      return TRUE;
    }

    foreach (array(dirname(LIB), APP_PATH) as $path) {
      $lib_path  = $path.DS.'library'.DS.$class;
      $lib_path .= is_dir($lib_path) ? DS.'initialize'.EXT : EXT;

      if (is_file($lib_path)) {
        require $lib_path;
        return TRUE;
      }
    }
  });


  // core libraries
  /**#@+
   * @ignore
   */
  require __DIR__.DS.'runtime'.EXT;
  require __DIR__.DS.'utilities'.EXT;
  require __DIR__.DS.'filesystem'.EXT;
  require __DIR__.DS.'conditions'.EXT;
  /**#@-*/


  // global
  config(APP_PATH.DS.'config'.EXT);

  // local
  if ( ! empty($GLOBALS['config'])) {
    config($GLOBALS['config']);
  }


  // default time zone
  $timezone = option('timezone', 'UTC');

  date_default_timezone_set($timezone);

  define('TIMEZONE', $timezone);


  // ----------------------------------------------------------------------------

  // OS temp path
  if (function_exists('sys_get_temp_dir')) {
    $temporary_files = @sys_get_temp_dir();
  } else {
    $temporary_files = getenv('TMP') ?: getenv('TEMP');

    if ( ! is_dir($temporary_files)) {
      $old = @tempnam('E', '');
      $temporary_files = @dirname($old);
      @unlink($old);
    }
  }

  define('TMP', @is_dir($temporary_files) && @is_writable($temporary_files) ? rtrim($temporary_files, DS) : '/tmp');

  ! is_dir(TMP) && mkpath(TMP);


  // initialize language settings
  require __DIR__.DS.'i18n'.DS.'initialize'.EXT;

  i18n::load_path(__DIR__.DS.'locale');


  // default error and exception hanlders
  set_exception_handler(function ($E) {
    raise(ln('exception_error', array('message' => $E->getMessage(), 'file' => $E->getFile(), 'number' => $E->getLine())));
  });

  set_error_handler(function ($errno, $errmsg, $file, $line, $trace) {
    if (($errno & error_reporting()) == $errno) {
      raise(ln('error_debug', array('error' => $errmsg, 'file' => $file, 'number' => $line)));

      return TRUE;
    }
  });

  // autoload
  foreach ((array) option('autoload') as $one) {
    $one && import($one);
  }

  debug('---');
});

/* EOF: ./framework/initialize.php */
