<?php

/**
 * CSS image functions
 */

/**
 * Image properties
 *
 * @param  string Path
 * @return string
 */
css_helper::implement('image', function ($path) {
  static $cache = array();


  if (isset($cache[$path])) {
    return $cache[$path];
  }


  $out = array(
    'width' => 'auto',
    'height' => 'auto',
    'url' => "url!($path)",
  );


  $img_file = css::path($path);

  if (is_file($img_file)) {
    $test = getimagesize($img_file);

    $out['width']  = "$test[0]px";
    $out['height'] = "$test[1]px";

    $out['data']   = function ()
      use($img_file) {
        $out  = 'data:image/' . str_replace('jpg', 'jpeg', ext($img_file));
        $out .= ';base64,' . base64_encode(read($img_file));

        return $out;
    };
  }

  $cache[$path] = $out;

  return $out;
});

/* EOF: ./library/tsss/helpers/image.php */
