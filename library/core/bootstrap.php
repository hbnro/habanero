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

  /**#@-*/



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

/* EOF: ./core/application.php */
