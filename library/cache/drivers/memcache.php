<?php

/**
 * Memcached cache adapter
 */

if ( ! function_exists('memcache_connect')) {
  raise(ln('extension_missing', array('name' => 'Memcached')));
}

/**#@+
 * @ignore
 */
define('CACHE_DRIVER', 'Memcached');
/**#@-*/


cache::implement('connect', function () {// TODO: overwrite if needed?
  static $resource = NULL;


  if (is_null($resource)) {
    $resource = memcache_connect('localhost', '11211');
  }

  return $resource;
});

cache::implement('free_all', function () {
  memcache_flush(cache::connect());

  $end = time() + 1;

  while(time() < $end);
});

cache::implement('fetch_item', function ($key) {
  return memcache_get(cache::connect(), $key);
});

cache::implement('store_item', function ($key, $val, $max) {
  return memcache_set(cache::connect(), $key, $val, 0, $max);
});

cache::implement('delete_item', function ($key) {
  return memcache_delete(cache::connect(), $key);
});

cache::implement('check_item', function ($key) {// http://www.php.net/manual/en/memcache.getextendedstats.php#98161
  $list  = array();
  $slabs = memcache_get_extended_stats(cache::connect(), 'slabs');
  $items = memcache_get_extended_stats(cache::connect(), 'items');

  foreach ($slabs as $server) {
    foreach (array_keys($server) as $id) {
      $test = memcache_get_extended_stats('cachedump', (int) $id);

      foreach ($test as $keys) {
        foreach (array_keys($keys) as $one) {
          if ($one === $key) {
            return TRUE;
          }
        }
      }
    }
  }
  return FALSE;
});

/* EOF: ./library/cache/drivers/memcache.php */
