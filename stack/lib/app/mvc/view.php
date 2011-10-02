<?php

/**
 * MVC base view
 */

class view extends prototype
{//TODO: caching or anything else?

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
  final public static function render($file, array $vars = array())
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


  /**
   * Fetch dynamic views
   *
   * @param  string File path
   * @param  string Action name
   * @param  array  Local vars
   * @return string
   */
  final public static function load($path, array $vars = array())
  {
    @list($action, $path) = array(basename($path), dirname($path));

    $view_file = findfile($path, "$action.*", FALSE, 1);

    if ( ! is_file($view_file))
    {
      raise(ln('mvc.view_missing', array('path' => $path, 'action' => $action)));
    }
    return static::render($view_file, $vars);
  }

}

/* EOF: ./lib/app/mvc/view.php */
