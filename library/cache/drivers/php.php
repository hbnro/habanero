<?php

/**
 * PHP based cache adapter
 */

/**#@+
 * @ignore
 */
define('CACHE_DRIVER', 'PHP');
/**#@-*/


cache::implement('free_all', function () {
  foreach (dir2arr(TMP, '--cache-php*') as $cache_file) {
    @unlink(TMP.DS.$cache_file);
  }
});

cache::implement('fetch_item', function ($key) {
  if (is_file($cache_file = TMP.DS.'--cache-php'.md5($key))) {
    $test = include $cache_file;

    if ( ! is_array($test)) {
      return @unlink($path);
    } elseif (time() < $test[0]) {
      return $test[1];
    }
    @unlink($cache_file);
  }
  return FALSE;
});

cache::implement('store_item', function ($key, $set = array(), $ttl = 0) {
  $cache_file = TMP.DS.'--cache-php'.md5($key);

  $vars = var_export($set, TRUE);
  $code = '<' . '?php return array(' . (time() + $ttl) . ", $vars);";

  return write($cache_file, $code);
});

cache::implement('delete_item', function ($key) {
  if (is_file($cache_file = TMP.DS.'--cache-php'.md5($key))) {
    return @unlink($cache_file);
  }
});

cache::implement('check_item', function ($key) {
  return cache::fetch_item($key) !== FALSE;
});

/* EOF: ./library/cache/drivers/php.php */
