<?php

$skel_dir = path(dirname(__DIR__), 'assets');

   create_dir($app_path);
   create_dir(path($app_path, 'logs'), 0777);
   create_dir(path($app_path, 'cache'), 0777);

   create_dir(path($app_path, 'config'));
  create_file(path($app_path, 'config', 'application.php'), "<?php\n");
  create_file(path($app_path, 'config', 'resources.php'), "<?php return array();\n", 0777);

    copy_file(path($app_path, 'config', 'environments'), path($skel_dir, 'development.php'));
    copy_file(path($app_path, 'config', 'environments'), path($skel_dir, 'production.php'));

   create_dir(path($app_path, 'config', 'initializers'));
    copy_file(path($app_path, 'config'), path($skel_dir, 'routes.php'));

   create_dir(path($app_path, 'app', 'models'), 0777);

   $vars = array(
      'app_name' => basename($app_path),
    );

  create_file(path($app_path, 'app', 'controllers', 'home.php'), template(path($skel_dir, 'home.php'), $vars));
  create_file(path($app_path, 'app', 'controllers', 'base.php'), template(path($skel_dir, 'base.php'), $vars));


   create_dir(path($app_path, 'database'), 0777);
   create_dir(path($app_path, 'database', 'backup'), 0777);
  create_file(path($app_path, 'database', 'sqlite.db'), '', 0777);
  create_file(path($app_path, 'database', 'fixtures.yaml'), "---\n", 0777);

   create_dir(path($app_path, 'library'));;

   create_dir(path($app_path, 'static'), 0777);
   create_dir(path($app_path, 'static', 'css'), 0777);
   create_dir(path($app_path, 'static', 'img'), 0777);
   create_dir(path($app_path, 'static', 'js'), 0777);

   create_dir(path($app_path, 'tasks'), 0777);

   create_dir(path($app_path, 'app', 'assets'));
   create_dir(path($app_path, 'app', 'assets', 'img'));

  create_file(path($app_path, 'app', 'assets', 'css', 'app.css'), "/**\n *= include app/base\n */\n");
    copy_file(path($app_path, 'app', 'assets', 'css', 'app'), path($skel_dir, 'base.css.less'));
    copy_file(path($app_path, 'app', 'assets', 'css'), path($skel_dir, 'sauce.less'));

    copy_file(path($app_path, 'app', 'assets', 'js'), path($skel_dir, 'jquery.min.js'));
    copy_file(path($app_path, 'app', 'assets', 'js'), path($skel_dir, 'modernizr.min.js'));
  create_file(path($app_path, 'app', 'assets', 'js', 'app.js'), sprintf("/**\n %s\n */\n", join("\n ", array(
    '*= head modernizr',
    '*= require jquery',
    '*= include lib/console',
    '*= include lib/jquery-ujs',
    '*= include app/script',
  ))));

    copy_file(path($app_path, 'app', 'assets', 'js', 'lib'), path($skel_dir, 'console.js'));
    copy_file(path($app_path, 'app', 'assets', 'js', 'lib'), path($skel_dir, 'jquery-ujs.js'));
    copy_file(path($app_path, 'app', 'assets', 'js', 'app'), path($skel_dir, 'script.js.coffee'));

   create_dir(path($app_path, 'app', 'views'));
    copy_file(path($app_path, 'app', 'views', 'error'), path($skel_dir, 'views', '404.php.neddle'));
    copy_file(path($app_path, 'app', 'views', 'error'), path($skel_dir, 'views', '500.php.neddle'));
    copy_file(path($app_path, 'app', 'views', 'layouts'), path($skel_dir, 'views', 'default.php.neddle'));
    copy_file(path($app_path, 'app', 'views', 'home'), path($skel_dir, 'views', 'index.php.neddle'));

    $ignored_files = array(
      '*~',
      '*.log',
      '.DS_Store',
      'database/db.sqlite',
      'config/resources.php',
      'static/img/*',
      'static/css/*',
      'static/js/*',
      'cache/*',
    );

  create_file(path($app_path, '.gitignore'), join("\n", $ignored_files) . "\n");

    copy_file($app_path, path($skel_dir, '.htaccess'));

    copy_file($app_path, path($skel_dir, 'composer.json'));
    copy_file($app_path, path($skel_dir, 'favicon.ico'));
    copy_file($app_path, path($skel_dir, 'config.php'));
    copy_file($app_path, path($skel_dir, 'index.php'));
