<?php

// timezone
date_default_timezone_set('UTC');


// benchmark
define('BEGIN', microtime(TRUE));
define('USAGE', memory_get_usage());


// environment
$trace = debug_backtrace(FALSE);
$trace = array_pop($trace);

define('APP_LOADER', realpath($trace['file']));
define('APP_PATH', realpath(getcwd()));
define('APP_ENV', getenv('ENV') ?: 'development');


// the root-script name
define('INDEX', basename(APP_LOADER));


// PCRE+Unicode
error_reporting(0);
ini_set('log_errors', 0);
ini_set('display_errors', 0);
define('IS_UNICODE', @preg_match('/\pL/u', 'ñ') > 0);

error_reporting(E_ALL |~E_STRICT);
ini_set('display_errors', 1);
ini_set('log_errors', 1);


// OS temp path
if (function_exists('sys_get_temp_dir')) {
  $temporary_files = @sys_get_temp_dir();
} else {
  $temporary_files = getenv('TMP') ?: getenv('TEMP');

  if ( ! is_dir($temporary_files)) {
    $old = @tempnam('E', '');
    $temporary_files = @dirname($old);
    @unlink($old);
  }
}

define('TMP', @is_dir($temporary_files) && @is_writable($temporary_files) ? rtrim($temporary_files, '\\/') : '/tmp');

is_dir(TMP) OR mkdir(TMP, 0777, TRUE);


// default error and exception handlers
set_error_handler(function ($errno, $errmsg, $file, $line, $trace) {
    if (($errno & error_reporting()) == $errno) {
      \Sauce\Base::raise("Error: $errmsg ($file#$line)", $trace);

      return TRUE;
    }
    return FALSE;
  });

set_exception_handler(function ($E) {
    \Sauce\Base::raise($E);
  });
