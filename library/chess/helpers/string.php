<?php

/**
 * CSS string functions
 */

/**
 * List quoting
 *
 * @param  mixed  Text|...
 * @return string
 */
chess_helper::implement('%q', function() {
  $args = array();
  foreach (func_get_args() as $one) {
    $args []= strrpos($one, ' ') ? "'$one'" : $one;
  }
  return join(', ', $args);
});


/**
 * Output format
 *
 * @param  string Text
 * @param  mixed  Arguments|...
 * @return string
 */
chess_helper::implement('%', function($text) {
  $args = array_slice(func_get_args(), 1);
  return vsprintf($text, $args);
});


/**
 * Index pick
 *
 * @param  string Text
 * @param  mixed  Arguments|...
 * @return string
 */
chess_helper::implement('#', function($text, $index = 1, $default = FALSE) {
  $args = preg_split('/\s+/', $text);
  return isset($args[$index - 1]) ? $args[$index - 1] : $default;
});

/* EOF: ./library/chess/helpers/string.php */
