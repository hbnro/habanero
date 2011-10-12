<?php

/**
 * MVC initialization
 */

call_user_func(function()
{
  import('tetl/server');

  define('CWD', dirname(APP_PATH));

  config(CWD.DS.'config'.DS.'application'.EXT);
  config(CWD.DS.'config'.DS.'environments'.DS.option('environment').EXT);


  $bootstrap = bootstrap::methods();

  bootstrap::implement('raise', function($message)
    use($bootstrap)
  {
    $error_status = 500;

    switch (option('environment'))
    {
      case 'development';
        $bootstrap['raise']($message);
      break;
      case 'production';
      case 'testing';
      default;
        if (preg_match('/^(?:GET|PUT|POST|DELETE)\s+\/.+?$/', $message))
        {
          $error_status = 404;
        }
      break;
    }


    $error_file = CWD.DS.'app'.DS.'views'.DS.'errors'.DS.$error_status;

    response(view::load($error_file), array(
      'status' => $error_status,
      'message' => $message,
    ));
  });


  bootstrap::bind(function($app)
  {
    require __DIR__.DS.'controller'.EXT;
    require __DIR__.DS.'model'.EXT;
    require __DIR__.DS.'view'.EXT;

    import('tetl/assets');

    i18n::load_path(__DIR__.DS.'locale', 'mvc');

    view::register('taml', function($file, array $vars = array())
    {
      import('tetl/taml');

      return taml::render($file, $vars);
    });


    $request = request::methods();

    request::implement('dispatch', function(array $params = array())
      use($request)
    {
      if (is_callable($params['to']))
      {
        $request['dispatch']($params);
      }
      else
      {
        list($controller, $action) = explode('#', (string) $params['to']);

        $controller_file = CWD.DS.'app'.DS.'controllers'.DS.$controller.EXT;

        if ( ! is_file($controller_file))
        {
          raise(ln('mvc.controller_missing', array('name' => $controller_file)));
        }


        /**#@+
         * @ignore
         */

        require $controller_file;

        /**#@-*/

        $class_name  = $controller . '_controller';


        if ( ! class_exists($class_name))
        {
          raise(ln('mvc.class_missing', array('controller' => $class_name)));
        }
        elseif ( ! $class_name::defined($action))
        {
          raise(ln('mvc.action_missing', array('controller' => $class_name, 'action' => $action)));
        }


        $helper_file = CWD.DS.'app'.DS.'helpers'.DS.$controller.EXT;

        if (is_file($helper_file))
        {
          /**
           * @ignore
           */
          require $helper_file;
        }

        $class_name::defined('init') && $class_name::init();
        $class_name::$action();

        if ( ! is_false($class_name::$layout))
        {
          $css_file = CWD.DS.'app'.DS.'views'.DS.'styles'.DS."$controller.css";

          if (is_file($css_file))
          {
            $partial = CWD.DS.'app'.DS.'views'.DS.'assets'.DS.'css'.DS."_$controller.css";

            if ( ! is_file($partial) OR (filemtime($css_file) > filemtime($partial)))
            {
              import('tetl/css');

              css::setup('path', CWD.DS.'app'.DS.'views'.DS.'styles');

              write($partial, css::render($css_file, option('environment') <> 'development'));
            }
          }

          $class_name::$head []= tag('meta', array('name' => 'csrf-token', 'content' => TOKEN));
          #$class_name::$head []= tag('link', array('rel' => 'stylesheet', 'href' => url_for('/assets/all.css?skip')));

          $layout_file = findfile(CWD.DS.'app'.DS.'views'.DS.'layouts', $class_name::$layout.'*', FALSE, 1);

          if ( ! is_file($layout_file))
          {
            raise(ln('mvc.layout_missing', array('name' => $layout_file)));
          }


          #assets::inline(tag('script', array('src' => url_for('/assets/all.js?skip'))), 'body');

          $view = view::load(CWD.DS.'app'.DS.'views'.DS.'scripts'.DS.$controller.DS.$action, (array) $class_name::$view);
          $view = view::render($layout_file, array(
            'body' => "$view\n" . assets::after(),
            'head' => assets::before() . "\n" . join("\n", $class_name::$head),
            'title' => $class_name::$title,
          ));
        }

        $output = $class_name::$response;

        $output['output'] = $view;

        response($output);
      }
    });

    return $app;
  });


  route('/assets/all.:type', function()
  {//TODO: ...
    if (params('type') === 'css')
    {
      import('tetl/css');

      css::setup('path', CWD.DS.'app'.DS.'views'.DS.'styles');

      foreach (findfile(CWD.DS.'app'.DS.'views'.DS.'assets'.DS.'css', '_*.css') as $css_file)
      {
        assets::append(basename($css_file));
      }
    }

    assets::setup('path', CWD.DS.'app'.DS.'views'.DS.'assets');

    call_user_func('assets::' . params('type'));
  }, array(
    'constraints' => array(
      ':type' => '(css|js)',
    ),
  ));

});

/* EOF: ./stack/library/app/base/initialize.php */
