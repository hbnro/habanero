<?php

/**
 * Application assets
 */

// last-body assets
function after_body() {
  return assets::after();
}

// first-head assets
function before_body() {
  return assets::before();
}

// includes js
function javascript_for($name) {
  assets::prepend(assets::build($name, 'js'), 'body');
}

// includes css
function stylesheet_for($name) {
  assets::prepend(assets::build($name, 'css'), 'head');
}

// includes security ;-)
function csrf_meta_tag() {
  echo tag('meta', array('name' => 'csrf-token', 'content' => option('csrf_token')));
}

// html for assets
function tag_for() {
  $args = func_get_args();
  return assets::apply(__FUNCTION__, $args);
}

// good for routing at mockz!
function asset_for($url) {
  return path_to(assets::fetch($for), TRUE);
}

// resolve paths into urls
function asset_url() {
  $args = func_get_args();
  return assets::apply('url_for', $args);
}

/* EOF: ./library/assets/functions.php */
