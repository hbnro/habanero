<?php

function javascript_for($name) {
  assets::prepend(tag('script', array('src' => assets::build($name, 'js'))), 'body');
}

function stylesheet_for($name) {
  assets::prepend(tag('link', array('rel' => 'stylesheet', 'href' => assets::build($name, 'css'))), 'head');
}

function csrf_meta_tag() {
  echo tag('meta', array('name' => 'csrf-token', 'content' => option('crsf_token')));
}
