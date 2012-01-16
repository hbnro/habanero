<?php

/**
 * PostgreSQL database adapter
 */

if ( ! function_exists('pg_connect')) {
  raise(ln('extension_missing', array('name' => 'PostgreSQL')));
}

class pgsql_driver extends pgsql_scheme
{
  protected $last_query = NULL;

  final protected function factory(array $params) {
    $obj = new static;

    $conn  = "dbname=" . trim($params['path'], '/');
    $conn .= " host={$params['host']} user=$params[user]";
    $conn .= ! empty($params['port'])? " port=$params[port]": '';
    $conn .= ! empty($params['pass'])? " password=$params[pass]": '';

    $obj->res = pg_connect($conn);
    $obj->set_encoding();

    return $obj;
  }

  final protected function version() {
    return pg_fetch_result(pg_exec($this->res, 'SELECT version()'), 0);
  }

  final protected function execute($sql) {
    return @pg_query($this->res, $this->last_query = $sql);
  }

  final protected function real_escape($test) {
    return pg_escape_string($this->res, $test);
  }

  final protected function has_error() {
    return pg_last_error($this->res);
  }

  final protected function fetch_result($res) {
    return pg_fetch_result($res, 0);
  }

  final protected function fetch_assoc($res) {
    return pg_fetch_assoc($res);
  }

  final protected function fetch_object($res) {
    return pg_fetch_object($res);
  }

  final protected function count_rows($res) {
    return pg_num_rows($res);
  }

  final protected function affected_rows($res) {
    return pg_affected_rows($res);
  }

  final protected function last_inserted_id($res, $table, $column) {
    $tmp = pg_fetch_row(pg_query($this->res, 'SELECT version()'), 0);

    $v = preg_replace('/^\w+\s+([0-9\.]+)\s.*$/i', '\\1', $tmp[0]);
    $v = (double) $v;


    if ($v >= 8.1) {//TODO: try to find out a better fix?
      $sql = ($table && $column) ? "SELECT MAX($column) FROM $table" : 'SELECT LASTVAL()';
    } elseif ( ! empty($table) &&  ! empty($column) && ($v >= 8.0)) {// http://www.php.net/pg_last_oid
      $sql = sprintf("SELECT CURRVAL(pg_get_serial_sequence('%s','%s'))", $table, $column);
    } else {
      return pg_last_oid($this->res);
    }

    $tmp = pg_fetch_row(pg_query($this->res, $sql), 0);

    return $tmp[0];
  }
}

/* EOF: ./library/db/drivers/pgsql.php */
