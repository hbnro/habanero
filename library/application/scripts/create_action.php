<?php

@list($parent, $name) = explode(':', $name);

$out_file = mkpath(CWD.DS.'app'.DS.'controllers').DS.$parent.EXT;

if ( ! $parent) {
  error(ln('app.controller_missing'));
} elseif ( ! is_file($out_file)) {
  error(ln('app.controller_not_exists', array('name' => $parent)));
} elseif ( ! $name) {
  error(ln("app.missing_{$what}_name"));
} else {
  $content = read($out_file);

  if (preg_match("/\b(?:private|public)\s+static\s+function\s+$name\s*\(/s", $content)) {
    error(ln('app.action_already_exists', array('name' => $name, 'controller' => $parent)));
  } else {
    success(ln('app.action_method_building', array('name' => $name, 'controller' => $parent)));

    $code = "  public static function $name()\n"
          . "  {\n  }\n\n";

    write($out_file, preg_replace('/\}[^{}]*?$/s', "$code\\0", $content));


    success(ln('app.action_route_building', array('name' => $name, 'controller' => $parent)));

    $route_file = CWD.DS.'app'.DS.'routes'.EXT;
    $method     = cli::flag('method') ?: 'get';
    $repl       = ";\n  %-6s('/$parent/$name', '$parent#$name', array('path' => '{$parent}_$name'))\\0";
    write($route_file, preg_replace('/;[^;]*?$/', sprintf($repl, $method), read($route_file)));


    if (cli::flag('view')) {
      success(ln('app.action_view_building', array('name' => $name, 'controller' => $parent)));

      $text = "<section>\n  <header>$parent#$name.view</header>\n  <pre><?php echo __FILE__; ?></pre>\n</section>\n";
      write(mkpath(CWD.DS.'app'.DS.'views'.DS.$parent).DS.$name.EXT, $text);
    }
  }
}

/* EOF: ./library/application/scripts/create_action.php */
