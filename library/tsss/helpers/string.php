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
css_helper::implement('%q', function() {
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
css_helper::implement('%', function($text) {
  $args = array_slice(func_get_args(), 1);
  return vsprintf($text, $args);
});

/* EOF: ./library/tsss/helpers/string.php */
