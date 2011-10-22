<?php

require dirname(__DIR__).DS.'controller'.EXT;
i18n::load_path(__DIR__.DS.'locale', 'app');

import('a_record');
import('partial');
import('taml');


$request = request::methods();

request::implement('dispatch', function (array $params = array())
  use($request) {
  if (is_callable($params['to'])) {
    $request['dispatch']($params);
  } else {
    list($controller, $action) = explode('#', (string) $params['to']);

    $controller_file = CWD.DS.'app'.DS.'controllers'.DS.$controller.EXT;

    if ( ! is_file($controller_file)) {
      raise(ln('mvc.controller_missing', array('name' => $controller_file)));
    }


    /**#@+
     * @ignore
     */

    require CWD.DS.'app'.DS.'controllers'.DS.'base'.EXT;
    require $controller_file;

    /**#@-*/

    $class_name  = $controller . '_controller';


    if ( ! class_exists($class_name)) {
      raise(ln('mvc.class_missing', array('controller' => $class_name)));
    } elseif ( ! $class_name::defined($action)) {
      raise(ln('mvc.action_missing', array('controller' => $class_name, 'action' => $action)));
    }


    /**
     * @ignore
     */
    require CWD.DS.'app'.DS.'helpers'.DS.'base'.EXT;

    $helper_file = CWD.DS.'app'.DS.'helpers'.DS.$controller.EXT;

    is_file($helper_file) && require $helper_file;
    /**#@-*/

    $class_name::defined('init') && $class_name::init();
    $class_name::$action();

    $view = partial::load(CWD.DS.'app'.DS.'views'.DS.$controller.DS.$action, (array) $class_name::$view);

    if ( ! is_false($class_name::$layout)) {
      $class_name::$head []= tag('meta', array('name' => 'csrf-token', 'content' => TOKEN));
      $class_name::$head []= tag('link', array('rel' => 'stylesheet', 'href' => url_for('/all.css')));

      $layout_file = CWD.DS.'app'.DS.'views'.DS.'layouts'.DS.$class_name::$layout;

      import('assets');

      assets::inline(tag('script', array('src' => url_for('/all.js'))), 'body');

      $view = partial::load($layout_file, array(
        'body' => $view,
        'head' => join("\n", $class_name::$head),
        'title' => $class_name::$title,
      ));
    }

    $output = $class_name::$response;

    $output['output'] = $view;

    response($output);
  }
});
