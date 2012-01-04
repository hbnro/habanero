<?php

import('partial');


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

$error_file = APP_PATH.DS.'views'.DS.'errors'.DS."$error_status.html";

response(partial::load($error_file), array(
  'status' => $error_status,
  'message' => $message,
));

/* EOF: ./library/application/scripts/raising.php */
