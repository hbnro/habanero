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

  protected $random = 'RAND()';

  final public static function factory(array $params) {
    $host  = $params['host'];
    $host .= ! empty($params['port']) ? ":$params[port]" : '';

    $obj = new static;

    $obj->res = mysqli_connect($host, $params['user'], ! empty($params['pass']) ? $params['pass'] : '');
    mysqli_select_db($obj->res, trim($params['path'], '/'));

    return $obj;
  }

  final public function version() {
    return $this->fetch_result(mysqli_query($this->res, 'SELECT version()'));
  }

  final public function execute($sql) {
    return mysqli_query($this->res, $sql);
  }

  final public function real_escape($test) {
    return str_replace("'", '\\\'', stripslashes($test));
  }

  final public function has_error() {
    return mysqli_error($this->res);
  }

  final public function fetch_result($res) {
    return @array_shift($this->fetch_assoc($res));
  }

  final public function fetch_assoc($res) {
    return mysqli_fetch_assoc($res);
  }

  final public function fetch_object($res) {
    return mysqli_fetch_object($res);
  }

  final public function count_rows($res) {
    return mysqli_num_rows($res);
  }

  final public function affected_rows() {
    return mysqli_affected_rows($this->res);
  }

  final public function last_inserted_id() {
    return mysqli_insert_id($this->res);
  }
}

/* EOF: ./library/db/drivers/mysqli.php */
