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
  protected static $public = array();

  // avoid constructor
  protected function __construct() {
  }

  // public method callback
  final public static function __callStatic($method, $arguments = array()) {
    $klass = get_called_class();
    if ( ! isset($klass::$public[$method])) {
      if ($klass::defined('missing')) {
        return $klass::missing($method, $arguments);
      }
      raise(ln('method_missing', array('class' => $klass, 'name' => $method)));
    }
    return $klass::apply($method, $arguments);
  }

  /**#@-*/


  /**
   * Internal method definition
   *
   * @param  string Method name
   * @param  mixed  Closure function
   */
  final public static function implement($method, Closure $lambda) {
    $klass = get_called_class();
    $klass::$public[$method] = $lambda;
  }


  /**
   * Is specified method defined?
   *
   * @param  string  Method name
   * @return boolean
   */
  final public static function defined($method) {
    $klass = get_called_class();
    if (isset($klass::$public[$method])) {
      return TRUE;
    }
    return method_exists($klass, $method);
  }


  /**
   * Prototype methods
   *
   * @return array
   */
  final public static function methods() {
    $klass = get_called_class();
    return $klass::$public;
  }


  /**
   * Currying apply
   *
   * @param  string Method name
   * @param  array  Arguments
   * @return mixed
   */
  final public static function apply($method, array $args = array()) {
    $klass = get_called_class();
    if (isset($klass::$public[$method])) {
      return call_user_func_array($klass::$public[$method], $args);
    }
    return call_user_func_array("$klass::$method", $args);
  }


  /**
   * Set configuration
   *
   * @param  mixed Key|Hash|Closure
   * @param  mixed Value
   * @return void
   */
  final public static function config($key, $value = '') {
    $klass = get_called_class();
    if (is_assoc($key)) {
      $klass::$defs = array_merge($klass::$defs, $key);
    } elseif (is_closure($key)) {
      $config = new stdClass;
      $key($config);

      $klass::$defs = array_merge($klass::$defs, (array) $config);
    } elseif (array_key_exists($key, $klass::$defs)) {
      $klass::$defs[$key] = $value;
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
    $klass = get_called_class();
    return ! empty($klass::$defs[$key]) ? $klass::$defs[$key] : $default;
  }

}

/* EOF: ./framework/klass/prototype.php */
