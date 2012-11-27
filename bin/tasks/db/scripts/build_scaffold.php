<?php

$base = arg('b', 'base') ?: $name;
$vars = compact('pk', 'base', 'name', 'fields', 'model_class');

$skel_dir = path(dirname(__DIR__), 'assets');
$out_file = path(APP_PATH, 'app', 'controllers', "$base.php");

if ( ! is_file($out_file) OR arg('f', 'force')) {
  $routes = render(path($skel_dir, 'routes.php'), $vars);
  $controller = render(path($skel_dir, 'controller.php'), $vars);
  $index_view = render(path($skel_dir, 'views', 'index.php'), $vars);
  $create_view = render(path($skel_dir, 'views', 'create.php'), $vars);
  $modify_view = render(path($skel_dir, 'views', 'modify.php'), $vars);

  $errors_tpl = <<<'TPL'
- if $error
  ul.error { data => array(errors => 'true') }
    - foreach $error as $field => $text
      li { data => compact('field') } = $text
TPL;


  $klass = preg_quote($model_class, '/');

  add_controller($base, TRUE);
  inject_into_file($out_file, $controller, array(
    'unless' => "/\b$klass::/",
    'before' => '/\}[^{}]*?$/',
  ));


     create_dir(path(APP_PATH, 'app', 'views', $base));
    create_file(path(APP_PATH, 'app', 'views', $base, 'index.php.neddle'), $index_view);

    create_file(path(APP_PATH, 'app', 'views', $base, 'create.php.neddle'), $create_view);
    create_file(path(APP_PATH, 'app', 'views', $base, 'modify.php.neddle'), $modify_view);

    create_file(path(APP_PATH, 'app', 'views', $base, 'errors.php.neddle'), "$errors_tpl\n");

    append_file(path(APP_PATH, 'config', 'routes.php'), "\n$routes", "/'root'\s*=>\s*'\/$base'/");
} else {
  error("Scaffold for '$base' already exists");
}
