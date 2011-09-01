<?php

/**
 * Cache functions library
 */

class cache extends prototype
{

  /**#@+
   * @ignore
   */

  // dynamic depth indicator
  private static $last = array();

  /**#@-*/


  /**
   * Starts buffering
   *
   * @param  string  Identifier
   * @return boolean
   */
  final public static function begin($key = NULL)
  {
    $key = ! func_num_args() ? '--n' . ob_get_level() : $key;

    if (self::exists($key))
    {
      echo self::get($key);
      return FALSE;
    }

    self::$last []= $key;

    ob_start();

    return TRUE;
  }


  /**
   * Stops buffering
   *
   * @link   http://www.php.net/manual/en/ref.outcontrol.php
   * @param  integer Duration in secs
   * @param  mixed   Tags|Array
   * @return boolean
   */
  final public static function end($max = 0, $tags = array())
  {
    if ( ! ob_get_level())
    {
      return FALSE;
    }

    $out = ob_get_clean();

    echo $out;

    if ( ! ($key = array_pop(self::$last)))
    {
      return FALSE;
    }

    if ($max > 0)
    {
      return self::set($key, $out, $max, $tags);
    }
    return TRUE;
  }


  /**
   * Store a functional block
   *
   * @param  string  Identifier
   * @param  mixed   Function callback
   * @param  integer Duration in secs
   * @return mixed
   */
  final public static function block($key, Closure $lambda, $max = 0)
  {
    if (is_false($old = self::get($key)))
    {
      ob_start() && $lambda();

      $old = ob_get_clean();

      self::set($key, $old, $max);
    }

    echo $old;
  }


  /**
   * Retrieve a item from cache
   *
   * @param  string Identifier
   * @param  mixed  Default value
   * @return mixed
   */
  final public static function get($key, $default = FALSE)
  {
    if (is_num($key) OR is_false($old = self::fetch_item($key)))
    {
      return $default;
    }
    return $old;
  }


  /**
   * Assign a element to cache
   *
   * @param  string  Identifier
   * @param  mixed   Default value
   * @param  mixed   Duration in secs
   * @param  mixed   Tags|Array
   * @return boolean
   */
  final public static function set($key, $value, $max = 0, $tags = array())
  {
    if (is_num($key))
    {
      return FALSE;
    }

    if (is_string($tags))
    {
      $tags = explode(',', $tags);
    }

    if ( ! empty($tags))
    {
      $old = self::fetch_item('--cache-tags');
      $old = ! is_array($old) ? array() : $old;

      $old[$key] = $tags;

      self::store_item('--cache-tags', $old, NEVER);
    }

    if ($max > 0)
    {
      return self::store_item($key, $value, $max);
    }

    self::remove($key);

    return FALSE;
  }


  /**
   * Delete a element from cache
   *
   * @param  mixed   Identifier|Tags|Array
   * @return boolean
   */
  final public static function remove($key)
  {
    if (is_string($key) && ! is_false(strpos($key, ',')))
    {
      $key = explode(',', $key);
    }


    if (is_array($key))
    {
      $old = self::fetch_item('--cache-tags');

      foreach ((array) $old as $i => $val)
      {
        $diff = array_intersect($key, $val);

        if (empty($diff))
        {
          continue;
        }

        self::delete_item($i);

        unset($old[$i]);
      }
      return self::store_item('--cache-tags', $old, NEVER);
    }
    return self::delete_item($key);
  }


  /**
   * Clear all cache entries
   *
   * @return void
   */
  final public static function clear()
  {
    self::free_all();
  }


  /**
   * Specific cache exists?
   *
   * @param  string  Identifier
   * @return boolean
   */
  final public static function exists($key)
  {
    return ! is_num($key) && self::check_item($key);
  }

}

/* EOF: ./lib/tetl/cache/system.php */
