<?php

namespace Sauce;

class Registry
{

  private static $stack = array();

  public static function fetch($item, $or = NULL, $bag = '')
  {
    return static::exists($item, $bag) ? static::get($bag)->$item : $or;
  }

  public static function assign($item, $value, $bag = '')
  {
    static::get($bag)->$item = $value;

    return TRUE;
  }

  public static function delete($item, $bag = '')
  {
    $bag = static::get($bag);

    if ( ! isset($bag->$item)) {
      return FALSE;
    }

    unset($bag->$item);

    return TRUE;
  }

  public static function exists($item, $bag = '')
  {
    return isset(static::get($bag)->$item);
  }

  private static function get($bag = '')
  {
    $bag = ($bag && ! is_numeric($bag)) ? $bag : 'default';

    if ( ! isset(static::$stack[$bag])) {
      static::$stack[$bag] = new \stdClass;
    }

    return static::$stack[$bag];
  }

}
