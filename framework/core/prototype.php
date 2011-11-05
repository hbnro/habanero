<?php

/**
 * Prototyping class
 */

class prototype
{

  /**#@+
   * @ignore
   */

  // defaults
  protected static $defs = array();

  // public function stack
  private static $public = array();

  // avoid constructor
  private function __construct() {
  }

  // public method callback
  final public static function __callStatic($method, $arguments = array()) {
    if ( ! isset(self::$public[get_called_class()][$method])) {
      if (static::defined('missing')) {
        return static::missing($method, $arguments);
      }
      raise(ln('method_missing', array('class' => get_called_class(), 'name' => $method)));
    }
    return self::apply($method, $arguments);
  }

  /**#@-*/


  /**
   * Internal method definition
   *
   * @param  string Method name
   * @param  mixed  Closure function
   */
  final public static function implement($method, Closure $lambda) {
    self::$public[get_called_class()][$method] = $lambda;
  }


  /**
   * Is specified method defined?
   *
   * @param  string  Method name
   * @return boolean
   */
  final public static function defined($method) {
    if (isset(self::$public[get_called_class()][$method])) {
      return TRUE;
    }
    return method_exists(get_called_class(), $method);
  }


  /**
   * Prototype methods
   *
   * @return array
   */
  final public static function methods() {
    return ! empty(self::$public[get_called_class()]) ? self::$public[get_called_class()] : array();
  }


  /**
   * Currying apply
   *
   * @param  string Method name
   * @param  array  Arguments
   * @return mixed
   */
  final public static function apply($method, array $args = array()) {
    if (isset(self::$public[get_called_class()][$method])) {
      return call_user_func_array(self::$public[get_called_class()][$method], $args);
    }
    return call_user_func_array(get_called_class() . "::$method", $args);
  }


  /**
   * Set configuration
   *
   * @param  mixed Key|Hash|Closure
   * @param  mixed Value
   * @return void
   */
  final public static function config($key, $value = '') {
    if (is_assoc($key)) {
      static::$defs = array_merge(static::$defs, $key);
    } elseif (is_closure($key)) {
      $config = new stdClass;
      $key($config);

      foreach ((array) $config as $key => $val) {
        static::config($key, $val);
      }
    } elseif (array_key_exists($key, static::$defs)) {
      static::$defs[$key] = $value;
    }
  }


  /**
   * Retrieve configuration
   *
   * @param  string Key
   * @param  mixed  Default value
   * @return mixed
   */
  final public static function option($key, $default = FALSE) {
    return ! empty(static::$defs[$key]) ? static::$defs[$key] : $default;
  }

}

/* EOF: ./framework/core/prototype.php */
