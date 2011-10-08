<?php

class assets extends prototype
{// TODO: plus plus...

  public static $set = array(
    'head' => array(),
    'body' => array(),
    'css' => array(),
    'js' => array(),
  );

  private static $defs = array(
    'path' => APP_PATH,
    'root' => ROOT,
  );


  /**
   * Set configuration
   *
   * @param  mixed Key|Hash
   * @param  mixed Value
   * @return void
   */
  final public static function setup($key, $value = '')
  {
    if (is_assoc($key))
    {
      static::$defs = array_merge(static::$defs, $key);
    }
    elseif (array_key_exists($key, static::$defs))
    {
      static::$defs[$key] = $value;
    }
  }


  final public static function inline($code, $to = '', $before = FALSE)
  {
    static::push($to ?: 'head', $code, $before);
  }

  final public static function script($path, $to = '', $before = FALSE)
  {
    static::push($to ?: 'head', tag('script', array('src' => pre_url($path))), $before);
  }

  final public static function append($path, $to = '')
  {
    is_url($path) ? static::script($path, $to) : static::push($to ?: ext($path), $path);
  }

  final public static function prepend($path, $to = '')
  {
    is_url($path) ? static::script($path, $to, TRUE) : static::push($to ?: ext($path), $path, TRUE);
  }

  final public static function favicon($path = '')
  {
    return tag('link', array('rel' => pre_url($path ?: './favicon.ico')));
  }

  final public static function image($path)
  {
    return tag('img', array('alt' => $path));
  }

  final public static function missing($method, $arguments)
  {
    switch ($method)
    {// TODO: caching
      case 'css';
      case 'js';
        $out = array();

        foreach (static::$set[$method] as $one)
        {
          $file = realpath(static::$defs['path'].DS.$method.DS.$one);
          $path = str_replace(dirname(APP_PATH).DS, '', $file);
          $now  = date('Y-m-d H:i:s', filemtime($file));

          $out []= sprintf("/* ./%s [%s] */\n%s", $path, $now, read($file));
        }

        response(join("\n", $out), array(
          'type' => mime($method),
        ));
      break;
      default;
        die('missing method!');
      break;
    }
  }

  final public static function before()
  {
    return join("\n", static::$set['head']);
  }

  final public static function after()
  {
    return join("\n", static::$set['body']);
  }



  /**#@+
   * @ignore
   */

  // generic aggregator
  final private static function push($on, $test, $prepend = FALSE)
  {
    $prepend ? array_unshift(static::$set[$on], $test) : static::$set[$on] []= $test;
  }

  /**#@-*/

}
