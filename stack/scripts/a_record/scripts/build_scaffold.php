<?php

$skel_dir = dirname(__DIR__).DS.'assets';
$params = array('locals' => compact('pk', 'model', 'fields'));
$controller_file = APP_PATH.DS.'controllers'.DS."{$model}s".EXT;

if ( ! is_file($controller_file) OR cli::flag('force')) {
  $model_file = mkpath(APP_PATH.DS.'models').DS.$model.EXT;
  ! is_file($model_file) && add_class($model_file, $model, 'db_model');

  $routes = render($skel_dir.DS.'routes'.EXT, TRUE, $params);
  $controller = render($skel_dir.DS.'controller'.EXT, TRUE, $params);

  $index_view = render($skel_dir.DS.'views'.DS.'index'.EXT, TRUE, $params);
  $create_view = render($skel_dir.DS.'views'.DS.'create'.EXT, TRUE, $params);
  $modify_view = render($skel_dir.DS.'views'.DS.'modify'.EXT, TRUE, $params);

  $errors_tpl = <<<'TPL'
- if $error
  ul.error { data => array(errors => 'true') }
    - foreach $error as $field => $text
      li { data => compact('field') } = $text
TPL;

    create_file($controller_file, $controller);

     create_dir(APP_PATH.DS.'views'.DS."{$model}s");
    create_file(APP_PATH.DS.'views'.DS."{$model}s".DS.'index.html.php.tamal', $index_view);
    create_file(APP_PATH.DS.'views'.DS."{$model}s".DS.'create.html.php.tamal', $create_view);
    create_file(APP_PATH.DS.'views'.DS."{$model}s".DS.'modify.html.php.tamal', $modify_view);

    create_file(APP_PATH.DS.'views'.DS."{$model}s".DS.'errors.html.php.tamal', "$errors_tpl\n");

    append_file(APP_PATH.DS.'config'.DS.'routes'.EXT, "\n$routes", array('unless' => "/'root'\s*=>\s*'\/{$model}s'/"));
    append_file(APP_PATH.DS.'index'.EXT, "\n  import('a_record');", array(
      'unless' => '/\ba_record\b/',
      'after' => '/run\s*\(\s*function\s*\(.*?\)\s*\{/',
    ));
} else {
  error(ln('ar.crud_already_exists', array('name' => $model)));
}
