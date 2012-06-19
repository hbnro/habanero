<?php

/**
 * Basic registry library
 */

class registry
{

  /**#@+
   * @ignore
   */

  // data collection
  private static $stack = array();

  /**#@-*/

  /**
   * Retrieve registry item
   *
   * @param  string Key
   * @param  mixed  Default value
   * @param  string Container
   * @return mixed
   */
  final public static function fetch($item, $or = NULL, $bag = '') {
    return value(static::get($bag), $item, $or);
  }


  /**
   * Assign registry item
   *
   * @param  string  Key
   * @param  mixed   Value
   * @param  string  Container
   * @return boolean
   */
  final public static function assign($item, $value, $bag = '') {
    static::get($bag)->$item = $value;
    return TRUE;
  }


  /**
   * Delete item from registry
   *
   * @param  string  Key
   * @param  string  Container
   * @return boolean
   */
  final public static function delete($item, $bag = '') {
    $bag = static::get($bag);

    if ( ! isset($bag->$item)) {
      return FALSE;
    }

    unset($bag->$item);

    return TRUE;
  }


  /**
   * Check if item exists on registry
   *
   * @param  string  Key
   * @param  string  Container
   * @return boolean
   */
  final public static function exists($item, $bag = '') {
    return isset(static::get($bag)->$item);
  }



  /**#@+
    * @ignore
    */

  // retrieve single bag
  final private static function get($bag = '') {
    $bag = ($bag && ! is_numeric($bag)) ? $bag : '--registry-default';

    if ( ! isset(static::$stack[$bag])) {
      static::$stack[$bag] = new stdClass;
    }
    return static::$stack[$bag];
  }

  /**#@-*/

}

/* EOF: ./library/registry.php */
