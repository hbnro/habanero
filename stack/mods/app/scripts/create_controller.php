<?php

$out_file = mkpath(CWD.DS.'app'.DS.'controllers').DS.$name.EXT;

if (is_file($out_file)) {
  error(ln('app.controller_already_exists', array('name' => $name)));
} else {
  $type = cli::flag('parent') ?: 'controller';
  $code = "<?php\n\nclass {$name}_controller extends $type\n{"
        . "\n\n  public static function index()\n"
        . "  {\n  }\n\n}\n";

  success(ln('app.controller_class_building', array('name' => $name)));
  write($out_file, $code);

  success(ln('app.controller_route_building', array('name' => $name)));

  $route_file = CWD.DS.'app'.DS.'routes'.EXT;
  write($route_file, preg_replace('/;[^;]*?$/', ";\nget('/$name', '$name#index', array('path' => '$name'))\\0", read($route_file)));


  if (cli::flag('helper')) {
    success(ln('app.controller_helper_building', array('name' => $name)));
    write(mkpath(CWD.DS.'app'.DS.'helpers').DS.$name.EXT, "<?php\n");
  }


  if (cli::flag('view')) {
    success(ln('app.controller_view_building', array('name' => $name)));

    $text = "<section>\n  <header>$name#index.view</header>\n  <pre><?php echo __FILE__; ?></pre>\n</section>\n";
    write(mkpath(CWD.DS.'app'.DS.'views'.DS.$name).DS.'index'.EXT, $text);
  }
}
