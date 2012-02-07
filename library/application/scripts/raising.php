<?php

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

$error_file = "errors/$error_status.html";
$content    = partial($error_file, array(
  'message' => $message,
));

response($content, array(
  'status' => $error_status
));

/* EOF: ./library/application/scripts/raising.php */
