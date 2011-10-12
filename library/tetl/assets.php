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

  // defaults
  private static $defs = array(
    'path' => APP_PATH,
  );

  // compile filters
  private static $filter = array();

  /**#@-*/



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


  /**
   * @return void
   */
  final public static function inline($code, $to = '', $before = FALSE)
  {
    static::push($to ?: 'head', $code, $before);
  }


  /**
   * @return void
   */
  final public static function script($path, $to = '', $before = FALSE)
  {
    static::push($to ?: 'head', tag('script', array('src' => pre_url($path))), $before);
  }


  /**
   * @return void
   */
  final public static function append($path, $to = '')
  {
    is_url($path) ? static::script($path, $to) : static::push($to ?: ext($path), $path);
  }


  /**
   * @return void
   */
  final public static function prepend($path, $to = '')
  {
    is_url($path) ? static::script($path, $to, TRUE) : static::push($to ?: ext($path), $path, TRUE);
  }


  /**
   * @return string
   */
  final public static function favicon($path = '')
  {
    return tag('link', array('rel' => pre_url($path ?: './favicon.ico')));
  }


  /**
   * @return string
   */
  final public static function image($path)
  {
    return tag('img', array('alt' => $path));
  }


  /**
   * @return string
   */
  final public static function before()
  {
    return join("\n", static::$set['head']);
  }


  /**
   * @return string
   */
  final public static function after()
  {
    return join("\n", static::$set['body']);
  }


  /**
   * @return void
   */
  final public static function compile($type, Closure $lambda)
  {
    static::$filter[$type] = $lambda;
  }


  /**
   * @return void
   */
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

          if (is_file($file))
          {
            $path = str_replace(dirname(APP_PATH).DS, '', $file);
            $now  = date('Y-m-d H:i:s', filemtime($file));
            $text = static::process($file);

            $out []= sprintf("/* %s ./%s */\n%s", $now, $path, $text);
          }
        }

        response(join("\n", array_merge($out, $arguments)), array(
          'type' => mime($method),
        ));
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
  final private static function process($file)
  {
    $type = ext($file);

    if ( ! empty(static::$filter[$type]))
    {
      $compiled = TMP.DS.'_'.basename($file);

      if ( ! is_file($compiled) OR (filemtime($file) > filemtime($compiled)))
      {
        write($compiled, call_user_func(static::$filter[$type], $file));
      };
      return read($compiled);
    }
    return read($file);
  }

  // generic aggregator
  final private static function push($on, $test, $prepend = FALSE)
  {
    $prepend ? array_unshift(static::$set[$on], $test) : static::$set[$on] []= $test;
  }

  /**#@-*/

}

/* EOF: ./library/tetl/assets.php */
