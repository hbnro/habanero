<?php

/**
 * CSS image functions
 */

/**
 * Image dimensions
 *
 * @param  string Path
 * @param  mixed  Index
 * @return string
 */
chess_helper::implement('image-size', function ($path, $key = -1) {
  static $cache = array();


  if (empty($cache[$path])) {
    if ( ! is_file($img_file = chess::path($path))) {
      return;
    }
    $cache[$path] = getimagesize($img_file);
  }

  $test = $cache[$path];
  $test = ! empty($test[$key]) ? $test[$key] : 0;

  return "{$test}px";
});

/* EOF: ./library/chess/helpers/image.php */
