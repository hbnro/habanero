<?php

/**
 * Magic config wrapper
 */

class configure
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
    return self::$bag;
  }


  /**
   * Option setup
   *
   * @param  mixed Item|Function callback
   * @param  mixed Value
   * @return void
   */
  final public static function set($key, $value = TRUE) {
    if (is_closure($key)) {
      $test = new stdClass;
      $key($test);

      return self::add((array) $test);
    } elseif ( ! empty(self::$proc[$key])) {
      $value = call_user_func(self::$proc[$key], $value);
    }
    self::$bag[$key] = $value;
  }


  /**
   * Option retrieve
   *
   * @param  string Item
   * @param  mixed  Default value
   * @return mixed
   */
  final public static function get($key, $or = FALSE) {
    return value(self::$bag, $key, $or);
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
        self::set($key, $value);
      }
    } elseif (is_file($set)) {
      self::import($set);
    }
  }


  /**
   * Option delete
   *
   * @param  string Item
   * @return void
   */
  final public static function rem($key) {
    if (isset(self::$bag[$key])) {
      unset(self::$bag[$key]);
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
      self::add(array_merge(self::$bag, $config));
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
    self::$proc[$key] = $lambda;
  }

}

/* EOF: ./library/configure.php */
