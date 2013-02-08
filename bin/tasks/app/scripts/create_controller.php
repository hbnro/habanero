<?php

$out_file = path(APP_PATH, 'app', 'controllers', "$name.php");

if (is_file($out_file)) {
  error("\n  Controller '$name' already exists\n");
} else {
  add_controller($name, arg('A no-action'));
  add_route($name, "$name#index", $name);

  if ( ! arg('V no-view')) {
    $text = "section\n  header\n    $name#index.view\n  pre = path(APP_PATH, 'app', 'views', '$name', 'index.php.neddle')";
    add_view($name, 'index.php.neddle', "$text\n");
  }
}
