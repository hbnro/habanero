<?php

// MVC middleware
return function($app)
{
  import('tetl/server');
  import('tetl/router');


  // controller prototype
  class controller extends prototype
  {
    public static $view = array();
    public static $helper = array();
    public static $response = array(
      'status' => 200,
      'headers' => array(
        'content-type' => 'text/html',
      ),
    );
  }


  // app configuration
  $controller_path = realpath(option('mvc.controller_path'));
  $view_path = realpath(option('mvc.view_path'));


  // routes trap
  trigger('route', function($params)
    use($controller_path, $view_path)
  {
    if (is_string($params['to']) && strpos($params['to'], '#'))
    {
      list($class, $method) = explode('#', $params['to']);

      $controller_file = $controller_path.DS.$class.EXT;

      if ( ! is_file($controller_file))
      {
        die("File not found: $controller_file");
      }


      require $controller_file;

      // try to MVC
      if ( ! class_exists($class))
      {
        die("Missing class: $class");
      }

      if ( ! $class::defined($method))
      {
        die("Missing method: $class#$method");
      }


      $class::defined('init') && $class::init();

      call_user_func(array($class, $method));


      $view_file = findfile($view_path.DS.$class, "$method.*", FALSE, 1);

      if ( ! is_file($view_file))
      {
        die("File not exists: $view_file");
      }


      $output    = $class::$response;
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


  return function($env)
    use($app)
  {
    call_user_func($app, $env);

    route('*', function()
    {
      raise('Route not found: '.URI);
    });
  };
};
