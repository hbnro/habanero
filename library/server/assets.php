<?php

/**
 * Basic asset manager
 */

class assets extends prototype
{// TODO: plus plus...

  /**#@+
   * @ignore
   */

  // groups
  public static $set = array(
                  'head' => array(),
                  'body' => array(),
                  'css' => array(),
                  'js' => array(),
                );

  // compile filters
  private static $filter = array();

  /**#@-*/



  /**
   * @param
   * @param
   * @param
   * @return void
   */
  final public static function url_for($path, $prefix = '', $host = FALSE) {
    return is_url($path) ? $path : path_to(($prefix ? $prefix : ext($path)).DS.$path, $host);
  }


  /**
   * @param
   * @param
   * @return void
   */
  final public static function tag_for($path, $type = '') {
    switch ($type ?: ext($path)) {
      case 'css';
        return tag('link', array(
          'rel' => 'stylesheet',
          'type' => 'text/css',
          'href' => static::url_for($path, 'css'),
        ));
      break;
      case 'js';
        return tag('script', array('src' => static::url_for($path, 'js')));
      break;
      case 'jpeg';
      case 'jpg';
      case 'png';
      case 'gif';
      case 'ico';
        return tag('img', array(
          'src' => static::url_for($path, 'img'),
          'alt' => $path,
        ));
      default;
      break;
    }
  }


  /**
   * @param
   * @param
   * @param
   * @return void
   */
  final public static function inline($code, $to = '', $before = FALSE) {
    static::push($to ?: 'head', $code, $before);
  }


  /**
   * @param
   * @param
   * @param
   * @return void
   */
  final public static function script($path, $to = '', $before = FALSE) {
    static::push($to ?: 'head', tag('script', array('src' => static::url_for($path))), $before);
  }


  /**
   * @param
   * @param
   * @return void
   */
  final public static function append($path, $to = '') {
    is_url($path) ? static::script($path, $to) : static::push($to ?: ext($path), $path);
  }


  /**
   * @param
   * @param
   * @return void
   */
  final public static function prepend($path, $to = '') {
    is_url($path) ? static::script($path, $to, TRUE) : static::push($to ?: ext($path), $path, TRUE);
  }


  /**
   * @param
   * @return string
   */
  final public static function favicon($path = '') {
    return tag('link', array('rel' => 'shortcut icon', 'href' => static::image($path ?: 'favicon.ico')));
  }


  /**
   * @param
   * @return string
   */
  final public static function image($path) {
    return static::url_for($path, 'img');
  }


  /**
   * @return string
   */
  final public static function before() {
    return join("\n", static::$set['head']);
  }


  /**
   * @return string
   */
  final public static function after() {
    return join("\n", static::$set['body']);
  }


  /**
   * @param
   * @return void
   */
  final public static function compile($type, Closure $lambda) {
    static::$filter[$type] = $lambda;
  }


  /**
   * @param
   * @param
   * @return void
   */
  final public static function missing($method, $arguments) {
    switch ($method) {
      case 'css';
      case 'js';
        $out       = array();
        $base_path = APP_PATH.DS.'public'.DS.$method;

        foreach (static::$set[$method] as $file) {
          if (is_file($file)) {
            $text  = static::process($file);
            $path  = str_replace(APP_PATH.DS, '', $file);
            $now   = date('Y-m-d H:i:s', filemtime($file));

            $out []= sprintf("/* %s ./%s */\n%s", $now, strtr($path, '\\', '/'), $text);
          }
        }

        $output  = join("\n", $out);
        $output .= join("\n", $arguments);

        return $output;
      break;
      default;
        raise(ln('method_missing', array('class' => get_called_class(), 'name' => $method)));
      break;
    }
  }



  /**#@+
   * @ignore
   */

  // type compiler
  final private static function process($file) {
    $type = ext($file);
    if ( ! empty(static::$filter[$type])) {
      return call_user_func(static::$filter[$type], $file);
    }
    return read($file);
  }

  // generic aggregator
  final private static function push($on, $test, $prepend = FALSE) {
    $prepend ? array_unshift(static::$set[$on], $test) : static::$set[$on] []= $test;
  }

  /**#@-*/

}

/* EOF: ./library/server/assets.php */
