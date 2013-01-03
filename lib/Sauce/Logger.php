<?php

namespace Sauce;

class Logger
{

  public static function __callStatic($method, array $arguments)
  {
    $message = join('', $arguments);
    $timestamp = date('Y-m-d H:i:s');

    static::log("[$timestamp] [$method] $message");
  }


  public static function log($message, $name = APP_ENV)
  {
    error_log("$message\n", 3, path(APP_PATH, 'logs', "$name.log"));
  }

}
