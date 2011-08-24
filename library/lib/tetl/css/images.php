<?php

/**
 * CSS basic image functions
 */

/**
 * Image properties
 *
 * @param  string Path
 * @return string
 */
css::implement('image', function($path)
{
  static $cache = array();


  if (isset($cache[$path]))
  {
    return $cache[$path];
  }


  $out = array(
    'width' => 'auto',
    'height' => 'auto',
    'url' => "url!($path)",
  );


  $img_file = css::path($path);

  if (is_file($img_file))
  {
    $test = getimagesize($img_file);

    $out['width']  = "$test[0]px";
    $out['height'] = "$test[1]px";

    $out['data']   = 'data:image/' . str_replace('jpg', 'jpeg', ext($img_file));
    $out['data']  .= ';base64,' . base64_encode(read($img_file));

    $out['url']    = url_to(css::path($path));
  }

  $cache[$path] = $out;

  return $out;
});

/* EOF: ./lib/tetl/css/images.php  */
