<?php

/**
 * eAccelerator cache adapter
 */

if ( ! function_exists('eaccelerator_put')) {
  raise(ln('extension_missing', array('name' => 'eAccelerator')));
}

/**#@+
 * @ignore
 */
define('CACHE_DRIVER', 'eAccelerator');
/**#@-*/


cache::implement('free_all', function () {
  eaccelerator_gc();

  foreach (eaccelerator_list_keys() as $one) {
    cache::delete_item(substr($one['name'], 1));
  }
});

cache::implement('fetch_item', function ($key) {
  return eaccelerator_get($key);
});

cache::implement('store_item', function ($key, $val, $max) {
  return eaccelerator_put($key, $val, $max);
});

cache::implement('delete_item', function ($key) {
  eaccelerator_rm($key);
});

cache::implement('check_item', function ($key) {
  return in_array($key, eaccelerator_list_keys());
});

/* EOF: ./stack/library/cache/drivers/eaccelerator.php */
