<?php

/**
 * SQLite3 database adapter
 */

if ( ! class_exists('SQLite3'))
{
  raise(ln('extension_missing', array('name' => 'SQLite3')));
}

/**#@+
 * @ignore
 */
define('RANDOM', 'RANDOM()');
define('DB_DRIVER', 'SQLite3');
/**#@-*/


sql::method('connect', function()
{
  static $object = NULL;
  
  
  if (is_null($object))
  {
    $parts   = func_get_arg(0);
    $db_file = $parts['host'] . $parts['path'];
    
    if ( ! is_file($db_file))
    {
      raise(ln('file_not_exists', array('name' => $db_file)));
    }

    $object = new SQLite3($db_file);

    $object->createFunction('concat', function()
    {
      return implode(func_get_args(), '');
    });
    
    $object->createFunction('md5rev', function($str)
    {
      return strrev(md5($str));
    }, 1);
    
    $object->createFunction('mod', function($a, $b)
    {
      return $a % $b;
    }, 2);
    
    $object->createFunction('md5', function($str)
    {
      return md5($str);
    }, 1);
    
    $object->createFunction('now', function()
    {
      return time();
    }, 0);
  }
  
  return $object;
});

sql::method('version', function()
{
  $test = sql::connect()->version();
  
  return $test['versionString'];
});

sql::method('execute', function($sql)
{
  return sql::connect()->query($sql);
});

sql::method('escape', function($test)
{
  return str_replace("'", "''", stripslashes($test));
});

sql::method('error', function()
{
  return sql::connect()->lastErrorMsg();
});

sql::method('result', function($res)
{
  return $res ? $res->fetchArray(SQLITE3_ASSOC) : FALSE;
});

sql::method('fetch_assoc', function($res)
{
  return $res ? $res->fetchArray(SQLITE3_ASSOC) : array();
});

sql::method('fetch_object', function($res)
{
  return (object) sql::fetch_assoc($res);
});

sql::method('count_rows', function($res)
{
  return $res ? sizeof($res) : 0;
});

sql::method('affected_rows', function()
{
  return sql::connect()->changes();
});

sql::method('last_id', function()
{
  return sql::connect()->lastInsertRowID();
});

/* EOF: ./lib/db/drivers/sqlite.php */
