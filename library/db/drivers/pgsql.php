<?php

/**
 * PostgreSQL database adapter
 */

if ( ! function_exists('pg_connect')) {
  raise(ln('extension_missing', array('name' => 'PostgreSQL')));
}

/**#@+
 * @ignore
 */
define('RANDOM', 'RANDOM()');
define('DB_DRIVER', 'PostgreSQL');
/**#@-*/


sql::implement('connect', function () {
  static $resource = NULL;


  if (is_null($resource)) {
    $parts = func_get_arg(0);

    $conn  = "dbname=" . trim($parts['path'], '/');
    $conn .= " host={$parts['host']} user=$parts[user]";
    $conn .= ! empty($parts['port'])? " port=$parts[port]": '';
    $conn .= ! empty($parts['pass'])? " password=$parts[pass]": '';

    $resource = pg_connect($conn);
  }

  return $resource;
});

sql::implement('version', function () {
  return pg_fetch_result(pg_exec(sql::connect(), 'SELECT version()'), 0);
});

sql::implement('execute', function ($sql) {
  return pg_query(sql::connect(), $sql);
});

sql::implement('escape', function ($test) {
  return pg_escape_string(sql::connect(), $test);
});

sql::implement('error', function () {
  return pg_last_error(sql::connect());
});

sql::implement('result', function ($res) {
  return pg_fetch_result($res, 0);
});

sql::implement('fetch_assoc', function ($res) {
  return pg_fetch_assoc($res);
});

sql::implement('fetch_object', function ($res) {
  return pg_fetch_object($res);
});

sql::implement('count_rows', function ($res) {
  return pg_num_rows($res);
});

sql::implement('affected_rows', function ($res) {
  return pg_affected_rows($res);
});

sql::implement('last_id', function ($res, $table, $column) {
  $tmp = pg_fetch_row(pg_query(sql::connect(), 'SELECT version()'), 0);

  $v = preg_replace('/^\w+\s+([0-9\.]+)\s.*$/i', '\\1', $tmp[0]);
  $v = (double) $v;


  if ($v >= 8.1) {//TODO: try to find out a better fix?
    $sql = ($table && $column) ? "SELECT MAX($column) FROM $table" : 'SELECT LASTVAL()';
  } elseif ( ! empty($table) &&  ! empty($column) && ($v >= 8.0)) {// http://www.php.net/pg_last_oid
    $sql = sprintf("SELECT CURRVAL(pg_get_serial_sequence('%s','%s'))", $table, $column);
  } else {
    return pg_last_oid(sql::connect());
  }

  $tmp = pg_fetch_row(pg_query(sql::connect(), $sql), 0);

  return $tmp[0];
});

/* EOF: ./library/db/drivers/pgsql.php */
