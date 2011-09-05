<?php

/**
 * Magic routes
 */

class url_for extends prototype
{

  /**#@+
   * @ignore
   */

  // paths collection
  private static $map = array();

  /**#@-*/


  /**
   * Register path
   *
   * @param  string Named path
   * @param  string Real path
   * @return void
   */
  public static function register($path, $to)
  {
    self::$map[$path] = $to;
  }


  /**
   * Create path with some hocus-pocus
   *
   * @return string
   */
  public static function missing($method, $arguments)
  {
    $params = array();

    $test = array_pop($arguments);

    if (is_assoc($test))
    {
      $params = $test;
    }
    else
    {
      $arguments []= $test;
    }

    $route = ! empty(self::$map[$method]) ? self::$map[$method] : strtr($method, '_', '/');
    $extra = $arguments ? '/' . join('/', $arguments) : '';

    return url_for($route . $extra, $params);
  }

}

/* EOF: ./lib/tetl/server/paths.php */
