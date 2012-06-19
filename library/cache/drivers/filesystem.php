<?php

/**
 * Filesystem based cache adapter
 */

/**#@+
 * @ignore
 */
define('CACHE_DRIVER', 'Filesystem');
/**#@-*/


cache::implement('free_all', function () {
  foreach (dir2arr(TMP, '--cache-file*') as $cache_file) {
    @unlink(TMP.DS.$cache_file);
  }
});

cache::implement('fetch_item', function ($key) {
  if (is_file($cache_file = TMP.DS.'--cache-file'.md5($key))) {
    $test   = @gzuncompress(read($path));
    $offset = strpos($test, '|');

    $old = (int) substr($test, 0, $offset);
    $new = substr($test, $offset + 1);

    if (($old - time()) <= 0) {
      @unlink($cache_file);
    }

    if (is_serialized($new)) {
      return unserialize($new);
    }
  }
  return FALSE;
});

cache::implement('store_item', function ($key, $val, $max) {
  $cache_file = TMP.DS.'--cache-file'.md5($key);
  $binary     = (time() + $max) . '|' . serialize($val);

  return write($cache_file, @gzcompress($binary));
});

cache::implement('delete_item', function ($key) {
  if (is_file($cache_file = TMP.DS.'--cache-file'.md5($key))) {
    return @unlink($cache_file);
  }
});

cache::implement('check_item', function ($key) {
  return is_file(TMP.DS.'--cache-file'.md5($key));
});

/* EOF: ./library/cache/drivers/filesystem.php */
