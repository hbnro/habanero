<?php

/**
 * MVC initialization
 */

call_user_func(function () {
  import('tetl/server');

  define('CWD', dirname(APP_PATH));

  config(CWD.DS.'config'.EXT);
  config(CWD.DS.'config'.DS.'application'.EXT);
  config(CWD.DS.'config'.DS.'environments'.DS.option('environment').EXT);


  $bootstrap = bootstrap::methods();

  bootstrap::implement('raise', function ($message)
    use($bootstrap) {
    $error_status = 500;

    switch (option('environment')) {
      case 'development';
        $bootstrap['raise']($message);
      break;
      case 'production';
      case 'testing';
      default;
        if (preg_match('/^(?:GET|PUT|POST|DELETE)\s+\/.+?$/', $message)) {
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


  bootstrap::bind(function ($app) {
    import('app/base/controller');
    import('app/base/model');
    import('app/base/view');

    import('tetl/assets');
    import('tetl/taml');
    import('tetl/css');

    i18n::load_path(__DIR__.DS.'locale', 'mvc');

    view::register('taml', function ($file, array $vars = array()) {
      return taml::render($file, $vars);
    });


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

        require $controller_file;

        /**#@-*/

        $class_name  = $controller . '_controller';


        if ( ! class_exists($class_name)) {
          raise(ln('mvc.class_missing', array('controller' => $class_name)));
        } elseif ( ! $class_name::defined($action)) {
          raise(ln('mvc.action_missing', array('controller' => $class_name, 'action' => $action)));
        }


        $helper_file = CWD.DS.'app'.DS.'helpers'.DS.$controller.EXT;

        if (is_file($helper_file)) {
          /**
           * @ignore
           */
          require $helper_file;
        }

        $class_name::defined('init') && $class_name::init();
        $class_name::$action();

        if ( ! is_false($class_name::$layout)) {
          $class_name::$head []= tag('meta', array('name' => 'csrf-token', 'content' => TOKEN));
          $class_name::$head []= tag('link', array('rel' => 'stylesheet', 'href' => url_for('/all.css')));

          $layout_file = findfile(CWD.DS.'app'.DS.'views'.DS.'layouts', $class_name::$layout.'*', FALSE, 1);

          if ( ! is_file($layout_file)) {
            raise(ln('mvc.layout_missing', array('name' => $layout_file)));
          }


          assets::inline(tag('script', array('src' => url_for('/all.js'))), 'body');

          $view = view::render($layout_file, array(
            'body' => view::load(CWD.DS.'app'.DS.'views'.DS.$controller.DS.$action, (array) $class_name::$view),
            'head' => join("\n", $class_name::$head),
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


  route('/all.:type', function () {//TODO: ...
    $type      = params('type');

    $minify    = option('environment') === 'production';

    $base_path = CWD.DS.'app'.DS.'views'.DS.'assets';
    $base_file = $base_path.DS.$type.DS."app.$type";


    assets::setup('path', $base_path);

    assets::compile('css', function ($file)
      use($base_path, $minify) {
      import('tetl/css');
      css::setup('path', $base_path.DS.'css');
      return css::render($file, $minify);
    });

    assets::compile('js', function ($file)
      use($minify) {// TODO: use JSMin instead...
      static $regex = array(
                      '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/' => '',
                      '/\s*([?!<(\[\])>=:,+]|if|else|for|while)\s*/' => '\\1',
                      '/\s{2,}/' => '',
                    );


      $text = read($file);

      if ($minify) {
        $text = preg_replace(array_keys($regex), $regex, $text);
        $text = str_replace('elseif', 'else if', $text);
      }
      return $text;
    });


    $test = preg_replace_callback('/\s+\*=\s+(.+?)\s/s', function ($match)
      use($type) {
      assets::append("$match[1].$type");
    }, read($base_file));

    $test = preg_replace('/\/\*[*\s]*?\*\//s', '', $test);

    assets::$type(trim($test));
  }, array(
    'constraints' => array(
      ':type' => '(css|js)',
    ),
  ));

});

/* EOF: ./stack/library/app/base/initialize.php */
