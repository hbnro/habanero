<?php

$source_dir = path($target_dir, 'views', 'pages');
$assets_dir = path($target_dir, 'assets');
$output_dir = path($target_dir, 'static');


if ( ! arg('c create') && ! is_dir($source_dir)) {
  return error("\n  No source directory especified\n");
}


$config_file = path($target_dir, 'config.php');
is_file($config_file) && config($config_file);

$base_url = option('base_url');


// routing
\Broil\Config::set('rewrite', TRUE);
\Broil\Config::set('server_base', $base_url);
\Broil\Config::set('tld_size', substr_count($base_url, '.'));


// assets
\Tailor\Config::set('fonts_url', "$base_url/font");
\Tailor\Config::set('images_url', "$base_url/img");
\Tailor\Config::set('styles_url', "$base_url/css");
\Tailor\Config::set('scripts_url', "$base_url/js");


// templating
\Tailor\Config::set('cache_dir', TMP);
\Tailor\Config::set('views_dir', path($target_dir, 'views'));
\Tailor\Config::set('fonts_dir', path($assets_dir, 'font'));
\Tailor\Config::set('images_dir', path($assets_dir, 'img'));
\Tailor\Config::set('styles_dir', path($assets_dir, 'css'));
\Tailor\Config::set('scripts_dir', path($assets_dir, 'js'));


if (arg('b build')) {
  say("\n  \cwhite,blue(**COMPILING**)\c");
} else {
  say("\n  \cwhite,magenta(**WATCHING**)\c\n  Press \bwhite(CTRL+C)\b to exit");
}

say("\n  *From*: $source_dir\n    *To*: $output_dir\n");


$cache_file = path($target_dir, '.cache');

$cache = explode("\n", read($cache_file)) ?: array();
$timeout = (int) arg('t to') ?: 3;

if ($recreate) {
  require path(__DIR__, 'create_skeleton.php');
} elseif (arg('r reset')) {
  $cache = array();

  \IO\Dir::unfile($output_dir, '*', TRUE);
  is_dir($output_dir) OR mkdir($output_dir, 0755, TRUE);

  status('reset');
}


if (arg('b build')) {
  $a = microtime(TRUE);
  $cache = array();
  $timeout = 0;

  require path(__DIR__, 'do_compile.php');

  $diff = round(microtime(TRUE) - $a, 4);
  say("\n  *Done*: {$diff}s\n");
} else {
  \Sauce\Shell\CLI::main(function ()
    use(&$cache, $target_dir, $assets_dir, $source_dir, $output_dir, $timeout) {
      require path(__DIR__, 'do_compile.php');
    });
}
