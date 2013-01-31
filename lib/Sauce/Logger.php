<?php

namespace Sauce;

class Logger
{

  private static $obj = NULL;
  private static $set = array(
                    \Psr\Log\LogLevel::EMERGENCY,
                    \Psr\Log\LogLevel::ALERT,
                    \Psr\Log\LogLevel::CRITICAL,
                    \Psr\Log\LogLevel::ERROR,
                    \Psr\Log\LogLevel::WARNING,
                    \Psr\Log\LogLevel::NOTICE,
                    \Psr\Log\LogLevel::INFO,
                    \Psr\Log\LogLevel::DEBUG,
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

      static::instance()->log($method, join('', $arguments), $params);
    }
  }

  private static function instance()
  {
    if (static::$obj === NULL) {
      $klass = \Sauce\Config::get('logger');

      if (! $klass instanceof \Psr\Log\LoggerInterface) {
        $klass = new \Sauce\LoggerAware;
      } elseif (is_string($klass)) {
        $klass = new $klass;
      }

      static::$obj = $klass;
    }

    return static::$obj;
  }

}
