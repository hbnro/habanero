<?php

/**
 * CSS image functions
 */

/**
 * Images
 *
 * @param  string Path
 * @param  mixed  Index
 * @return string
 */
css_helper::implement('image', function ($path, $key = -1) {
  static $cache = array();


  if (empty($cache[$path])) {
    $img_file = css::path($path);
    if (is_file($img_file)) {
      $cache[$path] = getimagesize($img_file);
    }
  }

  $test = $cache[$path];
  $test = ! empty($test[$key]) ? $test[$key] : 0;

  return "{$test}px";
});

/* EOF: ./library/css/helpers/image.php */
