<?php

/**
 * Prototyping class
 */

class prototype
{

  /**#@+
   * @ignore
   */

  // public function stack
  private static $public = array();

  // avoid constructor
  private function __construct()
  {
  }

  // public method callback
  final public static function __callStatic($method, $arguments = array())
  {
    if ( ! isset(self::$public[get_called_class()][$method]))
    {
      if (self::defined('missing'))
      {
        $class = get_called_class();

        return $class::missing($method, $arguments);
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
  final public static function implement($method, Closure $lambda)
  {
    self::$public[get_called_class()][$method] = $lambda;
  }


  /**
   * Is specified method defined?
   *
   * @param  string  Method name
   * @return boolean
   */
  final public static function defined($method)
  {
    if (isset(self::$public[get_called_class()][$method]))
    {
      return TRUE;
    }
    return method_exists(get_called_class(), $method);
  }


  /**
   * Prototype methods
   *
   * @return array
   */
  final public static function methods()
  {
    return ! empty(self::$public[get_called_class()]) ? self::$public[get_called_class()] : array();
  }


  /**
   * Currying apply
   *
   * @param  string Method name
   * @param  array  Arguments
   * @return mixed
   */
  final public static function apply($method, array $args = array())
  {
    if (isset(self::$public[get_called_class()][$method]))
    {
      return apply(self::$public[get_called_class()][$method], $args);
    }
    return apply(get_called_class() . "::$method", $args);
  }

}

/* EOF: ./core/prototype.php */
