<?php

namespace Sauce;

class Event
{

  private static $set = array();

  public static function __callStatic($method, $arguments)
  {
    return static::fire($method, $arguments);
  }

  public static function fire($events, array $params = array(), $halt = FALSE)
  {
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

  public static function clear($event)
  {
    if (static::has_listeners($event)) {
      unset(static::$set[$event]);
    }
  }

  public static function listen($event, $callback, $before = FALSE)
  {
    ! static::has_listeners($event) && static::$set[$event] = array();
    $before ? array_unshift(static::$set[$event], $callback) : static::$set[$event] []= $callback;
  }

  public static function override($event, $callback)
  {
    static::clear($event);
    static::listen($event, $callback);
  }

  public static function has_listeners($event)
  {
    return isset(static::$set[$event]);
  }

}
