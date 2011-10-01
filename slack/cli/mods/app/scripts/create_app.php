<?php

$skel_dir = dirname(__DIR__).DS.'assets';

create_dir(CWD.DS.'app');
create_file(CWD.DS.'app'.DS.'helpers'.EXT, "<?php\n");
create_file(CWD.DS.'app'.DS.'routes'.EXT, "<?php\n\nroot('home#index', array('path' => 'home'));");

create_dir(CWD.DS.'app'.DS.'assets');
create_dir(CWD.DS.'app'.DS.'assets'.DS.'javascripts');
create_dir(CWD.DS.'app'.DS.'assets'.DS.'javascripts'.DS.'lib');
create_dir(CWD.DS.'app'.DS.'assets'.DS.'stylesheets');

create_dir(CWD.DS.'app'.DS.'controllers');
create_file(CWD.DS.'app'.DS.'controllers'.DS.'home'.EXT, "<?php\n\nclass home_controller extends controller\n{\n\n  public static function index()\n  {\n  }\n\n}\n");

create_dir(CWD.DS.'app'.DS.'models');

create_dir(CWD.DS.'app'.DS.'views');
create_dir(CWD.DS.'app'.DS.'views'.DS.'errors');
create_file(CWD.DS.'app'.DS.'views'.DS.'errors'.DS.'404.html'.EXT, "<h1>Error 404</h1>\n");
create_file(CWD.DS.'app'.DS.'views'.DS.'errors'.DS.'500.html'.EXT, "<h1>Error 500</h1>\n");

create_dir(CWD.DS.'app'.DS.'views'.DS.'layouts');
copy_file(CWD.DS.'app'.DS.'views'.DS.'layouts', $skel_dir.DS.'default.html'.EXT);

create_dir(CWD.DS.'app'.DS.'views'.DS.'scripts');
create_dir(CWD.DS.'app'.DS.'views'.DS.'scripts'.DS.'home');
create_file(CWD.DS.'app'.DS.'views'.DS.'scripts'.DS.'home'.DS.'index'.EXT, "<h1>home#index.view</h1>\n<p><?php echo __FILE__; ?><br>\n<?php echo ticks(BEGIN), 's'; ?></p>\n");

create_dir(CWD.DS.'app'.DS.'views'.DS.'styles');
create_file(CWD.DS.'app'.DS.'views'.DS.'styles'.DS.'home.css');

create_dir(CWD.DS.'config');
create_file(CWD.DS.'config'.DS.'application'.EXT, "<?php\n\n\$config['environment'] = 'development';\n");
create_file(CWD.DS.'config'.DS.'database'.EXT, "<?php\n\n\$config['dsn'] = 'sqlite:'.dirname(__DIR__).DS.'db'.DS.'db.sqlite';\n");

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
copy_file(CWD.DS.'tasks'.DS.'rsync', $skel_dir.DS.'rsync'.EXT);
create_file(CWD.DS.'tasks'.DS.'rsync'.DS.'exclude.txt', "tasks\n");

create_dir(CWD.DS.'lib');

create_dir(CWD.DS.'public'.DS.'js');
copy_file(CWD.DS.'public'.DS.'js', $skel_dir.DS.'jquery-1.5.1.min.js');
copy_file(CWD.DS.'public'.DS.'js', $skel_dir.DS.'modernizr-1.7.min.js');

create_file(CWD.DS.'index'.EXT, "<?php\n\nrequire dirname(__DIR__).'/initialize.php';\n");
copy_file(CWD, $skel_dir.DS.'initialize'.EXT);
