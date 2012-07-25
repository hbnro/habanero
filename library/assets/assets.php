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
  private static $cache = NULL;

  /**#@-*/



  /**
   * @return void
   */
  final public static function save() {
    $out = var_export(array_filter(static::$cache, 'is_md5'), TRUE);
    write(APP_PATH.DS.'config'.DS.'resources'.EXT, '<' . "?php return $out;\n");
  }


  /**
   * @param
   * @param
   * @return void
   */
  final public static function assign($key, $val = NULL) {
    static::$cache[$key] = $val;
  }


  /**
   * @param
   * @return string
   */
  final public static function resolve($name) {
    $name = str_replace(APP_PATH.DS.'assets'.DS, '', $name);

    if ($hash = static::fetch($name)) {
      $name = str_replace(basename($name), extn($name, TRUE).$hash.ext($name, TRUE), $name);
    }
    return $name;
  }


  /**
   * @param
   * @return string
   */
  final public static function fetch($name) {
    if (is_null(static::$cache)) {
      static::$cache = is_file($cache_file = APP_PATH.DS.'config'.DS.'resources'.EXT) ? include $cache_file : array();
    }

    if ((APP_ENV === 'production') && ! empty(static::$cache[$name])) {
      return static::$cache[$name];
    }
  }


  /**
   * @param
   * @param
   * @return mixed
   */
  final public static function build($from, $type) {
    $base_path = APP_PATH.DS.'assets';
    $base_file = $base_path.DS."$from.$type";

    if (is_file($base_file)) {
      if (APP_ENV === 'production') {
        $path = static::url_for(static::resolve($base_file));

        if ($type == 'css') {
          return tag('link', array('rel' => 'stylesheet', 'href' => $path));
        } else {
          return tag('script', array('src' => $path));
        }
      } else {
        $set = array();
        $tmp = static::extract($base_file);

        foreach ($tmp['include'] as $one) {
          $path = static::url_for(str_replace($base_path.DS.$type.DS, '', $one));

          if ($type == 'css') {
            $set []= tag('link', array('rel' => 'stylesheet', 'href' => $path));
          } else {
            $set []= tag('script', array('src' => $path));
          }
        }

        return join("\n", $set);
      }
    }
  }


  /**
   * @param
   * @return array
   */
  final public static function extract($file) {
    $type = ext($file);
    $test = array(
      'require' => array(),
      'include' => array(),
    );

    // TODO: allow imports, sub-manifests, trees?
    if (preg_match_all('/\s+\*=\s+(require|include)\s+(\S+)/m', read($file), $match)) {
      foreach ($match[1] as $i => $key) {
        $test_file = APP_PATH.DS.'assets'.DS.$type.DS.$match[2][$i];
        @list($path, $name) = array(dirname($test_file), basename($test_file));
        $test[$key] []= $path.DS."$name.$type";
      }
    }

    return $test;
  }


  /**
   * @param
   * @param
   * @param
   * @return string
   */
  final public static function url_for($path, $prefix = '') {
    if (is_url($path)) {
      return $path;
    }


    $prefix = $prefix ? $prefix : ext($path);
    $path   = "$prefix/$path";

    if (option('assets.s3')) {// TODO: ok?
      $name   = option('assets.s3.bucket');
      $region = option('assets.s3.location');

      return "//$name." . ($region ? "$region." : '') . "s3.amazonaws.com/$path";
    }

    return url_for("static/$path", array(
      'complete' => option('assets.host') ?: FALSE,
      'host' => TRUE,
    ));
  }


  /**
   * @param
   * @param
   * @return mixed
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



  /**#@+
   * @ignore
   */

  // generic aggregator
  final private static function push($on, $test, $prepend = FALSE) {
    $prepend ? array_unshift(static::$set[$on], $test) : static::$set[$on] []= $test;
  }

  /**#@-*/

}

/* EOF: ./library/assets/assets.php */
