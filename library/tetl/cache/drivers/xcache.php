<?php

/**
 * XCache -cache- adapter
 */

if ( ! function_exists('xcache_get')) {
  raise(ln('extension_missing', array('name' => 'XCache')));
}

/**#@+
 * @ignore
 */
define('CACHE_DRIVER', 'XCache');
/**#@-*/


cache::implement('free_all', function () {
  xcache_clear_cache(XC_TYPE_VAR, 0);
});

cache::implement('fetch_item', function ($key) {
  return xcache_get($key);
});

cache::implement('store_item', function ($key, $val, $max) {
  return xcache_set($key, $val, $max);
});

cache::implement('delete_item', function ($key) {
  return xcache_unset($key);
});

cache::implement('check_item', function ($key) {
  return xcache_isset($key);
});

/* EOF: ./library/tetl/cache/drivers/xcache.php */
