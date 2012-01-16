<?php

/**
 * MySQLi database adapter
 */

if ( ! function_exists('mysqli_connect')) {
  raise(ln('extension_missing', array('name' => 'MySQLi')));
}

class mysqli_driver extends mysql_scheme
{
  protected $last_query = NULL;

  final public static function factory(array $params) {
    $host  = $params['host'];
    $host .= ! empty($params['port']) ? ":$params[port]" : '';

    $obj = new static;

    $obj->res = mysqli_connect($host, $params['user'], ! empty($params['pass']) ? $params['pass'] : '');
    mysqli_select_db($obj->res, trim($params['path'], '/'));
    $obj->set_encoding();

    return $obj;
  }

  final protected function version() {
    return $this->fetch_result(mysqli_query($this->res, 'SELECT version()'));
  }

  final protected function execute($sql) {
    return mysqli_query($this->res, $this->last_query = $sql);
  }

  final protected function real_escape($test) {
    return str_replace("'", '\\\'', stripslashes($test));
  }

  final protected function has_error() {
    return mysqli_error($this->res);
  }

  final protected function fetch_result($res) {
    return @array_shift($this->fetch_assoc($res));
  }

  final protected function fetch_assoc($res) {
    return mysqli_fetch_assoc($res);
  }

  final protected function fetch_object($res) {
    return mysqli_fetch_object($res);
  }

  final protected function count_rows($res) {
    return mysqli_num_rows($res);
  }

  final protected function affected_rows() {
    return mysqli_affected_rows($this->res);
  }

  final protected function last_inserted_id() {
    return mysqli_insert_id($this->res);
  }
}

/* EOF: ./library/db/drivers/mysqli.php */
