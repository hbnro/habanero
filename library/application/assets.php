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


  public static function initialize() {
    $cache_file = APP_PATH.DS.'config'.DS.'resources'.EXT;

    ! is_file($cache_file) && write($cache_file, '<' . "?php return array();\n");

    static::$cache = (array) include $cache_file;

    $static_dir = APP_PATH.DS.'static';

    // TODO: improve handling?
    if (APP_ENV <> 'production') {
      // cleanup
      foreach (array('css', 'img', 'js') as $one) {
        foreach (dir2arr($static_dir.DS.$one, '*', DIR_RECURSIVE | DIR_MAP) as $file) {
          if (preg_match('/\w+([a-f0-9]{32})\.\w+/', basename($file), $match)) {
            ! in_array($match[1], static::$cache) && unlink($file);
          }
        }
      }

      $img_path = APP_PATH.DS.'views'.DS.'assets'.DS.'img';
      $img_dir  = $static_dir.DS.'img';

      ! is_dir($img_dir) && mkpath($img_dir);

      $set = array();

      if ($test = dir2arr($img_path, '*.(jpe?g|png|gif)$', DIR_RECURSIVE | DIR_MAP)) {
        foreach (array_filter($test, 'is_file') as $file) {
          $file_hash  = md5(md5_file($file) . filesize($file));
          $file_name  = str_replace($img_path.DS, '', extn($file)) . $file_hash . ext($file, TRUE);

          $static_img = $img_dir.DS.$file_name;

          ! is_dir(dirname($static_img)) && mkpath(dirname($static_img));
          ! is_file($static_img) && copy($file, $static_img);

          if ( ! array_key_exists($file_name, $set)) {
            static::assign(str_replace($file_hash, '', $file_name), $file_hash);
          }
        }
      }
      static::save();
    }
  }

  public static function path($for) {
    return ($hash = static::item($for)) ? extn($for) . $hash . ext($for, TRUE) : $for;
  }

  public static function save() {
    $out = var_export(array_filter(static::$cache, 'is_md5'), TRUE);

    write(APP_PATH.DS.'config'.DS.'resources'.EXT, '<' . "?php return $out;\n");
  }

  public static function assign($key, $val = NULL) {
    static::$cache[$key] = $val;
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
        $out_file = $out_path.DS.$from.static::fetch("$from.$type").".min.$type";
      } else {
        $set = array();
        $tmp = TMP.DS."$from.$type.tmp";

        // css and js
        $test = preg_replace_callback('/\s+\*=\s+(\S+)/m', function ($match)
          use($base_path, $type, &$set) {
            $test_file = $base_path.DS.$type.DS."$match[1].$type";

            @list($path, $name) = array(dirname($test_file), basename($test_file));

            $set []= findfile($path, $name, FALSE, 1);
        }, $test);



        $out = array();

        foreach ($set as $file) {
          if (is_file($file)) {
            $text  = static::process($file);
            $path  = str_replace(APP_PATH.DS, '', $file);
            $now   = date('Y-m-d H:i:s', filemtime($file));

            $out []= sprintf("/* %s ./%s */\n%s", $now, strtr($path, '\\', '/'), $text);
          }
        }

        $out []= preg_replace('/\/\*[*\s]*?\*\//s', '', $test);

        write($tmp, join("\n", $out));

        $hash     = md5(md5_file($tmp) . filesize($tmp));
        $out_file = $out_path.DS."$from$hash.$type";

        static::assign("$from.$type", $hash);
        static::save();

        copy($tmp, $out_file);
        unlink($tmp);
      }

      return path_to($type.DS.basename($out_file));
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
