<?php

/**
 * CSS string functions
 */

/**
 * Force quoting
 *
 * @param  mixed  Text|...
 * @return string
 */
chess_helper::implement('qt', function () {
  $args = func_get_args();
  return sprintf("'%s'", join('', $args));
});


/**
 * List quoting
 *
 * @param  mixed  Text|...
 * @return string
 */
chess_helper::implement('list', function() {
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
chess_helper::implement('format', function($text) {
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
chess_helper::implement('argv', function($text, $index = 1, $default = FALSE) {
  $args = preg_split('/\s+/', $text);
  return isset($args[$index - 1]) ? $args[$index - 1] : $default;
});


/**
 * Numeric values
 *
 * @param  string Number
 * @return string
 */
chess_helper::implement('num', function ($text) {
  static $regex = '/(?:p[xtc]|e[xm]|[cm]m|g?rad|deg|in|s|%)/';


  $type = preg_match($regex, $text, $match) ? $match[0] : 'px';
  $out  = round((float) str_replace($type, '', $text), 9);

  return "$out$type";
});

/* EOF: ./library/chess/helpers/string.php */
