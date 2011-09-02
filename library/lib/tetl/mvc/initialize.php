<?php

/**
 * MVC initialization
 */

import('tetl/server');

bootstrap::bind(function($app)
{
  i18n::load_path(__DIR__.DS.'locale', 'mvc');

  $controllers_path = realpath(option('mvc.controllers_path'));
  $helpers_path = realpath(option('mvc.helpers_path'));
  $models_path = realpath(option('mvc.models_path'));
  $views_path = realpath(option('mvc.views_path'));


  $request = request::methods();

  request::implement('dispatch', function(array $params = array())
    use(&$request, $controllers_path, $helpers_path, $models_path, $views_path)
  {
    if (is_callable($params['to']))
    {
      $request['dispatch']($params);
    }
    else
    {
      list($controller, $action) = explode('#', (string) $params['to']);

      $controller_file = $controllers_path.DS.$controller.EXT;

      if ( ! is_file($controller_file))
      {
        raise(ln('mvc.controller_missing', array('name' => $controller_file)));
      }


      /**#@+
       * @ignore
       */

      require __DIR__.DS.'controller'.EXT;
      require $controller_file;

      /**#@-*/

      $class_name  = camelcase($controller, TRUE);
      $class_name .= 'Controller';


      if ( ! class_exists($class_name))
      {
        raise(ln('mvc.class_missing', array('class' => $class_name)));
      }
      elseif ( ! $class_name::defined($action))
      {
        raise(ln('mvc.action_missing', array('class' => $class_name, 'action' => $action)));
      }


      $helper_file = $helpers_path.DS.$controller.EXT;

      if ( ! is_file($helper_file))
      {
        raise(ln('mvc.helper_missing', array('name' => $helper_file)));
      }


      /**
       * @ignore
       */
      require $helper_file;


      spl_autoload_register(function($model_name)
        use($models_path)
      {
        $model_file = $models_path.DS.underscore($model_name).EXT;

        if (is_file($model_file))
        {
          /**#@+
            * @ignore
            */

          if ( ! class_exists('db'))
          {
            import('tetl/db');

            require __DIR__.DS.'model'.EXT;
          }

          require $model_file;

          /**#@-*/
        }
      });


      $class_name::defined('init') && $class_name::init();
      $class_name::$action();


      $view_file = findfile($views_path.DS.'scripts'.DS.$controller, "$action.*", FALSE, 1);

      if ( ! is_file($view_file))
      {
        raise(ln('mvc.view_missing', array('name' => $controller, 'action' => $action)));
      }


      /**
       * @ignore
       */
      require __DIR__.DS.'view'.EXT;

      $view = View::load($view_file, (array) $class_name::$view);

      if ( ! is_false($class_name::$layout))
      {
        $layout_file = $views_path.DS.'layouts'.DS.$class_name::$layout.EXT;

        if ( ! is_file($layout_file))
        {
          raise(ln('mvc.layout_missing', array('name' => $layout_file)));
        }

        $view = render($layout_file, TRUE, array(
          'locals' => array(
            'yield' => $view,
            'title' => $class_name::$title,
          ),
        ));
      }

      $output = $class_name::$response;

      $output['output'] = $view;

      response($output);
    }
  });

  return $app;
});

/* EOF: ./lib/tetl/mvc/initialize.php */
