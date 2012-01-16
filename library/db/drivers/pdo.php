<?php

/**
 * PDO database adapter
 */

if ( ! class_exists('PDO')) {
  raise(ln('extension_missing', array('name' => 'PDO')));
}

class pdo_driver
{

  protected $last_query = NULL;

  final public static function factory(array $params) {
    switch ($params['scheme']) {
      case 'sqlite';
        $dsn_string = 'sqlite:' . str_replace('\\', '/', $params['host'] . $params['path']);
      break;
      default;
        $dsn_string = "$params[scheme]:host=$params[host];";

        if ($params['port'] > 0) {
          $dsn_string .= "port=$params[port];";
        }

        $params['database'] = trim($params['path'], '/');
        $dsn_string        .= "dbname=$params[database];";
      break;
    }

    parse_str($params['query'], $query);

    $scheme_class = $params['scheme'] . '_scheme';
    $fake_class   = "pdo_{$params['scheme']}_driver";
    $php_class    = "class $fake_class extends $scheme_class{public function __call(\$m,\$a){return call_user_func_array(array(\$this->bridge,\$m),\$a);}}";

    ! class_exists($fake_class) && eval($php_class);

    $obj = new $fake_class;
    $obj->bridge = new static;
    $obj->bridge->res  = new PDO($dsn_string, $params['user'], $params['pass'], $query);

    return $obj;
  }

  final public function version() {
    $test = $this->res->getAttribute(PDO::ATTR_SERVER_VERSION);
    return $test['versionString'];
  }

  final public function execute($sql) {
    $this->last_query = $sql;

    if (preg_match('/^\s*(UPDATE|DELETE)\s+/', $sql)) {
      return $this->res->exec($sql);
    }
    return $this->res->query($sql);
  }

  final public function real_escape($test) {
    return substr($this->res->quote($test), 1, -1);
  }

  final public function has_error() {
    $test = $this->res->errorInfo();
    return $test[0] == '00000' ? FALSE : $test[2];
  }

  final public function fetch_result($res) {
    return @array_shift($this->fetch_assoc($res));
  }

  final public function fetch_assoc($res) {
    return $res ? $res->fetch(PDO::FETCH_ASSOC) : FALSE;
  }

  final public function fetch_object($res) {
    return $res ? $res->fetch(PDO::FETCH_OBJ) : FALSE;
  }

  final public function count_rows($res) {
    if ( ! $res) {
      return FALSE;
    }

    $out = $res->rowCount();

    if (preg_match('/^\s*SELECT.+?FROM(.+?)$/is', $res->queryString, $match)) {
      // http://www.php.net/manual/es/pdostatement.rowcount.php
      $tmp = $this->execute("SELECT COUNT(*) FROM $match[1]");
      $out = $this->fetch_result($tmp);
    }
    return (int) $out;
  }

  final public function affected_rows($res) {
    return $res ? (int) $res : FALSE;
  }

  final public function last_inserted_id() {
    // TODO: support for postgres?
    return $this->res->lastInsertId();
  }
}
/* EOF: ./library/db/drivers/pdo.php */
