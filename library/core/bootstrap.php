<?php

/**
 * Application bootstrap
 */

final class bootstrap extends prototype
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
   * @param  string Module name
   * @return void
   */
  final public static function enhance($lib)
  {
    $lib = strtr($lib, '\\/', DS.DS);

    if ( ! in_array($lib, bootstrap::$library))
    {
      $test   = (array) option('import_path', array());
      $test []= LIB.DS.'lib'.DS;


      foreach ($test as $dir)
      {
        $helper_path  = $dir.$lib;
        $helper_path .= is_dir($helper_path) ? DS.'initialize'.EXT : EXT;

        if (is_file($helper_path))
        {
          break;
        }
      }

      // fallback, do not use i18n...
      if ( ! is_loaded($helper_path))
      {
        /**
          * @ignore
          */
        require $helper_path;

        bootstrap::$library []= $lib;
      }
    }
  }


  /**
   * Run application
   *
   * @param  mixed Function callback
   * @return void
   */
  final public static function execute(Closure $bootstrap)
  {
    require_once LIB.DS.'core'.DS.'initialize'.EXT;

    if (defined('BEGIN'))
    {
      raise(ln('application_error'));
    }


    // start
    define('BEGIN', ticks());

    foreach (bootstrap::$middleware as $callback)
    {
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
  final public static function bind(Closure $middleware)
  {
    bootstrap::$middleware []= $middleware;
  }

}


// basic output
bootstrap::implement('raise', function($message)
{
  $var   = array();
  $args  = func_get_args();
  $trace = array_slice(debug_backtrace(), 1);


  // finalize opened buffers
  while (ob_get_level())
  {
    ob_end_clean();
  }

  if ( ! empty($GLOBALS['--raise-message']))
  {// this could be used in fatal error scenarios
    $message = $GLOBALS['--raise-message'];
    unset($GLOBALS['--raise-message']);
  }


  foreach ($trace as $i => $on)
  {
    $type   = ! empty($on['type']) ? $on['type'] : '';
    $system = ! empty($on['file']) && strstr($on['file'], LIB) ?: FALSE;
    $prefix = ! empty($on['object']) ? get_class($on['object']) : ( ! empty($on['class']) ? $on['class'] : '');

    $call   = $prefix . $type . $on['function'];


    // - app source
    // + system source
    // ~ unknown source

    $format_str = ($true = ! empty($on['file'])) ? '%s %s#%d %s()' : '~ %4$s';
    $format_val = sprintf($format_str, is_true($system) ? '+' : '-', $true ? $on['file'] : '', $true ? $on['line'] : '', $call);

    $trace[$i]  = $format_val;
  }

  $var['message']   = dump($message);
  $var['backtrace'] = array_reverse($trace);
  $var['route']     = IS_CLI ? @array_shift($_SERVER['argv']) : value($_SERVER, 'REQUEST_URI');

  if ( ! IS_CLI)
  {
    // raw headers
    foreach (headers_list() as $one)
    {
      list($key, $val) = explode(':', $one);

      $var['headers'][$key] = trim($val);
    }
  }


  // system info
  $var['host'] = php_uname('n');
  $var['user'] = 'Unknown';

  foreach (array('USER', 'LOGNAME', 'USERNAME', 'APACHE_RUN_USER') as $key)
  {
    if ($one = @getenv($key))
    {
      $var['user'] = $one;
    }
  }


  // environment info
  $var['env'] = $_SERVER;

  foreach (array('PATH_TRANSLATED', 'DOCUMENT_ROOT', 'REQUEST_TIME', 'argc', 'argv') as $key)
  {
    if (isset($var['env'][$key]))
    {
      unset($var['env'][$key]);
    }
  }

  // received headers
  foreach ((array) $var['env'] as $key => $val)
  {
    if (preg_match('/^(?:PHP|HTTP|SCRIPT)/', $key))
    {
      if ( ! IS_CLI && (substr($key, 0, 5) === 'HTTP_'))
      {// there is no need to use request object since are not required
        $var['received'][camelcase(strtolower(substr($key, 5)), TRUE, '-')] = $val;
      }
      unset($var['env'][$key]);
    }
  }


  $type     = IS_CLI ? 'txt' : 'html';
  $inc_file = LIB.DS.'assets'.DS.'views'.DS."raise.$type".EXT;

  $output = call_user_func(function()
    use($inc_file, $var)
  {
    ob_start();
    extract($var);
    require $inc_file;
    return ob_get_clean();
  });

  die($output);
});

/* EOF: ./core/application.php */
