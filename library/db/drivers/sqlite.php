<?php

/**
 * SQLite3 database adapter
 */

if ( ! class_exists('SQLite3')) {
  raise(ln('extension_missing', array('name' => 'SQLite3')));
}

class sqlite_driver extends sqlite_scheme
{

  protected $last_query = NULL;

  protected $random = 'RANDOM()';

  final public static function factory(array $params) {
    $db_file = $params['host'] . $params['path'];

    if ( ! is_file($db_file) && ($db_file <> ':memory:')) {
      raise(ln('file_not_exists', array('name' => $db_file)));
    }

    $obj = new static;
    $obj->res = new SQLite3($db_file);
    $obj->res->createfunction('concat', function () {
      return implode(func_get_args(), '');
    });

    $obj->res->createfunction('md5rev', function ($str) {
      return strrev(md5($str));
    }, 1);

    $obj->res->createfunction('mod', function ($a, $b) {
      return $a % $b;
    }, 2);

    $obj->res->createfunction('md5', function ($str) {
      return md5($str);
    }, 1);

    $obj->res->createfunction('now', function () {
      return time();
    }, 0);

    return $obj;
  }

  final public function version() {
    $test = $this->res->version();
    return $test['versionString'];
  }

  final public function execute($sql) {//FIX
    $this->last_query = $sql;
    return @$this->res->query($sql);
  }

  final public function real_escape($test) {
    return str_replace("'", "''", stripslashes($test));
  }

  final public function has_error() {
    return $this->res->lastErrorCode() ? $this->res->lastErrorMsg() : FALSE;
  }

  final public function fetch_result($res) {
    return ($tmp = $this->fetch_assoc($res)) ? array_shift($tmp) : FALSE;
  }

  final public function fetch_assoc($res) {
    return $res ? $res->fetchArray(SQLITE3_ASSOC) : FALSE;
  }

  final public function fetch_object($res) {
    if ($res && $out = $this->fetch_assoc($res)) {//FIX
      return (object) $out;
    }
  }

  final public function count_rows($res) {//FIX
    $sql = sprintf('SELECT COUNT(*) FROM (%s)', $this->last_query);
    return $this->last_query ? $this->fetch_result($this->execute($sql)) : FALSE;
  }

  final public function affected_rows() {
    return $this->res->changes();
  }

  final public function last_inserted_id() {
    return $this->res->lastInsertRowID();
  }
}

/* EOF: ./library/db/drivers/sqlite.php */
