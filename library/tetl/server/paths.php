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
  public static function register($path, $to) {
    static::$map[$path] = $to;
  }


  /**
   * Create path with some hocus-pocus
   *
   * @return string
   */
  public static function missing($method, $arguments) {
    $params = array();

    $test = array_pop($arguments);

    if (is_assoc($test)) {
      $params = $test;
    }
    else
    {
      $test && $arguments []= $test;
    }

    $route = ! empty(static::$map[$method]) ? static::$map[$method] : strtr($method, '_', '/');
    $extra = $arguments ? '/' . join('/', $arguments) : '';

    return url_for($route . $extra, $params);
  }

}

/* EOF: ./library/tetl/server/paths.php */
