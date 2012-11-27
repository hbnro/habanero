<?php

$out_file = path(APP_PATH, 'app', 'controllers', "$name.php");

if ( ! is_file($out_file)) {
  error("\n  Missing '$name' controller\n");
} elseif ( ! $action) {
  error("\n  Missing action for '$name' controller\n");
} else {
  $continue = TRUE;

  $method = arg('m method') ?: 'get';
  $route  = arg('r route') ?: "$name/$action";
  $path   = arg('p path') ?: "{$name}_$action";


  if ( ! arg('A no-action')) {
    if ( ! add_action($name, $action, $method, $route, $path)) {
      error("\n  Action '$action' already exists\n");
      $continue = FALSE;
    }
  }


  if ($continue) {
    add_route($route, "$name#$action", $path, $method);

    if ( ! arg('V no-view')) {
      $text = "section\n  header\n    h1 $name#$action.view\n  pre = path(APP_PATH, 'app', 'views', '$name', '$action.php.neddle')";
      add_view($name, "$action.php.neddle", "$text\n");
    }
  }
}
