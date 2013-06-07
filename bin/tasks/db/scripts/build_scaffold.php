<?php

$base = arg('b base') ?: plural($name);
$vars = compact('pk', 'base', 'name', 'fields', 'model_class');

$skel_dir = path(dirname(__DIR__), 'assets');
$out_file = path(APP_PATH, 'app', 'controllers', "$base.php");

if ( ! is_file($out_file) OR arg('f force')) {
  $routes = render(path($skel_dir, 'routes.php'), $vars);
  $controller = render(path($skel_dir, 'controller.php'), $vars);
  $show_view = render(path($skel_dir, 'views', 'show.php'), $vars);
  $error_view = render(path($skel_dir, 'views', 'error.php'), $vars);
  $index_view = render(path($skel_dir, 'views', 'index.php'), $vars);
  $create_view = render(path($skel_dir, 'views', 'create.php'), $vars);
  $modify_view = render(path($skel_dir, 'views', 'modify.php'), $vars);

  $klass = preg_quote($model_class, '/');
  $based = preg_quote($base, '/');

  add_controller($base, TRUE);
  inject_into_file($out_file, $controller, array(
    'unless' => "/\b$klass::/",
    'before' => '/\}[^{}]*?$/',
  ));

     create_dir(path(APP_PATH, 'app', 'views', $base));
    create_file(path(APP_PATH, 'app', 'views', $base, 'show.php.neddle'), $show_view);
    create_file(path(APP_PATH, 'app', 'views', $base, 'index.php.neddle'), $index_view);

    create_file(path(APP_PATH, 'app', 'views', $base, 'create.php.neddle'), $create_view);
    create_file(path(APP_PATH, 'app', 'views', $base, 'modify.php.neddle'), $modify_view);

    create_file(path(APP_PATH, 'app', 'views', $base, 'errors.php.neddle'), $error_view);

    append_file(path(APP_PATH, 'config', 'routes.php'), "\n$routes", "/'root'\s*=>\s*'\/$based'/");
} else {
  error("\n  Scaffold for '$base' already exists\n");
}
