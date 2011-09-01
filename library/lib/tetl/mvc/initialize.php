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
  $views_path = realpath(option('mvc.views_path'));


  $request = request::methods();

  request::implement('dispatch', function(array $params = array())
    use($request, $controllers_path, $helpers_path, $views_path)
  {
    if (is_callable($params['to']))
    {
      $request['dispatch']($params);
    }
    else
    {
      list($class, $method) = explode('#', (string) $params['to']);

      $controller_file = $controllers_path.DS.$class.EXT;

      if ( ! is_file($controller_file))
      {
        die("File not found: $controller_file");
      }


      /**#@+
       * @ignore
       */

      require __DIR__.DS.'controller'.EXT;

      require $controller_file;

      /**#@-*/

      if ( ! class_exists($class))
      {
        die("Missing class: $class");
      }
      elseif ( ! $class::defined($method))
      {
        die("Missing method: $class#$method");
      }


      /**#@+
       * @ignore
       */

      require __DIR__.DS.'model'.EXT;
      require $helpers_path.DS.$class.EXT;

      /**#@-*/



      $class::defined('init') && $class::init();
      $class::$method();


      $view_file = findfile($views_path.DS.'scripts'.DS.$class, "$method.*", FALSE, 1);

      if ( ! is_file($view_file))
      {
        die("File not exists: $view_file");
      }


      $output = $class::$response;
      $extension = ext($view_file);

      switch ($extension)
      {
        case 'taml';
          import('tetl/taml');
          $output['output'] = taml::render($view_file, (array) $class::$view);
        break;
        case 'php';
        case 'phtml';
          $output['output'] = render(array(
            'partial' => $view_file,
            'locals' => (array) $class::$view,
          ));
        break;
        default;
          die("Unknown extension: $extension");
        break;
      }

      response($output);
    }
  });

  return $app;
});

/* EOF: ./lib/tetl/mvc/initialize.php */
