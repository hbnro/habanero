<?php

/**
 * MySQLi database adapter
 */

if ( ! function_exists('mysqli_connect')) {
  raise(ln('extension_missing', array('name' => 'MySQLi')));
}

/**#@+
 * @ignore
 */
define('RANDOM', 'RAND()');
define('DB_DRIVER', 'MySQLi');
/**#@-*/


sql::implement('connect', function () {
  static $resource = NULL;


  if (is_null($resource)) {
    $parts = func_get_arg(0);

    $host  = $parts['host'];
    $host .= ! empty($parts['port']) ? ":$parts[port]" : '';

    $resource = mysqli_connect($host, $parts['user'], ! empty($parts['pass']) ? $parts['pass'] : '');
    mysqli_select_db($resource, trim($parts['path'], '/'));
  }
  return $resource;
});

sql::implement('version', function () {
  $res  = mysqli_query(sql::connect(), 'SELECT version()');
  $test = sql::fetch_assoc($res);

  return array_shift($test);
});

sql::implement('execute', function ($sql) {
  return mysqli_query(sql::connect(), $sql);
});

sql::implement('escape', function ($test) {
  return str_replace("'", '\\\'', stripslashes($test));
});

sql::implement('error', function () {
  return mysqli_error(sql::connect());
});

sql::implement('result', function ($res) {
  return @array_shift(sql::fetch_assoc($res));
});

sql::implement('fetch_assoc', function ($res) {
  return mysqli_fetch_assoc($res);
});

sql::implement('fetch_object', function ($res) {
  return mysqli_fetch_object($res);
});

sql::implement('count_rows', function ($res) {
  return mysqli_num_rows($res);
});

sql::implement('affected_rows', function () {
  return mysqli_affected_rows(sql::connect());
});

sql::implement('last_id', function () {
  return mysqli_insert_id(sql::connect());
});

/* EOF: ./library/db/drivers/mysqli.php */
