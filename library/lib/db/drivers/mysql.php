<?php

/**
 * MySQL database adapter
 */

if ( ! function_exists('mysql_connect'))
{
  raise(ln('extension_missing', array('name' => 'MySQL')));
}

/**#@+
 * @ignore
 */
define('RANDOM', 'RAND()');
define('DB_DRIVER', 'MySQL');
/**#@-*/


sql::method('connect', function()
{
  static $resource = NULL;
  
  
  if (is_null($resource))
  {
    $parts = func_get_arg(0);

    $host  = $parts['host'];
    $host .= ! empty($parts['port']) ? ":$parts[port]" : '';

    $resource = mysql_connect($host, $parts['user'], ! empty($parts['pass']) ? $parts['pass'] : '');
    mysql_select_db(trim($parts['path'], '/'), $resource);
  }
  
  return $resource;
});

sql::method('version', function()
{
  return mysql_result(mysql_query('SELECT version()', sql::connect()), 0);
});

sql::method('execute', function($sql)
{
  return mysql_query($sql, sql::connect());
});

sql::method('escape', function($test)
{
  return str_replace("'", '\\\'', stripslashes($test));
});

sql::method('error', function()
{
  return mysql_error(sql::connect());
});

sql::method('result', function($res)
{
  return mysql_result($res, 0);
});

sql::method('fetch_assoc', function($res)
{
  return mysql_fetch_assoc($res);
});

sql::method('fetch_object', function($res)
{
  return mysql_fetch_object($res);
});

sql::method('count_rows', function($res)
{
  return mysql_num_rows($res);
});

sql::method('affected_rows', function()
{
  return mysql_affected_rows(sql::connect());
});

sql::method('last_id', function()
{
  return mysql_insert_id(sql::connect());
});

/* EOF: ./lib/db/drivers/mysql.php */
