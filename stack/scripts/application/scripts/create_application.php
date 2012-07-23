<?php

/**
 * Skeleton
 */

$skel_dir = dirname(__DIR__).DS.'assets';

   create_dir($app_path);
   create_dir($app_path.DS.'logs', TRUE);
        chmod($app_path.DS.'logs', 0777);

   create_dir($app_path.DS.'config');
  create_file($app_path.DS.'config'.DS.'application'.EXT, "<?php\n");
  create_file($app_path.DS.'config'.DS.'resources'.EXT, "<?php return array();\n");
        chmod($app_path.DS.'config'.DS.'resources'.EXT, 0777);

   create_dir($app_path.DS.'config'.DS.'environments');
    copy_file($app_path.DS.'config'.DS.'environments', $skel_dir.DS.'development'.EXT);
    copy_file($app_path.DS.'config'.DS.'environments', $skel_dir.DS.'production'.EXT);

   create_dir($app_path.DS.'config'.DS.'initializers', TRUE);
    copy_file($app_path.DS.'config', $skel_dir.DS.'routes'.EXT);

   create_dir($app_path.DS.'controllers');
    copy_file($app_path.DS.'controllers', $skel_dir.DS.'home'.EXT);
    copy_file($app_path.DS.'controllers', $skel_dir.DS.'error'.EXT);
     template($app_path.DS.'controllers', $skel_dir.DS.'base'.EXT, array(
        'app_name' => basename($app_path),
      ));

   create_dir($app_path.DS.'database');
        chmod($app_path.DS.'database', 0777);

   create_dir($app_path.DS.'database'.DS.'backup', TRUE);
   create_dir($app_path.DS.'database'.DS.'migrate', TRUE);

  create_file($app_path.DS.'database'.DS.'db.sqlite');
        chmod($app_path.DS.'database'.DS.'db.sqlite', 0777);

    copy_file($app_path.DS.'database', $skel_dir.DS.'schema'.EXT);
  create_file($app_path.DS.'database'.DS.'seeds'.EXT, "<?php\n");

   create_dir($app_path.DS.'library');
  create_file($app_path.DS.'library'.DS.'helpers'.EXT, "<?php\n");

   create_dir($app_path.DS.'static', TRUE);
        chmod($app_path.DS.'static', 0777);

   create_dir($app_path.DS.'static'.DS.'css');
        chmod($app_path.DS.'static'.DS.'css', 0777);

   create_dir($app_path.DS.'static'.DS.'img');
        chmod($app_path.DS.'static'.DS.'img', 0777);

   create_dir($app_path.DS.'static'.DS.'js');
        chmod($app_path.DS.'static'.DS.'js', 0777);

     copy_dir($app_path, $skel_dir.DS.'tasks');

   create_dir($app_path.DS.'assets');
   create_dir($app_path.DS.'assets'.DS.'css');
  create_file($app_path.DS.'assets'.DS.'app.css', "/**\n *= include base\n */\n");
    copy_file($app_path.DS.'assets'.DS.'css', $skel_dir.DS.'base.css.chess');

   create_dir($app_path.DS.'assets'.DS.'js');
    copy_file($app_path.DS.'assets'.DS.'js', $skel_dir.DS.'jquery.min.js');
    copy_file($app_path.DS.'assets'.DS.'js', $skel_dir.DS.'modernizr.min.js');
  create_file($app_path.DS.'assets'.DS.'app.js', sprintf("/**\n%s\n */\n", join("\n", array(
    ' *= require jquery.min',
    ' *= require modernizr.min',
    ' *= include lib/console',
    ' *= include lib/jquery-ujs',
    ' *= include script',
  ))));

   create_dir($app_path.DS.'assets'.DS.'js'.DS.'lib');
    copy_file($app_path.DS.'assets'.DS.'js'.DS.'lib', $skel_dir.DS.'console.js');
    copy_file($app_path.DS.'assets'.DS.'js'.DS.'lib', $skel_dir.DS.'jquery-ujs.js');
    copy_file($app_path.DS.'assets'.DS.'js', $skel_dir.DS.'script.js.coffee');

   create_dir($app_path.DS.'views');
   create_dir($app_path.DS.'views'.DS.'error');
    copy_file($app_path.DS.'views'.DS.'error', $skel_dir.DS.'views'.DS.'not_found.html.php.tamal');
    copy_file($app_path.DS.'views'.DS.'error', $skel_dir.DS.'views'.DS.'unknown.html.php.tamal');

   create_dir($app_path.DS.'views'.DS.'layouts');
    copy_file($app_path.DS.'views'.DS.'layouts', $skel_dir.DS.'views'.DS.'default.html.php.tamal');

   create_dir($app_path.DS.'views'.DS.'home');
    copy_file($app_path.DS.'views'.DS.'home', $skel_dir.DS.'views'.DS.'index.html.php.tamal');


    $ignored_files = array(
      '*~',
      '*.log',
      'tetlphp',
      '.develop',
      '.DS_Store',
      'logs/*.log',
      'database/db.sqlite',
      'config/resources.php',
      'config/tables.php',
      'static/img',
      'static/css',
      'static/js',
      'assets/_',
    );

  create_file($app_path.DS.'.gitignore', join("\n", $ignored_files) . "\n");
    copy_file($app_path, $skel_dir.DS.'.develop');
    copy_file($app_path, $skel_dir.DS.'.htaccess');

    copy_file($app_path, $skel_dir.DS.'Stubfile');
    copy_file($app_path, $skel_dir.DS.'favicon.ico');
    copy_file($app_path, $skel_dir.DS.'config'.EXT);
    copy_file($app_path, $skel_dir.DS.'index'.EXT);

/* EOF: ./stack/scripts/application/scripts/create_application.php */
