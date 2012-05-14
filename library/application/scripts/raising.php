<?php

error_log($message);
debug("Error:\n  $message");

$error_status = 500;

switch (APP_ENV) {
  case 'production';
    if (preg_match('/^(?:GET|PUT|POST|DELETE)\s+\/.+?$/', $message)) {
      $error_status = 404;
    }
  break;
  default;
    $bootstrap['raise']($message);
  break;
}

$methods[500] = 'unknown';
$methods[404] = 'not_found';

$output = application::execute('error', $methods[$error_status]);

response($output);

exit;

/* EOF: ./library/application/scripts/raising.php */
