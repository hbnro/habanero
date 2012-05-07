<?php

/**
 * MySQL database adapter
 */

if ( ! function_exists('mysql_connect')) {
  raise(ln('extension_missing', array('name' => 'MySQL')));
}

class mysql_driver extends mysql_scheme
{
  protected $last_query = array();

  final public static function factory(array $params) {
    $host  = $params['host'];
    $host .= ! empty($params['port']) ? ":$params[port]" : '';

    $obj = new static;

    $obj->res = mysql_connect($host, $params['user'], ! empty($params['pass']) ? $params['pass'] : '');
    mysql_select_db(trim($params['path'], '/'), $obj->res);
    $obj->set_encoding();

    return $obj;
  }

  final protected function version() {
    return mysql_result(mysql_query('SELECT version()', $this->res), 0);
  }

  final protected function execute($sql) {
    $this->debug($sql, TRUE);
    $out = @mysql_query($sql, $this->res);
    $this->debug(FALSE);
    return $out;
  }

  final protected function real_escape($test) {
    return mysql_real_escape_string($test, $this->res);
  }

  final protected function has_error() {
    return mysql_error($this->res);
  }

  final protected function fetch_result($res) {
    return mysql_result($res, 0);
  }

  final protected function fetch_assoc($res) {
    return mysql_fetch_assoc($res);
  }

  final protected function fetch_object($res) {
    return mysql_fetch_object($res);
  }

  final protected function count_rows($res) {
    return mysql_num_rows($res);
  }

  final protected function affected_rows() {
    return mysql_affected_rows($this->res);
  }

  final protected function last_inserted_id() {
    return mysql_insert_id($this->res);
  }
}

/* EOF: ./library/db/drivers/mysql.php */
