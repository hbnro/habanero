<?php

/**
 * MySQL database adapter
 */

if ( ! function_exists('mysql_connect')) {
  raise(ln('extension_missing', array('name' => 'MySQL')));
}

/**#@+
 * @ignore
 */
define('RANDOM', 'RAND()');
define('DB_DRIVER', 'MySQL');
/**#@-*/


sql::implement('connect', function () {
  static $resource = NULL;


  if (is_null($resource)) {
    $parts = func_get_arg(0);

    $host  = $parts['host'];
    $host .= ! empty($parts['port']) ? ":$parts[port]" : '';

    $resource = mysql_connect($host, $parts['user'], ! empty($parts['pass']) ? $parts['pass'] : '');
    mysql_select_db(trim($parts['path'], '/'), $resource);
  }

  return $resource;
});

sql::implement('version', function () {
  return mysql_result(mysql_query('SELECT version()', sql::connect()), 0);
});

sql::implement('execute', function ($sql) {
  return mysql_query($sql, sql::connect());
});

sql::implement('escape', function ($test) {
  return str_replace("'", '\\\'', stripslashes($test));
});

sql::implement('error', function () {
  return mysql_error(sql::connect());
});

sql::implement('result', function ($res) {
  return mysql_result($res, 0);
});

sql::implement('fetch_assoc', function ($res) {
  return mysql_fetch_assoc($res);
});

sql::implement('fetch_object', function ($res) {
  return mysql_fetch_object($res);
});

sql::implement('count_rows', function ($res) {
  return mysql_num_rows($res);
});

sql::implement('affected_rows', function () {
  return mysql_affected_rows(sql::connect());
});

sql::implement('last_id', function () {
  return mysql_insert_id(sql::connect());
});

/* EOF: ./library/db/drivers/mysql.php */
