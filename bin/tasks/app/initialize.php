<?php

require path(__DIR__, 'functions.php');

task('app:new', 'Create the application skeleton', function ($params) {
  require path(__DIR__, 'scripts', 'check_skeleton.php');
});


0 && task('app:make', 'Generate actions and controllers', function ($params) {
  require path(__DIR__, 'scripts', 'check_actions.php');
});


task('app:config', 'Check and configure the application', function ($params) {
  $flags = arg('global', 'app', 'dev', 'prod', 'current');

  if ( ! $flags OR ! $params OR isset($params['help'])) {
    $message = <<<INFO

  \bcyan(Available options:)\b

  --app      \clight_gray(# desc)\c
  --dev      \clight_gray(# desc)\c
  --prod     \clight_gray(# desc)\c
  --global   \clight_gray(# desc)\c
  --current  \clight_gray(# desc)\c

INFO;

    writeln(colorize($message));
  } else {
    require path(__DIR__, 'scripts', 'check_config.php');
  }
});


0 && task('db:model', 'Define models for the application', function ($params) {
  require path(__DIR__, 'scripts', 'create_model.php');
});


task('db:console', 'Interactive model inspection like REPL', function ($params) {
  require path(__DIR__, 'scripts', 'load_console.php');
});


0 && task('db:migrate', 'Perform migrations on production environments', function ($params) {
  require path(__DIR__, 'scripts', 'reload_models.php');
});


task('assets:clean', 'Remove pre-compiled assets from cache', function ($params) {
  require path(__DIR__, 'scripts', 'assets_clean.php');
});


task('assets:precompile', 'Prepare the application assets for production', function ($params) {
  $flags = arg('v', 'i', 'c', 'j', 'views', 'images', 'styles', 'scripts');

  if ( ! $flags OR ! $params OR isset($params['help'])) {
    $message = <<<INFO

  \bcyan(Available options:)\b

  -v, --views    \clight_gray(# Prepare views)\c
  -i, --images   \clight_gray(# Prepare images)\c
  -c, --styles   \clight_gray(# Prepare styles)\c
  -j, --scripts  \clight_gray(# Prepare scripts)\c

INFO;

    writeln(colorize($message));
  } else {
    require path(__DIR__, 'scripts', 'assets_precompile.php');
  }
});
