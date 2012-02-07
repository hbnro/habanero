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
cssp_helper::implement('image_size', function ($path, $key = -1) {
  static $cache = array();


  if (empty($cache[$path])) {
    $img_file = cssp::path($path);
    $cache[$path] = getimagesize($img_file);
  }

  $test = $cache[$path];
  $test = ! empty($test[$key]) ? $test[$key] : 0;

  return "{$test}px";
});


/**
 * Image url
 *
 * @param  string Path
 * @return string
 */
cssp_helper::implement('image_path', function ($path, $raw = FALSE) {
  $img_file = cssp::path($path);

  if (is_file($img_file)) {

    $file_hash = str_replace(APP_PATH.DS.'views'.DS.'assets'.DS.'img'.DS, '', $img_file);
    $file_name = extn($path, TRUE) . assets::fetch($file_hash) . ext($path, TRUE);

    $path = str_replace(basename($path), $file_name, $path);

    if ($raw) {
      return $path;
    }
  }
  return "url!($path)";
});

/* EOF: ./library/cssp/helpers/image.php */
