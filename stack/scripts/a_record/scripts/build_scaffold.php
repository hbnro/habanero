<?php

$skel_dir = dirname(__DIR__).DS.'assets';
$params = array('locals' => compact('pk', 'model', 'fields'));
$controller_file = APP_PATH.DS.'controllers'.DS."{$model}s".EXT;

if ( ! is_file($controller_file)) {
  $routes = render($skel_dir.DS.'routes'.EXT, TRUE, $params);
  $controller = render($skel_dir.DS.'controller'.EXT, TRUE, $params);

  $index_view = render($skel_dir.DS.'views'.DS.'index'.EXT, TRUE, $params);
  $create_view = render($skel_dir.DS.'views'.DS.'create'.EXT, TRUE, $params);
  $modify_view = render($skel_dir.DS.'views'.DS.'modify'.EXT, TRUE, $params);

  $errors_tpl = <<<'TPL'
- if $error
  ul { data => array(errors => 'true') }
    - foreach $error as $field => $text
      li { data => compact('field') } = $text
TPL;

  inject_into_file(APP_PATH.DS.'index'.EXT, "\n  import('a_record');", array(
    'unless' => '/\ba_record\b/',
    'after' => '/run\s*\(\s*function\s*\(.*?\)\s*\{/',
  ));

    create_file($controller_file, $controller);

     create_dir(APP_PATH.DS.'views'.DS."{$model}s");
    create_file(APP_PATH.DS.'views'.DS."{$model}s".DS.'index.html.tamal', $index_view);
    create_file(APP_PATH.DS.'views'.DS."{$model}s".DS.'create.html.tamal', $create_view);
    create_file(APP_PATH.DS.'views'.DS."{$model}s".DS.'modify.html.tamal', $modify_view);

    create_file(APP_PATH.DS.'views'.DS."{$model}s".DS.'errors.html.tamal', "$errors_tpl\n");

    append_file(APP_PATH.DS.'routes'.EXT, "$routes\n");
} else {
  error(ln('ar.crud_already_exists', array('name' => $model)));
}
