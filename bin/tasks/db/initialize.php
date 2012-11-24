<?php

require path(__DIR__, 'functions.php');

task('model', 'Build models for the application', function ($params) {
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
