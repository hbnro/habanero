<?php

/**
 * Application assets
 */

// includes js
function javascript_for($name) {
  assets::prepend(tag('script', array('src' => assets::build($name, 'js'))), 'body');
}

// includes css
function stylesheet_for($name) {
  assets::prepend(tag('link', array('rel' => 'stylesheet', 'href' => assets::build($name, 'css'))), 'head');
}

// includes security ;-)
function csrf_meta_tag() {
  echo tag('meta', array('name' => 'csrf-token', 'content' => option('csrf_token')));
}

// good for routing at mockz!
function asset_url($for) {
  return path_to(assets::fetch($for), TRUE);
}

/* EOF: ./library/assets/functions.php */
