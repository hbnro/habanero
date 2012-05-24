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

  // assets hashing
  private static $cache  = array();

  /**#@-*/

  public static function save() {
    $out = var_export(array_filter(static::$cache, 'is_md5'), TRUE);
    write(APP_PATH.DS.'config'.DS.'resources'.EXT, '<' . "?php return $out;\n");
  }

  public static function assign($key, $val = NULL) {
    static::$cache[$key] = $val;
  }

  public static function resolve($name) {
    static $load = FALSE;


    if ( ! $load) {
      $cache_file = APP_PATH.DS.'config'.DS.'resources'.EXT;
      if (is_file($cache_file)) {
        static::$cache = (array) include $cache_file;
      }
      $load = TRUE;
    }

    $name = str_replace(APP_PATH.DS.'views'.DS.'assets'.DS, '', $name);

    if ( ! empty(static::$cache[$name])) {
      $name = dirname($name).DS.extn($name, TRUE).static::$cache[$name].ext($name, TRUE);
    }
    return $name;
  }

  public static function build($from, $type) {
    $base_path  = APP_PATH.DS.'views'.DS.'assets';
    $base_file  = $base_path.DS.$type.DS."$from.$type";

    if (is_file($base_file)) {
      $test = read($base_file);

      if (APP_ENV <> 'production') {
        $path = ROOT.'static/'.static::resolve($base_file);
        if ($type == 'css') {
          return tag('link', array('rel' => 'stylesheet', 'href' => $path));
        } else {
          return tag('script', array('src' => $path));
        }
      } else {
        $set = array();

        // css and js
        $test = preg_replace_callback('/\s+\*=\s+(\S+)/m', function ($match)
          use($base_path, $type, &$set) {
            $test_file = $base_path.DS.$type.DS.$match[1];

            @list($path, $name) = array(dirname($test_file), basename($test_file));

            $set []= $path.DS."$name.$type";
        }, $test);


        $set = array_map(function ($val)
          use($base_path, $type) {
          $path = url_for(strtr('static'.str_replace($base_path, '', $val), '\\', '/'));
          if ($type == 'css') {
            return tag('link', array('rel' => 'stylesheet', 'href' => $path));
          } else {
            return tag('script', array('src' => $path));
          }
        }, $set);

        return join("\n", $set);
      }
    }
  }


  /**
   * @param
   * @return void
   */
  final public static function read($path) {
    $file = APP_PATH.DS.'views'.DS.'assets'.DS.$path;
    $file = findfile(dirname($file), extn($file, TRUE), FALSE, 1);

    if (is_file($file)) {
      if (preg_match('/\.(jpe?g|png|gif)$/i', $path)) {
        return read($file);
      }

      $old_file = TMP.DS.str_replace(DS, '__DS__', $path);

      if (is_file($old_file)) {
        if (filemtime($file) > filemtime($old_file)) {
          unlink($old_file);
        }
      }

      if ( ! is_file($old_file)) {
        $text = static::process($file);
        $now  = date('Y-m-d H:i:s', filemtime($file));
        $out  = sprintf("/* %s ./%s */\n%s", $now, strtr($path, '\\', '/'), $text);

        return $out;
      } else {
        return read($old_file);
      }
    }
  }


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

/* EOF: ./library/assets/assets.php */
