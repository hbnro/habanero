<?php

/**
 * Magic config wrapper
 */

class config
{

  /**#@+
   * @ignore
   */

  // options
  private static $bag = array();

  // filters
  private static $proc = array();

  /**#@-*/



  /**
   * Retrieve all options
   *
   * @return array
   */
  final public static function all() {
    return static::$bag;
  }


  /**
   * Option setup
   *
   * @param  string Item
   * @param  mixed  Value
   * @return void
   */
  final public static function set($key, $value = TRUE) {
    if ( ! empty(static::$proc[$key])) {
      $value = call_user_func(static::$proc[$key], $value);
    }
    static::$bag[$key] = $value;
  }


  /**
   * Option retrieve
   *
   * @param  string Item
   * @param  mixed  Default value
   * @return mixed
   */
  final public static function get($key, $or = FALSE) {
    return value(static::$bag, $key, $or);
  }


  /**
   * Multiple option merge
   *
   * @param  mixed Array|Script
   * @return void
   */
  final public static function add($set) {
    if (is_assoc($set)) {
      foreach ($set as $key => $value) {
        static::set($key, $value);
      }
    } elseif (is_file($set)) {
      static::import($set);
    }
  }


  /**
   * Option delete
   *
   * @param  string Item
   * @return void
   */
  final public static function rem($key) {
    if (isset(static::$bag[$key])) {
      unset(static::$bag[$key]);
    }
  }


  /**
   * Import config file
   *
   * @param  string Path
   * @return void
   */
  final public static function import($set) {
    $config = array();
    $test   = include $set;

    if (is_array($config)) {
      if (is_array($test)) {
        $config = array_merge($config, $test);
      }
      static::add(array_merge(static::$bag, $config));
    }
  }


  /**
   * Option filtering
   *
   * @param  string Item
   * @param  mixed  Function callback
   * @return void
   */
  final public static function filter($key, Closure $lambda) {
    static::$proc[$key] = $lambda;
  }

}

/* EOF: ./library/config.php */
