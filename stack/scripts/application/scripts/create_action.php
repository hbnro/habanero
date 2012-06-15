<?php

@list($parent, $name) = explode(':', $name);

$out_file = mkpath(APP_PATH.DS.'controllers').DS.$parent.EXT;

if ( ! $parent) {
  error(ln('app.controller_missing'));
} elseif ( ! is_file($out_file)) {
  error(ln('app.controller_not_exists', array('name' => $parent)));
} elseif ( ! $name) {
  error(ln('app.action_missing'));
} else {
  if (inject_into_file($out_file, function ()
    use($name, $parent) {
    success(ln('app.action_route_building', array('name' => $name, 'controller' => $parent)));

    $method     = cli::flag('method') ?: 'get';
    $route      = cli::flag('route') ?: "$parent/$name";
    $path       = cli::flag('path') ?: "{$parent}_$name";

    add_route($route, "$parent#$name", $path, $method);

    if ( ! cli::flag('no-view')) {
      success(ln('app.action_view_building', array('name' => $name, 'controller' => $parent)));
      $text = "section\n  header\n    h1 $parent#$name.view\n  pre = APP_PATH.DS.'views'.DS.'$parent'.DS.'$name.html.tamal'";
      add_view($parent, $name, "$text\n", '.tamal');
    }

    return "  public static function $name() {\n  }\n";
  }, array(
      'before' => '/\}[^{}]*?$/s',
      'unless' => "/\b[\w\s]*function\s+$name\s*\(/s",
    ))) {
    success(ln('app.action_method_building', array('name' => $name, 'controller' => $parent)));
  } else {
    error(ln('app.action_already_exists', array('name' => $name, 'controller' => $parent)));
  }
}

/* EOF: ./stack/scripts/application/scripts/create_action.php */
