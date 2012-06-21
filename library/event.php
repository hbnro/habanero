<?php

/**
 * Simple event handler
 */

class event extends prototype
{

  /**#@+
   * @ignore
   */

  // collection
  private static $set = array();

  /**#@-*/



  /**
   * Fire custom events
   *
   * @param  mixed   Event|Array
   * @param  array   Params
   * @param  boolean Halt?
   * @return void
   */
  final public static function fire($events, array $params = array(), $halt = FALSE) {
    $out = array();

    foreach ((array) $events as $event) {
      if (static::has_listeners($event)) {
        foreach (static::$set[$event] as $callback) {
          $test = call_user_func_array($callback, $params);

          if ( ! is_null($test) && $halt) {
            return $test;
          }
          $out []= $test;
        }
      }
    }

    return $out;
  }


  /**
   * Clear custom events
   *
   * @param  string Event
   * @return void
   */
  final public static function clear($event) {
    if (static::has_listeners($event)) {
      unset(static::$set[$event]);
    }
  }


  /**
   * Listen for custom events
   *
   * @param  string  Event
   * @param  mixed   Function callback
   * @param  boolean Before?
   * @return void
   */
  final public static function listen($event, $callback, $before = FALSE) {
    ! static::has_listeners($event) && static::$set[$event] = array();
    $before ? array_unshift(static::$set[$event], $callback) : static::$set[$event] []= $callback;
  }


  /**
   * Override custom event
   *
   * @param  string Event
   * @param  mixed  Function callback
   * @return void
   */
  final public static function override($event, $callback) {
    static::clear($event);
    static::listen($event, $callback);
  }


  /**
   * Has listeners?
   *
   * @param  string Event
   * @return void
   */
  final public static function has_listeners($event) {
    return isset(static::$set[$event]);
  }


  /**
   * A shotgun, bang bang!
   *
   * @param  string Method
   * @param  array  Arguments
   * @return void
   */
  final public static function missing($method, $arguments) {
    return static::fire($method, $arguments);
  }

}

/* EOF: ./library/event.php */
