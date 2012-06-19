<?php

/**
 * Translation core functions
 */

/**
 * Dynamic shortcode alias
 *
 * @param  mixed Input string|...
 * @return mixed
 */
function ln($input) {
  $args  = func_get_args();

  if (is_array($input)) {
    foreach ($input as $key => $value) {
      $args[0]     = $value;
      $input[$key] = call_user_func_array(__FUNCTION__, $args);
    }
  } else {
    $callback = is_numeric($input) ? 'pluralize' : 'translate';
    $input    = call_user_func_array("i18n::$callback", $args);
  }

  return $input;
}

/* EOF: ./library/i18n/functions.php */