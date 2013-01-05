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

function field_for($type, $key)
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
            'list' => array('type' => 'list'),
            'json' => array('type' => 'json'),
          );


  if ( ! empty($set[$type])) {
    $out = $set[$type];
    $out['title'] = titlecase($key);
    return $out;
  }

  return FALSE;
}
