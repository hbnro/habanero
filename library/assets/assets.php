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


  public static function init() {
    $cache_file = APP_PATH.DS.'config'.DS.'resources'.EXT;

    ! is_file($cache_file) && write($cache_file, '<' . "?php return array();\n");

    static::$cache = (array) include $cache_file;
  }

  public static function path($for) {
    return ($hash = static::item($for)) ? extn($for) . $hash . ext($for, TRUE) : $for;
  }

  public static function save() {
    $out = static::$cache;
    $out = var_export(array_filter($out, 'is_md5'), TRUE);

    write(APP_PATH.DS.'config'.DS.'resources'.EXT, '<' . "?php return $out;\n");
  }

  public static function assign($key, $val = NULL) {
    is_array($key) && static::$cache = array_merge(static::$cache, $key);
    is_md5($val) && static::$cache[$key] = $val;

    static::save();
  }

  public static function fetch($name) {
    return ! empty(static::$cache[$name]) ? static::$cache[$name] : FALSE;
  }

  public static function build($from, $type) {
    $out_path   = APP_PATH.DS.'static'.DS.$type;
    $base_path  = APP_PATH.DS.'views'.DS.'assets';
    $base_file  = $base_path.DS.$type.DS."$from.$type";

    if (is_file($base_file)) {
      $test = read($base_file);

      if (APP_ENV === 'production') {
        // TODO: minify without CLI?
        $out_file = $out_path.DS.$from.static::fetch("$from.$type").".min.$type";
      } else {
        $tmp = TMP.DS."$from.$type.tmp";

        // css and js
        $test = preg_replace_callback('/\s+\*=\s+(\S+)/m', function ($match)
          use($base_path, $type) {
            $test_file = $base_path.DS.$type.DS."$match[1].$type";

            @list($path, $name) = array(dirname($test_file), basename($test_file));

            assets::append(findfile($path, $name, FALSE, 1), $type);
        }, $test);

        $test = preg_replace('/\/\*[*\s]*?\*\//s', '', $test);

        write($tmp, assets::$type($test));

        $hash     = md5(md5_file($tmp) . filesize($tmp));
        $suffix   = APP_ENV === 'production' ? '.min' : '';
        $out_file = $out_path.DS.$from.$hash.$suffix.".$type";

        assets::assign("$from.$type", $hash);

        copy($tmp, $out_file);
        unlink($tmp);
      }

      return path_to($type.DS.basename($out_file));
    }
  }

  public static function images() {
    if (APP_ENV <> 'production') {
      $img_path   = APP_PATH.DS.'views'.DS.'assets'.DS.'img';
      $static_dir = APP_PATH.DS.'static'.DS.'img';

      ! is_dir($static_dir) && mkpath($static_dir);

      $set = array();

      unfile($static_dir, '*', DIR_RECURSIVE);

      if ($test = dir2arr($img_path, '*.(jpe?g|png|gif)$', DIR_RECURSIVE | DIR_MAP)) {
        foreach (array_filter($test, 'is_file') as $file) {
          $file_hash  = md5(md5_file($file) . filesize($file));
          $file_name  = str_replace($img_path.DS, '', extn($file)) . $file_hash . ext($file, TRUE);

          $static_img = $static_dir.DS.$file_name;

          ! is_dir(dirname($static_img)) && mkpath(dirname($static_img));
          ! is_file($static_img) && copy($file, $static_img);

          if ( ! array_key_exists($file_name, $set)) {
            $set[str_replace($file_hash, '', $file_name)] = $file_hash;
          }
        }
      }

      assets::assign($set);
      assets::save();
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
        $out = array();

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

/* EOF: ./library/assets/assets.php */
