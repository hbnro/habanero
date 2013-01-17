<?php

namespace Sauce;

class Logger
{

  private static $obj = NULL;
  private static $set = array(
                    \Psr\Log\Loglevel::EMERGENCY,
                    \Psr\Log\Loglevel::ALERT,
                    \Psr\Log\Loglevel::CRITICAL,
                    \Psr\Log\Loglevel::ERROR,
                    \Psr\Log\Loglevel::WARNING,
                    \Psr\Log\Loglevel::NOTICE,
                    \Psr\Log\Loglevel::INFO,
                    \Psr\Log\Loglevel::DEBUG,
                  );


  public static function __callStatic($method, array $arguments)
  {
    if (($level = array_search($method, static::$set)) === FALSE) {
      throw new \Exception("Unknown logger '$method' level");
    }


    $test = \Sauce\Config::get('level') ?: 'debug';
    $test = array_search($test, static::$set);

    if ($level <= $test) {
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
