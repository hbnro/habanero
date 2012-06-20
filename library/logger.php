<?php

/**
 * Basic logging wrapper
 */

class logger extends prototype
{
  final public static function missing($method, $arguments) { // TODO: levels?
    static::defined('send') && static::send($method, join('', $arguments));
  }
}


// default logging
logger::implement('send', function ($type, $message) {
  ! IS_CLI && error_log(str_replace("\n", '\\n', $message));
});

/* EOF: ./library/logger.php */
