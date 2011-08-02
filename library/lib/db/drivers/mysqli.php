<?php

/**
 * MySQLi database adapter
 */

if ( ! function_exists('mysqli_connect'))
{
  raise(ln('extension_missing', array('name' => 'MySQLi')));
}

/**#@+
 * @ignore
 */
define('RANDOM', 'RAND()');
define('DB_DRIVER', 'MySQLi');
/**#@-*/


sql::method('connect', function()
{
  static $resource = NULL;
  
  
  if (is_null($resource))
  {
    $parts = func_get_arg(0);
    
    $host  = $parts['host'];
    $host .= ! empty($parts['port']) ? ":$parts[port]" : '';

    $resource = mysqli_connect($host, $parts['user'], ! empty($parts['pass']) ? $parts['pass'] : '');
    mysqli_select_db($resource, trim($parts['path'], '/'));
  }
  return $resource;
});

sql::method('version', function()
{
  $res  = mysqli_query(sql::connect(), 'SELECT version()');
  $test = sql::fetch_assoc($res);
  
  return array_shift($test);
});

sql::method('execute', function($sql)
{
  return mysqli_query(sql::connect(), $sql);
});

sql::method('escape', function($test)
{
  return str_replace("'", '\\\'', stripslashes($test));
});

sql::method('error', function()
{
  return mysqli_error(sql::connect());
});

sql::method('result', function($res)
{
  return @array_shift(sql::fetch_assoc($res));
});

sql::method('fetch_assoc', function($res)
{
  return mysqli_fetch_assoc($res);
});

sql::method('fetch_object', function($res)
{
  return mysqli_fetch_object($res);
});

sql::method('count_rows', function($res)
{
  return mysqli_num_rows($res);
});

sql::method('affected_rows', function()
{
  return mysqli_affected_rows(sql::connect());
});

sql::method('last_id', function()
{
  return mysqli_insert_id(sql::connect());
});

/* EOF: ./lib/db/drivers/mysqli.php */
