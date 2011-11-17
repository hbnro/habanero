<?php

/**
 * Partial base view
 */

class partial extends prototype
{//TODO: caching or anything else?

  /**#@+
   * @ignore
   */

  // render adapters
  private static $render = array();

  // defaults
  protected static $defs = array(
                      'path' => APP_PATH,
                    );

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
      return ln('partial.view_missing', array('path' => dirname($file), 'action' => $action));
    }


    $parts = explode('.', basename($file));
    $name  = array_shift($parts);
    $test  = TMP.DS.md5($file);

    write($test, read($file));

    while ($parts) {
      $type = array_pop($parts);

      if ($type && array_key_exists($type, static::$render)) {
        $output = call_user_func(static::$render[$type], $test, $vars);

        write($test, $output);

        continue;
      }
      @unlink($test);
      break;
    }
    return $output;
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

    $tpl_file = findfile($path, $action, FALSE, 1);

    if ( ! is_file($tpl_file)) {
      return ln('partial.view_missing', array('path' => $path, 'action' => $action));
    }
    return static::render($tpl_file, $vars);
  }

}

/* EOF: ./library/partial/partial.php */
