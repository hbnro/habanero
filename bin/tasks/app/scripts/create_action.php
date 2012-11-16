<?php

$out_file = path(APP_PATH, 'app', 'controllers', "$name.php");

if ( ! is_file($out_file)) {
  error("\n  Missing '$name' controller\n");
} elseif ( ! $action) {
  error("\n  Missing action for '$name' controller\n");
} else {
  $method = arg('method') ?: 'get';
  $route  = arg('route') ?: "$name/$action";
  $path   = arg('path') ?: "{$name}_$action";

  add_action($name, $action, $method, $route, $path);
}
