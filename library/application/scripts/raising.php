<?php

import('partial');

$error_status = 500;

switch (option('environment')) {
  case 'development';
    $bootstrap['raise']($message);
  break;
  case 'production';
  default;
    if (preg_match('/^(?:GET|PUT|POST|DELETE)\s+\/.+?$/', $message)) {
      $error_status = 404;
    }
  break;
}


$error_file = CWD.DS.'app'.DS.'views'.DS.'errors'.DS."$error_status.html";

response(partial::load($error_file), array(
  'status' => $error_status,
  'message' => $message,
));

/* EOF: ./library/application/scripts/raising.php */
