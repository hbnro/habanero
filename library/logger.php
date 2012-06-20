<?php

/**
 * Basic logging wrapper
 */

class logger extends prototype
{
  final public static function missing($method, $arguments) {
    static::defined('send') && static::send($method, $arguments);
  }
}


// default logging
logger::implement('send', function ($type, $params) {
  ! IS_CLI && error_log(preg_replace('/[\r\n]+\s*/', ' ', join('', $params)));
});

/* EOF: ./library/logger.php */
