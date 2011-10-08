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


sql::implement('connect', function()
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

sql::implement('version', function()
{
  $test = sql::connect()->version();
  
  return $test['versionString'];
});

sql::implement('execute', function($sql)
{//FIX
  sql::connect()->lastQuery = $sql;
  
  return sql::connect()->query($sql);
});

sql::implement('escape', function($test)
{
  return str_replace("'", "''", stripslashes($test));
});

sql::implement('error', function()
{
  return sql::connect()->lastErrorCode() ? sql::connect()->lastErrorMsg() : FALSE;
});

sql::implement('result', function($res)
{
  return ($tmp = sql::fetch_assoc($res)) ? array_shift($tmp) : FALSE;
});

sql::implement('fetch_assoc', function($res)
{
  return $res ? $res->fetchArray(SQLITE3_ASSOC) : FALSE;
});

sql::implement('fetch_object', function($res)
{
  if ($res && $out = sql::fetch_assoc($res))
  {//FIX
    return (object) $out;
  }
});

sql::implement('count_rows', function($res)
{//FIX
  return sql::result(sql::execute(sprintf('SELECT COUNT(*) FROM (%s)', sql::connect()->lastQuery)));
});

sql::implement('affected_rows', function()
{
  return sql::connect()->changes();
});

sql::implement('last_id', function()
{
  return sql::connect()->lastInsertRowID();
});

/* EOF: ./lib/tetl/db/drivers/sqlite.php */
