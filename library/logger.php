<?php

/**
 * Basic logging wrapper
 */

class logger extends prototype
{
  final public static function missing($method, $arguments) { // TODO: levels?
    static::defined('write') && static::write($method, join('', $arguments));
  }
}


// default logging
logger::implement('write', function ($type, $message) {
  if (IS_CLI) {
    $date    = date('Y-m-d H:i:s');
    $message = preg_replace('/[\r\n]+\s*/', ' ', $message);

    write(APP_PATH.DS.'logs'.DS.'environment.log', "[$date] [$type] $message\n", 1);
  } else {
    error_log(str_replace("\n", '\\n', $message));
  }
});

/* EOF: ./library/logger.php */
