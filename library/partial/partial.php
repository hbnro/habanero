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

  /**#@-*/


  /**
   * Register adapter
   *
   * @param  mixed File type|Types
   * @param  mixed Function callback
   * @return void
   */
  final public static function register($type, Closure $lambda) {
    if (is_array($type)) {
      foreach ($type as $one) {
        static::$render[$one] = $lambda;
      }
    } else {
      static::$render[$type] = $lambda;
    }
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

    $start  = ticks();
    $test   = TMP.DS.strtr($file, array(DS => '__DS__'));
    $parts  = explode('.', basename($file));
    $output = read($file);

    write($test, $output);

    while ($parts) {
      $type = array_pop($parts);

      if ((sizeof($parts) > 1) && array_key_exists($type, static::$render)) {
        $output = call_user_func(static::$render[$type], $test, $vars);

        write($test, $output);

        continue;
      }
      break;
    }

    @unlink($test);

    $path = str_replace('__DS__', DS, basename($test));
    debug("Render: ($path)\n", '  ', ticks($start));

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
