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

task('model', 'Define models for the application', function ($params) {
  if (arg('help')) {
    $message = <<<INFO

  # Usage:
    *model* <name> <field:type> <?> ...

  Example:
    {@} *model* {user} {email:string} {uid:string}
    {@} *model* {profile} {name:string} {address:string} --stamp -x mongodb
    {@} *model* {response} {body:text} {from_uid:string} {to_uid:string} -t -n comments -i to_uid

  ## Available options:

  -f, [--force]           # Rewrite model file
  -n, [--table=NAME]      # Custom table +NAME+ for the class
  -x, [--extends=CLASS]   # Choose +database+, +mongodb+ or custom +CLASS+
  -c, [--connection=ID]   # The connection +ID+ to use
  -i, [--indexes=FIELDS]  # Comma separated +FIELDS+ to index
  -t, [--stamp]           # Fields for +created_at+ and +updated_at+

INFO;

    say($message);
  } else {
    require path(__DIR__, 'scripts', 'create_model.php');
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


task('console', 'Interactive command line', function ($params) {
  if (arg('help')) {
    $message = <<<INFO

  # Usage:
    *console* ...

  Example:
    {@} *console* -s

  ## Available options:

    -s, [--run]  # Interactive command line

INFO;

    say($message);
  } else {
    require path(__DIR__, 'scripts', 'load_console.php');
  }
});


task('hydrate', 'Model reloading for migrations', function ($params) {
  if (arg('help')) {
    $message = <<<INFO

  # Usage:
    *hydrate* <path> ...

  Example:
    {@} *hydrate* {app/models}
    {@} *hydrate* {library} -R

  ## Available options:

  -R, [--recursive]   # Search for models recursively

INFO;

    say($message);
  } else {
    require path(__DIR__, 'scripts', 'reload_models.php');
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

  -v, [--views]    # Include views
  -i, [--images]   # Include images
  -c, [--styles]   # Include styles
  -j, [--scripts]  # Include scripts

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

  -v, [--views]    # Remove views
  -i, [--images]   # Remove images
  -c, [--styles]   # Remove styles
  -j, [--scripts]  # Remove scripts

INFO;

    say($message);
  } else {
    require path(__DIR__, 'scripts', 'assets_clean.php');
  }
});
