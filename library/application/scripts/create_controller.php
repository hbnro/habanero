<?php

$out_file = mkpath(getcwd().DS.'controllers').DS.$name.EXT;

if (is_file($out_file)) {
  error(ln('app.controller_already_exists', array('name' => $name)));
} else {
  // TODO: use functions like create_class, append_class, prepend_class, add_route, etc.
  $type = cli::flag('parent') ?: 'base_controller';
  $code = "<?php\n\nclass {$name}_controller extends $type\n{"
        . "\n\n  public static function index()\n"
        . "  {\n  }\n\n}\n";

  success(ln('app.controller_class_building', array('name' => $name)));
  write($out_file, $code);

  success(ln('app.controller_route_building', array('name' => $name)));

  $route_file = getcwd().DS.'routes'.EXT;
  write($route_file, preg_replace('/;[^;]*?$/', ";\nget('/$name', '$name#index', array('path' => '$name'))\\0", read($route_file)));


  if (cli::flag('helper')) {
    success(ln('app.controller_helper_building', array('name' => $name)));
    write(mkpath(getcwd().DS.'helpers').DS.$name.EXT, "<?php\n");
  }


  if (cli::flag('view')) {
    success(ln('app.controller_view_building', array('name' => $name)));

    $text = "<section>\n  <header>$name#index.view</header>\n  <pre><?php echo getcwd().DS.'views'.DS.'$name'.DS.'index.html'.EXT; ?></pre>\n</section>\n";
    write(mkpath(getcwd().DS.'views'.DS.$name).DS.'index.html'.EXT, $text);
  }
}

/* EOF: ./library/application/scripts/create_controller.php */
