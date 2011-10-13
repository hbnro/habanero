<?php

/**
 * Skeleton
 */

$skel_dir = dirname(__DIR__).DS.'assets';

cli::flag('force') && status('force');

   create_dir(CWD.DS.'app');
  create_file(CWD.DS.'app'.DS.'helpers'.EXT, "<?php\n");
    copy_file(CWD.DS.'app', $skel_dir.DS.'routes'.EXT);

   create_dir(CWD.DS.'app'.DS.'controllers');
    copy_file(CWD.DS.'app'.DS.'controllers', $skel_dir.DS.'home'.EXT);

   create_dir(CWD.DS.'app'.DS.'models');

   create_dir(CWD.DS.'app'.DS.'views');

   create_dir(CWD.DS.'app'.DS.'views'.DS.'assets');

   create_dir(CWD.DS.'app'.DS.'views'.DS.'assets'.DS.'css');
  create_file(CWD.DS.'app'.DS.'views'.DS.'assets'.DS.'css'.DS.'app.css', "/**\n *= base\n */");
  create_file(CWD.DS.'app'.DS.'views'.DS.'assets'.DS.'css'.DS.'base.css', "@use 'reset'\n");

   create_dir(CWD.DS.'app'.DS.'views'.DS.'assets'.DS.'js');
  create_file(CWD.DS.'app'.DS.'views'.DS.'assets'.DS.'js'.DS.'app.js', "/**\n *= lib/jquery-ujs\n */\n");
   create_dir(CWD.DS.'app'.DS.'views'.DS.'assets'.DS.'js'.DS.'lib');
    copy_file(CWD.DS.'app'.DS.'views'.DS.'assets'.DS.'js'.DS.'lib', $skel_dir.DS.'jquery-ujs.js');

   create_dir(CWD.DS.'app'.DS.'views'.DS.'errors');
    copy_file(CWD.DS.'app'.DS.'views'.DS.'errors', $skel_dir.DS.'errors'.DS.'404.html'.EXT);
    copy_file(CWD.DS.'app'.DS.'views'.DS.'errors', $skel_dir.DS.'errors'.DS.'500.html'.EXT);

   create_dir(CWD.DS.'app'.DS.'views'.DS.'layouts');
    copy_file(CWD.DS.'app'.DS.'views'.DS.'layouts', $skel_dir.DS.'views'.DS.'default.html'.EXT);

   create_dir(CWD.DS.'app'.DS.'views'.DS.'home');
    copy_file(CWD.DS.'app'.DS.'views'.DS.'home', $skel_dir.DS.'views'.DS.'index'.EXT);

   create_dir(CWD.DS.'config');
    copy_file(CWD.DS.'config', $skel_dir.DS.'application'.EXT);

   create_dir(CWD.DS.'config'.DS.'environments');
  create_file(CWD.DS.'config'.DS.'environments'.DS.'development'.EXT);
  create_file(CWD.DS.'config'.DS.'environments'.DS.'production'.EXT);
  create_file(CWD.DS.'config'.DS.'environments'.DS.'testing'.EXT);

   create_dir(CWD.DS.'db');
  create_file(CWD.DS.'db'.DS.'db.sqlite');
  create_file(CWD.DS.'db'.DS.'schema'.EXT, "<?php\n");
  create_file(CWD.DS.'db'.DS.'seeds'.EXT, "<?php\n");
   create_dir(CWD.DS.'db'.DS.'backup');
   create_dir(CWD.DS.'db'.DS.'migrate');

   create_dir(CWD.DS.'lib');

   create_dir(CWD.DS.'public');
   create_dir(CWD.DS.'public'.DS.'js');
    copy_file(CWD.DS.'public'.DS.'js', $skel_dir.DS.'public'.DS.'jquery-1.5.1.min.js');
    copy_file(CWD.DS.'public'.DS.'js', $skel_dir.DS.'public'.DS.'modernizr-1.7.min.js');
    copy_file(CWD.DS.'public', $skel_dir.DS.'public'.DS.'.htaccess');
    copy_file(CWD.DS.'public', $skel_dir.DS.'public'.DS.'index'.EXT);

     copy_dir(CWD, $skel_dir.DS.'tasks');
    copy_file(CWD, $skel_dir.DS.'initialize'.EXT);

/* EOF: ./stack/console/mods/app/scripts/create_app.php */
