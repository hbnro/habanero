<?php

$skel_dir = path(dirname(__DIR__), 'assets');

   create_dir($app_path);
   create_dir(path($app_path, 'logs'), 0777);

   create_dir(path($app_path, 'config'));
  create_file(path($app_path, 'config', 'application.php'), "<?php\n");
    copy_file(path($app_path, 'config', 'environments'), path($skel_dir, 'config', 'development.php'));
    copy_file(path($app_path, 'config', 'environments'), path($skel_dir, 'config', 'production.php'));

   create_dir(path($app_path, 'config', 'initializers'));
    copy_file(path($app_path, 'config'), path($skel_dir, 'config', 'routes.php'));

   create_dir(path($app_path, 'app', 'cache'), 0777);
   create_dir(path($app_path, 'app', 'models'), 0777, TRUE);

   $vars = array(
      'app_name' => basename($app_path),
    );

  create_file(path($app_path, 'app', 'controllers', 'home.php'), template(path($skel_dir, 'home.php'), $vars));
  create_file(path($app_path, 'app', 'controllers', 'base.php'), template(path($skel_dir, 'base.php'), $vars));

   create_dir(path($app_path, 'database'), 0777);
  create_file(path($app_path, 'database', 'sqlite.db'), '', 0777);

   create_dir(path($app_path, 'library'), 0777, TRUE);

   create_dir(path($app_path, 'static'), 0777, TRUE);
   create_dir(path($app_path, 'static', 'font'), 0777);
   create_dir(path($app_path, 'static', 'css'), 0777);
   create_dir(path($app_path, 'static', 'img'), 0777);
   create_dir(path($app_path, 'static', 'js'), 0777);

   create_dir(path($app_path, 'tasks'), 0777);

   create_dir(path($app_path, 'app', 'assets'));
   create_dir(path($app_path, 'app', 'assets', 'img'));
   create_dir(path($app_path, 'app', 'assets', 'font'));

    copy_file(path($app_path, 'app', 'assets', 'css'), path($skel_dir, 'config', 'app.css'));
    copy_file(path($app_path, 'app', 'assets', 'css', 'app'), path($skel_dir, 'styles', 'styles.css.less'));
    copy_file(path($app_path, 'app', 'assets', 'css'), path($skel_dir, 'styles', 'sauce.less'));
    copy_file(path($app_path, 'app', 'assets', 'css'), path($skel_dir, 'styles', 'media.less'));
    copy_file(path($app_path, 'app', 'assets', 'css'), path($skel_dir, 'styles', 'base.less'));

    copy_file(path($app_path, 'app', 'assets', 'js'), path($skel_dir, 'config', 'app.js'));
    copy_file(path($app_path, 'app', 'assets', 'js', 'app'), path($skel_dir, 'scripts', 'script.js.coffee'));
   create_dir(path($app_path, 'app', 'assets', 'js', 'lib'), 0777);

   create_dir(path($app_path, 'app', 'views'));
    copy_file(path($app_path, 'app', 'views', 'layouts'), path($skel_dir, 'views', 'raising.php.neddle'));
    copy_file(path($app_path, 'app', 'views', 'layouts'), path($skel_dir, 'views', 'default.php.neddle'));
    copy_file(path($app_path, 'app', 'views', 'home'), path($skel_dir, 'views', 'index.php.neddle'));

    copy_file($app_path, path($skel_dir, 'gitignore.txt'));
    copy_file($app_path, path($skel_dir, 'htaccess.txt'));
    copy_file($app_path, path($skel_dir, 'composer.json'));
    copy_file($app_path, path($skel_dir, 'bowerrc.txt'));
    copy_file($app_path, path($skel_dir, 'exclude.txt'));
    copy_file($app_path, path($skel_dir, 'deploy.sh'));

    copy_file($app_path, path($skel_dir, 'favicon.ico'));
    copy_file($app_path, path($skel_dir, 'config.php'));
    copy_file($app_path, path($skel_dir, 'index.php'));
