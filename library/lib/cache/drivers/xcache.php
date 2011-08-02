<?php

/**
 * XCache -cache- adapter
 */

if ( ! function_exists('xcache_get'))
{
  raise(ln('extension_missing', array('name' => 'XCache')));
}

/**#@+
 * @ignore
 */
define('CACHE_DRIVER', 'XCache');
/**#@-*/


cache::method('free_all', function()
{
  xcache_clear_cache(XC_TYPE_VAR, 0);
});

cache::method('fetch_item', function($key)
{
  return xcache_get($key);
});

cache::method('store_item', function($key, $val, $max)
{
  return xcache_set($key, $val, $max);
});

cache::method('delete_item', function($key)
{
  return xcache_unset($key);
});

cache::method('check_item', function($key)
{
  return xcache_isset($key);
});

/* EOF: ./lib/cache/drivers/xcache.php */
