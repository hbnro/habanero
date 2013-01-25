<?php

create_dir($target_dir);
create_dir(path($target_dir, 'cache'), 0777);

$out = array();
$base_dir = path(dirname(__DIR__), 'assets');

\IO\Dir::each($base_dir, '*', function ($file)
  use ($target_dir, $base_dir) {
    $new = str_replace($base_dir.DIRECTORY_SEPARATOR, '', $file);
    $out = path($target_dir, $new);

    if ( ! is_dir($file)) {
      $path = dirname($out);

      if ( ! is_dir($path)) {
        status('create', $path);
        mkdir($path, 0755, TRUE);
      }

      status('copy', $out);
      copy($file, $out);
    }
  });


$name = camelcase(basename($target_dir));

create_file(path($target_dir, '.gitignore'), ".cache\nstatic/*");
create_file(path($target_dir, 'config.php'), '<' . "?php\n\n\$config['title'] = '$name';\n\$config['base_url'] = '';\n");
