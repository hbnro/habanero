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
cssp_helper::implement('image_path', function ($path) {
  $img_file = cssp::path($path);

  if (defined('ROOT') && is_file($img_file)) {
    $file_hash = md5(md5_file($img_file) . filesize($img_file));
    $file_name = extn($path, TRUE) . $file_hash . ext($path, TRUE);

    $path = str_replace(basename($path), $file_name, $path);
    $path = str_replace('../', ROOT . 'static/', $path);
  }
  return "url!($path)";
});

/* EOF: ./library/cssp/helpers/image.php */
