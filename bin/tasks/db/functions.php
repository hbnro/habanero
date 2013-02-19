<?php

function __set($val = NULL, array $vars = array())
{
  static $set = array();

  if (func_num_args() === 0) {
    return $set;
  } elseif (is_array($vars)) {
    $set = array_merge($set, $vars);
  }

  return $val;
}

function db($for = 'default')
{
  $dsn = option("database.$for");
  $db = \Grocery\Base::connect($dsn);

  return $db;
}

function mongo($for = 'mongodb')
{
  $dsn = option("database.$for");
  $collection = substr($dsn, strrpos($dsn, '/') + 1);
  $mongo = $dsn ? new \Mongo($dsn) : new \Mongo;
  $db = $mongo->{$collection ?: 'default'};

  return $db;
}

function field_for($type, $key = NULL)
{
  static $set = array(
            'primary_key' => array('type' => 'hidden'),
            'text' => array('type' => 'textarea'),
            'string' => array('type' => 'text'),
            'integer' => array('type' => 'number'),
            'numeric' => array('type' => 'number'),
            'float' => array('type' => 'number'),
            'boolean' => array('type' => 'checkbox'),
            'binary' => array('type' => 'file'),
            'timestamp' => array('type' => 'datetime'),
            'datetime' => array('type' => 'datetime'),
            'date' => array('type' => 'date'),
            'time' => array('type' => 'time'),
            'object' => array('type' => 'object'), // native-dummy on mongo
            'array' => array('type' => 'array'), // TODO: support for postgres
            'hash' => array('type' => 'hash'),
            'enum' => array('type' => 'enum'),
            'json' => array('type' => 'json'),
            'set' => array('type' => 'set'),
          );

  if ( ! empty($set[$type])) {
    if (! $key) {
      return TRUE;
    }

    $out = $set[$type];
    $out['title'] = titlecase($key);

    return $out;
  }

  return FALSE;
}

function hydrate_model($file)
{
  if (is_file($file) && strpos($file, '.php')) {
    preg_match_all('/class\s(\S+)\s/', read($file), $match);

    require $file;

    foreach ($match[1] as $klass) {
      $re = new \ReflectionClass($klass);

      switch ($re->getParentClass()->getName()) {
        case 'Servant\\Mapper\\Database';
          status('hydrate', $file);

          $dsn = option('database.' . $klass::CONNECTION);
          $db = \Grocery\Base::connect($dsn);

          $columns = $klass::columns();
          $indexes = $klass::indexes();

          if ( ! isset($db[$klass::table()])) {
            $db[$klass::table()] = $columns;
          }

          \Grocery\Helpers::hydrate($db[$klass::table()], $columns, $indexes);
        break;
        case 'Servant\\Mapper\\MongoDB';
          status('hydrate', $file);

          $dsn_string = \Servant\Config::get($klass::CONNECTION);
          $database = substr($dsn_string, strrpos($dsn_string, '/') + 1);
          $mongo = $dsn_string ? new \Mongo($dsn_string) : new \Mongo;
          $db = $mongo->{$database ?: 'default'};

          \Servant\Helpers::reindex($db->{$klass::table()}, $klass::indexes());
        break;
        default;
        break;
      }
    }
  }
}
