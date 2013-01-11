<?php

namespace Sauce;

class Logger
{

  public static function __callStatic($method, array $arguments)
  {
    $ticks = microtime(TRUE) - BEGIN;
    $message = join('', $arguments);
    $timestamp = date('Y-m-d H:i:s');

    static::log("[$timestamp] [$method] $message ($ticks)");
  }


  public static function log($message, $name = APP_ENV)
  {
    $log_dir = path(APP_PATH, 'logs');

    if (is_dir($log_dir)) {
      error_log("$message\n", 3, path($log_dir, "$name.log"));
    }
  }

}
