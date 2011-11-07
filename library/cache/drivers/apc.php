<?php

/**
 * APC cache adapter
 */

if ( ! function_exists('apc_fetch')) {
  raise(ln('extension_missing', array('name' => 'APC')));
}

/**#@+
 * @ignore
 */
define('CACHE_DRIVER', 'APC');
/**#@-*/


cache::implement('free_all', function () {
  apc_clear_cache('user');
  apc_clear_cache();
});

cache::implement('fetch_item', function ($key) {
  return apc_fetch($key);
});

cache::implement('store_item', function ($key, $val, $max) {
  return apc_store($key, $val, $max);
});

cache::implement('delete_item', function ($key) {
  return apc_delete($key);
});

cache::implement('check_item', function ($key) {
  return apc_exists($key);
});

/* EOF: ./library/cache/drivers/apc.php */
