<?php

/**
 * MVC base view
 */

class partial extends prototype
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
  final public static function register($type, Closure $lambda) {
    static::$render[$type] = $lambda;
  }


  /**
   * Load partial view
   *
   * @param  string Filepath
   * @param  array  Local vars
   * @return string
   */
  final public static function render($file, array $vars = array()) {
    if ( ! is_file($file)) {
      raise(ln('partial.view_missing', array('path' => dirname($file), 'action' => $action)));
    }

    $type = ext(basename($file, EXT));

    if ($type && array_key_exists($type, static::$render)) {
      return call_user_func(static::$render[$type], $file, $vars);
    }

    return render($file, TRUE, array(
      'locals' => $vars,
    ));
  }


  /**
   * Fetch dynamic templates
   *
   * @param  string File path
   * @param  string Action name
   * @param  array  Local vars
   * @return string
   */
  final public static function load($from, array $vars = array()) {
    @list($action, $path) = array(basename($from), dirname($from));

    $tpl_file = findfile($path, "$action.*", FALSE, 1);

    if ( ! is_file($tpl_file)) {
      raise(ln('file_not_exists', array('name' => $from)));
    }
    return static::render($tpl_file, $vars);
  }

}

/* EOF: ./stack/library/app/base/view.php */
