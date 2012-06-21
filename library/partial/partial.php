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

  // partial sections
  private static $sections = array();

  /**#@-*/


  /**
   * Clear section
   *
   * @param  string Name
   * @return void
   */
  final public static function clear($name) {
    if (isset(static::$sections[$name])) {
      unset(static::$sections[$name]);
    }
  }


  /**
   * Create section
   *
   * @param  string Name
   * @param  mixed  Content|Function callback
   * @return void
   */
  final public static function section($name, $content) {
    static::$sections[$name] = array($content);
  }


  /**
   * Prepend content to section
   *
   * @param  string Name
   * @param  mixed  Content|Function callback
   * @return void
   */
  final public static function prepend($section, $content) {
    isset(static::$sections[$name]) && array_unshift(static::$sections[$name], $content);
  }


  /**
   * Append content to section
   *
   * @param  string Name
   * @param  mixed  Content|Function callback
   * @return void
   */
  final public static function append($section, $content) {
    isset(static::$sections[$name]) && static::$sections[$name] []= $content;
  }


  /**
   * Retrieve section
   *
   * @param  string Name
   * @param  array  Params
   * @return string
   */
  final public static function yield($section, array $params = array()) {
    $out = '';

    if ( ! empty(static::$sections[$section])) {
      foreach (static::$sections[$section] as $one) {
        if (is_callable($one)) {
          ob_start() && call_user_func($one, $params);
          $one = ob_get_clean();
        }
        $out .= $one;
      }
    }

    return $out;
  }


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
    logger::debug("Render: ($path) ", ticks($start));

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

    $tpl_file = findfile($path, "$action*", FALSE, 1);

    if ( ! is_file($tpl_file)) {
      return ln('partial.view_missing', array('path' => $path, 'action' => $action));
    }
    return static::render($tpl_file, $vars);
  }

}

/* EOF: ./library/partial/partial.php */
