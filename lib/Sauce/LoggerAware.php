<?php

namespace Sauce;

class LoggerAware extends \Psr\Log\AbstractLogger
{

  public function log($level, $message, array $context = array())
  {
    $test = strtoupper(PHP_SAPI);

    $log_dir = path(APP_PATH, 'logs');
    $log_name = ((strpos($test, 'CLI') === FALSE) OR ($test === 'CLI-SERVER')) ? APP_ENV : 'environment';

    $timestamp = date('Y-m-d H:i:s');
    $context['ticks'] = microtime(TRUE) - BEGIN;

    $message = "[$timestamp] [$level] $message\n";
    $message = static::interpolate($message, $context);

    if (is_dir($log_dir)) {
      @error_log($message, 3, path($log_dir, "$log_name.log"));
    } else { // TODO: should be stderr?
      @error_log($message);
    }
  }

  private static function interpolate($message, array $context = array())
  {
    if (strpos($message, '{') === FALSE) {
      return $message;
    }

    $repl = array();

    foreach ($context as $key => $val) {
      $repl["{{$key}}"] = is_scalar($val) ? $val : json_encode($val);
    }

    return strtr($message, $repl);
  }

}
