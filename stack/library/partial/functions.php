<?php

/**
 * Partial helper functions
 */

/**
 * Render shortcut
 *
 * @param  string Path
 * @param  array  Vars
 * @return string
 */
function partial($path, array $vars = array()) {
  $view_file = partial::option('path').DS.str_replace('/', DS, $path);
  return partial::apply(is_file($view_file) ? 'render' : 'load', array($view_file, $vars));
}

/* EOF: ./stack/library/partial/functions.php */
