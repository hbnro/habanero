<?php

/**
 * Skeleton
 */

$skel_dir = dirname(__DIR__).DS.'assets';

   create_dir(getcwd().DS.'config');
    copy_file(getcwd().DS.'config', $skel_dir.DS.'application'.EXT);

   create_dir(getcwd().DS.'config'.DS.'environments');
  create_file(getcwd().DS.'config'.DS.'environments'.DS.'development'.EXT);
  create_file(getcwd().DS.'config'.DS.'environments'.DS.'production'.EXT);

   create_dir(getcwd().DS.'controllers');
    copy_file(getcwd().DS.'controllers', $skel_dir.DS.'home'.EXT);
     template(getcwd().DS.'controllers', $skel_dir.DS.'base'.EXT, array(
        'app_name' => basename(getcwd()),
      ));

   create_dir(getcwd().DS.'database');
   create_dir(getcwd().DS.'database'.DS.'backup');
   create_dir(getcwd().DS.'database'.DS.'migrate');
  create_file(getcwd().DS.'database'.DS.'db.sqlite');
  create_file(getcwd().DS.'database'.DS.'schema'.EXT, "<?php\n");
  create_file(getcwd().DS.'database'.DS.'seeds'.EXT, "<?php\n");

   create_dir(getcwd().DS.'helpers');
  create_file(getcwd().DS.'helpers'.DS.'base'.EXT, "<?php\n");

   create_dir(getcwd().DS.'library');
   create_dir(getcwd().DS.'library'.DS.'vendor');

   create_dir(getcwd().DS.'logs');
        chmod(getcwd().DS.'logs', 0777);

   create_dir(getcwd().DS.'models');

   create_dir(getcwd().DS.'public');
        chmod(getcwd().DS.'public', 0777);

   create_dir(getcwd().DS.'public'.DS.'css');
        chmod(getcwd().DS.'public'.DS.'css', 0777);
  create_file(getcwd().DS.'public'.DS.'css'.DS.'all.css', "\n");
        chmod(getcwd().DS.'public'.DS.'css'.DS.'all.css', 0777);

   create_dir(getcwd().DS.'public'.DS.'img');

   create_dir(getcwd().DS.'public'.DS.'js');
        chmod(getcwd().DS.'public'.DS.'js', 0777);
  create_file(getcwd().DS.'public'.DS.'js'.DS.'all.js', "\n");
        chmod(getcwd().DS.'public'.DS.'js'.DS.'all.js', 0777);
    copy_file(getcwd().DS.'public'.DS.'js', $skel_dir.DS.'public'.DS.'jquery-1.5.1.min.js');
    copy_file(getcwd().DS.'public'.DS.'js', $skel_dir.DS.'public'.DS.'modernizr-2.0.6.min.js');

    copy_file(getcwd().DS.'public', $skel_dir.DS.'public'.DS.'.htaccess');
    copy_file(getcwd().DS.'public', $skel_dir.DS.'public'.DS.'index'.EXT);

     copy_dir(getcwd(), $skel_dir.DS.'tasks');

   create_dir(getcwd().DS.'tmp');
        chmod(getcwd().DS.'tmp', 0777);

   create_dir(getcwd().DS.'views');
   create_dir(getcwd().DS.'views'.DS.'assets');

   create_dir(getcwd().DS.'views'.DS.'assets'.DS.'css');
  create_file(getcwd().DS.'views'.DS.'assets'.DS.'css'.DS.'app.css', "/**\n *= base\n */");
    copy_file(getcwd().DS.'views'.DS.'assets'.DS.'css', $skel_dir.DS.'base.css');

   create_dir(getcwd().DS.'views'.DS.'assets'.DS.'js');
  create_file(getcwd().DS.'views'.DS.'assets'.DS.'js'.DS.'app.js', "/**\n *= lib/jquery-ujs\n */\n");
   create_dir(getcwd().DS.'views'.DS.'assets'.DS.'js'.DS.'lib');
    copy_file(getcwd().DS.'views'.DS.'assets'.DS.'js'.DS.'lib', $skel_dir.DS.'jquery-ujs.js');

   create_dir(getcwd().DS.'views'.DS.'errors');
    copy_file(getcwd().DS.'views'.DS.'errors', $skel_dir.DS.'errors'.DS.'404.html'.EXT);
    copy_file(getcwd().DS.'views'.DS.'errors', $skel_dir.DS.'errors'.DS.'500.html'.EXT);

   create_dir(getcwd().DS.'views'.DS.'layouts');
    copy_file(getcwd().DS.'views'.DS.'layouts', $skel_dir.DS.'views'.DS.'default.html'.EXT);

   create_dir(getcwd().DS.'views'.DS.'home');
    copy_file(getcwd().DS.'views'.DS.'home', $skel_dir.DS.'views'.DS.'index.html'.EXT);

    copy_file(getcwd(), $skel_dir.DS.'Stubfile');
    copy_file(getcwd(), $skel_dir.DS.'config'.EXT);
    copy_file(getcwd(), $skel_dir.DS.'initialize'.EXT);
    copy_file(getcwd(), $skel_dir.DS.'routes'.EXT);

/* EOF: ./library/application/scripts/create_application.php */
