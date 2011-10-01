<?php

/**
 * Skeleton
 */

$skel_dir = dirname(__DIR__).DS.'assets';

cli::flag('force') && status('force');

   create_dir(CWD.DS.'app');
    copy_file(CWD.DS.'app', $skel_dir.DS.'helpers'.EXT);
    copy_file(CWD.DS.'app', $skel_dir.DS.'routes'.EXT);

   create_dir(CWD.DS.'app'.DS.'assets');
   create_dir(CWD.DS.'app'.DS.'assets'.DS.'javascripts');
   create_dir(CWD.DS.'app'.DS.'assets'.DS.'javascripts'.DS.'lib');
    copy_file(CWD.DS.'app'.DS.'assets'.DS.'javascripts'.DS.'lib', $skel_dir.DS.'jquery-ujs.js');
   create_dir(CWD.DS.'app'.DS.'assets'.DS.'stylesheets');

   create_dir(CWD.DS.'app'.DS.'controllers');
    copy_file(CWD.DS.'app'.DS.'controllers', $skel_dir.DS.'home'.EXT);

   create_dir(CWD.DS.'app'.DS.'models');

   create_dir(CWD.DS.'app'.DS.'views');
   create_dir(CWD.DS.'app'.DS.'views'.DS.'errors');
    copy_file(CWD.DS.'app'.DS.'views'.DS.'errors', $skel_dir.DS.'errors'.DS.'404.html'.EXT);
    copy_file(CWD.DS.'app'.DS.'views'.DS.'errors', $skel_dir.DS.'errors'.DS.'500.html'.EXT);

   create_dir(CWD.DS.'app'.DS.'views'.DS.'layouts');
    copy_file(CWD.DS.'app'.DS.'views'.DS.'layouts', $skel_dir.DS.'views'.DS.'default.html'.EXT);

   create_dir(CWD.DS.'app'.DS.'views'.DS.'scripts');
   create_dir(CWD.DS.'app'.DS.'views'.DS.'scripts'.DS.'home');
    copy_file(CWD.DS.'app'.DS.'views'.DS.'scripts'.DS.'home', $skel_dir.DS.'views'.DS.'index'.EXT);

   create_dir(CWD.DS.'app'.DS.'views'.DS.'styles');
  create_file(CWD.DS.'app'.DS.'views'.DS.'styles'.DS.'home.css');

   create_dir(CWD.DS.'config');
  create_file(CWD.DS.'config'.DS.'application'.EXT);
  create_file(CWD.DS.'config'.DS.'database'.EXT);

   create_dir(CWD.DS.'config'.DS.'environments');
  create_file(CWD.DS.'config'.DS.'environments'.DS.'development'.EXT);
  create_file(CWD.DS.'config'.DS.'environments'.DS.'production'.EXT);
  create_file(CWD.DS.'config'.DS.'environments'.DS.'testing'.EXT);

   create_dir(CWD.DS.'db');
  create_file(CWD.DS.'db'.DS.'db.sqlite');
   create_dir(CWD.DS.'db'.DS.'backup');
   create_dir(CWD.DS.'db'.DS.'migrate');

   create_dir(CWD.DS.'tasks');
   create_dir(CWD.DS.'tasks'.DS.'rsync');
     copy_dir(CWD.DS.'tasks'.DS.'rsync', $skel_dir.DS.'rsync');

   create_dir(CWD.DS.'lib');

   create_dir(CWD.DS.'public');
   create_dir(CWD.DS.'public'.DS.'js');
    copy_file(CWD.DS.'public'.DS.'js', $skel_dir.DS.'public'.DS.'jquery-1.5.1.min.js');
    copy_file(CWD.DS.'public'.DS.'js', $skel_dir.DS.'public'.DS.'modernizr-1.7.min.js');
    copy_file(CWD.DS.'public', $skel_dir.DS.'public'.DS.'.htaccess');
    copy_file(CWD.DS.'public', $skel_dir.DS.'public'.DS.'index'.EXT);

    copy_file(CWD, $skel_dir.DS.'initialize'.EXT);

/* EOF: ./cli/mods/app/scripts/create_app.php */
