<?php

/**
 * CSS initialization
 */

/**#@+
 * @ignore
 */

class css_helper extends prototype
{
}

require __DIR__.DS.'system'.EXT;
require __DIR__.DS.'colors'.EXT;
require __DIR__.DS.'images'.EXT;
require __DIR__.DS.'numbers'.EXT;



// list quoting
css_helper::implement('%q', function() {
  $args = array();
  foreach (func_get_args() as $one) {
    $args []= strrpos($one, ' ') ? "'$one'" : $one;
  }
  return join(', ', $args);
});

// format
css_helper::implement('%', function($text) {
  $args = array_slice(func_get_args(), 1);
  return vsprintf($text, $args);
});

/**#@-*/

/* EOF: ./library/tetl/css/initialize.php */
