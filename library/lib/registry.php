<?php

/**
 * Basic registry library
 */

/**
 * Registry root
 *
 * @param  string Container
 * @return mixed
 */
function registry($bag = '')
{
  static $set = array();

  
  $bag = ($bag && ! is_num($bag)) ? $bag : '--registry-default';

  if ( ! isset($set[$bag]))
  {
    $set[$bag] = new stdClass;
  }

  return $set[$bag];
}


/**
 * Retrieve registry item
 *
 * @param  string Key
 * @param  mixed  Default value
 * @param  string Container
 * @return mixed
 */
function registry_get($item, $or = NULL, $bag = '')
{
  $bag = registry($bag);

  if (is_num($item))
  {
    return FALSE;
  }
  
  return value($bag, $item, $or);
}


/**
 * Assign registry item
 *
 * @param  string Key
 * @param  mixed  Value
 * @param  string Container
 * @return mixed
 */
function registry_set($item, $value, $bag = '')
{
  $bag = registry($bag);

  if (is_num($item))
  {
    return FALSE;
  }

  $bag->$item = $value;

  return TRUE;
}


/**
 * Delete item from registry
 *
 * @param  string Key
 * @param  string Container
 * @return mixed
 */
function registry_unset($item, $bag = '')
{
  $bag = registry($bag);

  if (is_num($item) OR ! isset($bag->$item))
  {
    return FALSE;
  }

  unset($bag->$item);

  return TRUE;
}

/* EOF: ./lib/registry.php */
