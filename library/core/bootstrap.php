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
  function enhance($lib)
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
bootstrap::implement('raise', function()
{
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
