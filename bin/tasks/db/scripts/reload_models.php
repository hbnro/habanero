<?php

$path = array_shift($params);

if (! $path) {
  error("\n  Missing model path\n");
} elseif (is_file($path)) {
  hydrate_model($path);
} else {
  $mod_path = path(APP_PATH, $path);

  if ( ! is_dir($mod_path)) {
    error("\n  Model path '$path' does not exists\n");
  } else {
    $crawl = function ($file) {
        hydrate_model($file);
      };

    if (arg('R recursive')) {
      \IO\Dir::each($mod_path, '*.php', $crawl);
    } else {
      \IO\Dir::open($mod_path, $crawl);
    }
  }
}
