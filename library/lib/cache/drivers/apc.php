<?php

/**
 * APC cache adapter
 */

if ( ! function_exists('apc_fetch'))
{
  raise(ln('extension_missing', array('name' => 'APC')));
}

/**#@+
 * @ignore
 */
define('CACHE_DRIVER', 'APC');
/**#@-*/


cache::method('free_all', function()
{
  apc_clear_cache('user');
  apc_clear_cache();
});

cache::method('fetch_item', function($key)
{
  return apc_fetch($key);
});

cache::method('store_item', function($key, $val, $max)
{
  return apc_store($key, $val, $max);
});

cache::method('delete_item', function($key)
{
  return apc_delete($key);
});

cache::method('check_item', function($key)
{
  return apc_exists($key);
});

/* EOF: ./lib/cache/drivers/apc.php */
