<?php

/**
 * SQLite cache adapter
 */

if ( ! class_exists('SQLite3')) {
  raise(ln('extension_missing', array('name' => 'SQLite3')));
}

/**#@+
 * @ignore
 */
define('CACHE_DRIVER', 'SQLite3');
/**#@-*/


cache::implement('link', function () {
  static $object = NULL;
  
  
  if (is_null($object)) {
    if ( ! is_file($db_file = TMP.DS.'--cache-db')) {
      touch($db_file);
      
      $tmp = new SQLite3($db_file); 
      
      $tmp->exec('CREATE TABLE "data"('
                . '"key" CHAR(32) PRIMARY KEY,'
                . '"value" TEXT,'
                . '"expire" INTEGER'
                . ')');
      
      $tmp->close();
      unset($tmp);
    }
    
    $object = new SQLite3($db_file);
  }
  
  
  $time = time();
  
  $sql  = 'DELETE FROM "data"';
  $sql .= "\nWHERE \"expire\" < $time";
  
  $object->exec($sql);
  
  return $object;
});

cache::implement('free_all', function () {
  cache::link()->exec('DELETE FROM "data"');
});

cache::implement('fetch_item', function ($key) {
  $sql  = 'SELECT value FROM "data"';
  $sql .= "\nWHERE \"key\" = PHP('md5', '$key')";
  
  if ($tmp = cache::link()->query($sql)) {
    $test = @array_shift($tmp->fetchArray(SQLITE3_NUM));
    
    if (is_serialized($test)) {
      return unserialize($test);
    }
    cache::delete_item($key);
  }
});

cache::implement('store_item', function ($key, $val, $max) {
  $time = time() + $max;
  $val  = str_replace("'", "''", serialize($val));
  
  $sql  = 'REPLACE INTO "data"';
  $sql .= '("key", "value", "expire")';
  $sql .= "\nVALUES(PHP('md5', '$key'), '$val', $time)";
  
  return cache::link()->exec($sql);
});

cache::implement('delete_item', function ($key) {
  $sql  = 'DELETE FROM "data"';
  $sql .= "\nWHERE \"key\" = PHP('md5', '$key')";
  
  return cache::link()->exec($sql);
});

cache::implement('check_item', function ($key) {
  $sql  = "SELECT COUNT(*) FROM \"data\"";
  $sql .= "\nWHERE \"key\" = PHP('md5', '$key')";
  
  $tmp  = cache::link()->query($sql);
  
  return @array_shift($tmp->fetchArray(SQLITE3_NUM)) > 0;
});

/* EOF: ./library/tetl/cache/drivers/sqlite.php */
