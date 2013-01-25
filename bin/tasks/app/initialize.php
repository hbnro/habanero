<?php

require path(__DIR__, 'functions.php');

task('new', 'The application skeleton', function ($params) {
  if (arg('help')) {
    $message = <<<INFO

  # Usage:
    *new* <name> ...

  Example:
    {@} *new* {my-app}
    {@} *new* {sandbox} -fD

  ## Available options:

  -f, [--force]       # Rewrite all skeleton files
  -D, [--delete-all]  # Remove all files before create

INFO;

    say($message);
  } else {
    require path(__DIR__, 'scripts', 'check_skeleton.php');
  }
});


task('do', 'Actions and controllers', function ($params) {
  if (arg('help')) {
    $message = <<<INFO

  # Usage:
    *do* <controller>[<:action>] ...

  Example:
    {@} *do* {profile} -VA
    {@} *do* {profile:show} --path=my_profile
    {@} *do* {admin:dashbord} -p admin -r /backend -m panel

  ## Available options:

  -V, [--no-view]      # Skip view creation
  -A, [--no-action]    # Skip method creation
  -m, [--method=NAME]  # User custom method +NAME+
  -r, [--route=EXPR]   # Configure custom route +EXPR+
  -p, [--path=NAME]    # Custom path +NAME+ for solving

INFO;

    say($message);
  } else {
    require path(__DIR__, 'scripts', 'check_actions.php');
  }
});


task('routes', 'Display all defined routes', function ($params) {
  if (arg('help')) {
    $message = <<<INFO

  # Usage:
    *routes* ...

  Example:
    {@} *routes* -l
    {@} *routes* -gu
    {@} *routes* --delete

  ## Available options:

  -l, [--list]    # Show all routes
  -g, [--get]     # Include +GET+ routes
  -u, [--put]     # Include +PUT+ routes
  -p, [--post]    # Include +POST+ routes
  -d, [--delete]  # Include +DELETE+ routes

INFO;

    say($message);
  } else {
    require path(__DIR__, 'scripts', 'check_routes.php');
  }
});


task('config', 'Configuration tool', function ($params) {
  if (arg('help')) {
    $message = <<<INFO

  # Usage:
    config --type [--key=value] ...

  Example:
    {@} *config* -d --database.mongo ""
    {@} *config* --app --rewrite --expires=30
    {@} *config* --global --database.default=sqlite:database/sqlite.db

  ## Available options:

  -a, [--app]     # Show/update application.php
  -d, [--dev]     # Show/update development.php
  -p, [--prod]    # Show/update production.php
  -g, [--global]  # Show/update config.php

INFO;

    say($message);
  } else {
    require path(__DIR__, 'scripts', 'check_config.php');
  }
});


task('prepare', 'Precompile assets for production', function ($params) {
  if (arg('help')) {
    $message = <<<INFO

  # Usage:
    *prepare* ...

  Example:
    {@} *prepare* -cj
    {@} *prepare* --views

  ## Available options:

  -a, [--all]       # All things
  -f, [--fonts]     # Font files
  -v, [--views]     # Views, layouts
  -i, [--images]    # Static images
  -c, [--styles]    # Stylesheets
  -j, [--scripts]   # Javascript

INFO;

    say($message);
  } else {
    require path(__DIR__, 'scripts', 'assets_precompile.php');
  }
});

task('purge', 'Remove assets from cache', function ($params) {
  if (arg('help')) {
    $message = <<<INFO

  # Usage:
    *purge* ...

  Example:
    {@} *purge* -iv
    {@} *purge* --scripts

  ## Available options:

  -a, [--all]       # All things
  -r, [--reset]     # Clear resources
  -f, [--fonts]     # Font files
  -v, [--views]     # Views, layouts
  -i, [--images]    # Static images
  -c, [--styles]    # Stylesheets
  -j, [--scripts]   # Javascript

INFO;

    say($message);
  } else {
    require path(__DIR__, 'scripts', 'assets_clean.php');
  }
});


task('watch', 'Static pages generator', function ($params) {
  if (arg('help')) {
    $message = <<<INFO

  # Usage:
    *watch* <path> ...

  Example:
    {@} *watch* {my-site}
    {@} *watch* {~/sandbox} -cb
    {@} *watch* {/path/to/example} --timeout 10

  ## Available options:

  -c, [--create]   # Create new project
  -b, [--build]    # Compile instead of watch
  -r, [--reset]    # Clear everything from output
  -t, [--to=SECS]  # Refresh timeout (default is 3 +SECS+)

INFO;

    say($message);
  } else {
    require path(__DIR__, 'scripts', 'check_watchdog.php');
  }
});
