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
   * @param  string Path
   * @param  array  Params
   * @return void
   */
  public static function register($path, array $params) {
    static::$map[$path] = $params;
  }


  /**
   * Create path with some hocus-pocus
   *
   * @param  string Method
   * @param  array  Arguments
   * @return string
   */
  public static function missing($method, $arguments) {
    $vars = array();
    $test = array_pop($arguments);

    if (is_assoc($test)) {
      $vars = $test;
    } else {
      $test && $arguments []= $test;
    }


    if ( ! empty(static::$map[$method])) {

      $params = static::$map[$method];
      $extra  = $arguments ? '/' . join('/', array_filter($arguments)) : '';

      @list(, $route) = explode(' ', $params['match']);

      $route  = preg_replace_callback('/:([^:()\/]+)/', function($match)
        use($vars) {
        return ! empty($vars[$match[1]]) ? $vars[$match[1]] : $match[0];
      }, $route);

      $params['action'] = "$route$extra";
    } else {
      $params['action'] = strtr($method, '_', '/');
    }

    $out = url_for($params);

    do {
      $tmp = $out;
      $out = preg_replace('/\([^()]*?\)|\/?\*\w+/', '', $out);
    } while($tmp <> $out);

    return $out;
  }

}

/* EOF: ./library/url_for.php */
