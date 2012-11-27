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
            'object' => array('type' => 'hash'),
            'array' => array('type' => 'enum'),
          );


  if ( ! empty($set[$type])) {
    $out = $set[$type];
    $out['title'] = camelcase($key, TRUE, ' ');
    return $out;
  }

  return FALSE;
}
