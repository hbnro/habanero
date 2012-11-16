<?php

$out_file = path(APP_PATH, 'controllers', "$name.php");

if (is_file($out_file)) {
  error("\n  Controller '$name' already exists\n");
} else {
  add_class($out_file, "{$name}_controller", 'base_controller', array('index'));
  add_route($name, "$name#index", $name);

  if ( ! arg('V', 'no-view')) {
    $text = "section\n  header\n    h1 $name#index.view\n  pre = path(APP_PATH, 'views', '$name', 'index.php.neddle')";
    add_view($name, 'index.php.neddle', "$text\n");
  }
}