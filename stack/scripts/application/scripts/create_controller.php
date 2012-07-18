<?php

$out_file = mkpath(APP_PATH.DS.'controllers').DS.$name.EXT;

if (is_file($out_file)) {
  error(ln('app.controller_already_exists', array('name' => $name)));
} else {
  success(ln('app.controller_class_building', array('name' => $name)));
  add_class($out_file, "{$name}_controller", cli::flag('parent') ?: 'base_controller', 'index');

  success(ln('app.controller_route_building', array('name' => $name)));
  add_route($name, "$name#index", $name);

  if ( ! cli::flag('no-view')) {
    success(ln('app.controller_view_building', array('name' => $name)));
    $text = "section\n  header\n    h1 $name#index.view\n  pre = APP_PATH.DS.'views'.DS.'$name'.DS.'index.html.php.tamal'";
    add_view($name, 'index', "$text\n", '.php.tamal');
  }
}

/* EOF: ./stack/scripts/application/scripts/create_controller.php */
