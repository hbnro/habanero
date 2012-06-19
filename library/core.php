<?php

/**
 * Application bootstrap
 */

/**
 * @ignore
 */
final class core extends prototype
{

  /**#@+
   * @ignore
   */

  // middleware stack
  private static $middleware = array();

  // module stack
  private static $library = array();

  /**#@-*/



  /**
   * Adds extra functionality
   *
   * @param  string Module name|Array|...
   * @return void
   */
  final public static function load($set) {
    $args = is_array($set) ? $set : func_get_args();

    if (is_assoc($set)) {
      $tmp  = ! empty($set[APP_ENV]) ? $set[APP_ENV] : NULL;
      $args = $tmp ?: array();
    }

    foreach ($args as $lib) {
      $lib = strtr($lib, '\\/', DS.DS);

      if ( ! in_array($lib, static::$library)) {
        foreach (array(dirname(LIB), APP_PATH) as $path) {
          $helper_path  = $path.DS.'library'.DS.$lib;
          $helper_path .= is_dir($helper_path) ? DS.'initialize'.EXT : EXT;

          if (in_array($lib, static::$library)) {
            continue;
          } elseif (is_file($helper_path)) {
            /**
              * @ignore
              */
            $test = require $helper_path;

            is_closure($test) && static::$middleware []= $test;

            static::$library []= $lib;
          }
        }

        ! in_array($lib, static::$library) && raise(ln('file_not_exists', array('name' => $lib)));
      }
    }
  }


  /**
   * Run application
   *
   * @param  mixed Function callback
   * @return void
   */
  final public static function exec(Closure $bootstrap) {
    if (defined('BEGIN')) {
      raise(ln('application_error'));
    }


    // start
    define('BEGIN', ticks());

    foreach (static::$middleware as $callback) {
      $bootstrap = $callback($bootstrap);
    }
    $bootstrap();
  }


  /**
   * Register middleware
   *
   * @param  mixed Function callback
   * @return void
   */
  final public static function bind(Closure $middleware) {
    static::$middleware []= $middleware;
  }

}


// basic output
core::implement('raise', function ($message, $debug = NULL) {
  $var   = array();
  $args  = func_get_args();
  $trace = array_slice(debug_backtrace(FALSE), 1);


  if ( ! empty($GLOBALS['--raise-message'])) {// this could be used in fatal error scenarios
    $message = $GLOBALS['--raise-message'];
    unset($GLOBALS['--raise-message']);
  }


  foreach ($trace as $i => $on) {
    $type   = ! empty($on['type']) ? $on['type'] : '';
    $system = ! empty($on['file']) && strstr($on['file'], dirname(LIB)) ?: FALSE;
    $prefix = ! empty($on['object']) ? get_class($on['object']) : ( ! empty($on['class']) ? $on['class'] : '');

    $call   = $prefix . $type . $on['function'];


    // - app source
    // + system source
    // ~ unknown source

    $format_str = ($true = ! empty($on['file'])) ? '%s %s#%d %s()' : '~ %4$s';
    $format_val = sprintf($format_str, is_true($system) ? '+' : '-', $true ? $on['file'] : '', $true ? $on['line'] : '', $call);

    $trace[$i]  = $format_val;
  }

  $var['debug']     = $debug;
  $var['message']   = dump($message);
  $var['backtrace'] = array_reverse($trace);
  $var['route']     = IS_CLI ? @array_shift($_SERVER['argv']) : value($_SERVER, 'REQUEST_URI');

  ! IS_CLI && $var['vars'] = $_POST;


  if ( ! IS_CLI) {
    // raw headers
    foreach (headers_list() as $one) {
      list($key, $val) = explode(':', $one);

      $var['headers'][$key] = trim($val);
    }
  }


  // system info
  $var['host'] = @php_uname('n') ?: sprintf('<%s>', ln('unknown'));
  $var['user'] = 'Unknown';

  foreach (array('USER', 'LOGNAME', 'USERNAME', 'APACHE_RUN_USER') as $key) {
    if ($one = @getenv($key)) {
      $var['user'] = $one;
    }
  }


  // environment info
  $var['env'] = $_SERVER;

  foreach (array('PATH_TRANSLATED', 'DOCUMENT_ROOT', 'REQUEST_TIME', 'argc', 'argv') as $key) {
    if (isset($var['env'][$key])) {
      unset($var['env'][$key]);
    }
  }

  // received headers
  foreach ((array) $var['env'] as $key => $val) {
    if (preg_match('/^(?:PHP|HTTP|SCRIPT)/', $key)) {
      if ( ! IS_CLI && (substr($key, 0, 5) === 'HTTP_')) {// there is no need to use request object since are not required
        $var['received'][camelcase(strtolower(substr($key, 5)), TRUE, '-')] = $val;
      }
      unset($var['env'][$key]);
    }
  }


  $type     = IS_CLI ? 'txt' : 'html';
  $inc_file = LIB.DS.'assets'.DS.'views'.DS."raise.$type".EXT;

  $output = call_user_func(function ()
    use($inc_file, $var) {
    ob_start();
    extract($var);
    require $inc_file;
    return ob_get_clean();
  });

  die($output);
});

/* EOF: ./library/core.php */
