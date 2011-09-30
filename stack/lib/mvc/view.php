<?php

/**
 * MVC view
 */

class view extends prototype
{

  /**#@+
   * @ignore
   */

  // render adapters
  private static $render = array();

  /**#@-*/


  /**
   * Register adapter
   *
   * @param  string File type
   * @param  mixed  Function callback
   * @return void
   */
  final public static function register($type, Closure $lambda)
  {
    static::$render[$type] = $lambda;
  }


  /**
   * Load partial view
   *
   * @param  string Filepath
   * @param  array  Local vars
   * @return string
   */
  final public static function load($file, array $vars = array())
  {
    $type = ext(basename($file, EXT));

    if ($type && array_key_exists($type, static::$render))
    {
      return call_user_func(static::$render[$type], $file, $vars);
    }

    return render($file, TRUE, array(
      'locals' => $vars,
    ));
  }

}

/* EOF: ./lib/tetl/mvc/view.php */
