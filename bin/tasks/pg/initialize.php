<?php

require path(__DIR__, 'functions.php');

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

