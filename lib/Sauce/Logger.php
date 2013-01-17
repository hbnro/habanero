<?php

namespace Sauce;

class Logger
{

  private static $obj = NULL;


  public static function __callStatic($method, array $arguments)
  {
    $params = array();

    foreach ($arguments as $key => $set) {
      if ( ! is_scalar($set)) {
        unset($arguments[$key]);
        $params = array_merge($params, (array) $set);
      }
    }

    $message = join('', $arguments);

    $params['timestamp'] = date('Y-m-d H:i:s');
    $params['ticks'] = microtime(TRUE) - BEGIN;
    $params['level'] = $method;

    static::instance()->log($method, $message, $params);
  }



  private static function instance()
  {
    if (static::$obj === NULL) {
      $klass = \Sauce\Config::get('logger');

      if ( ! $klass instanceof \Psr\Log\LoggerInterface) {
        $klass = new \Sauce\LoggerAware;
      } elseif (is_string($klass)) {
        $klass = new $klass;
      }

      static::$obj = $klass;
    }
    return static::$obj;
  }

}
