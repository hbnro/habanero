<?php

create_dir($target_dir);

$out = array();
$tmp_dir = path(dirname(__DIR__), 'assets');
$base_dir = path($tmp_dir, 'static');

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


copy_file(path($target_dir, 'assets', 'css'), path($tmp_dir, 'sauce.less'));
copy_file(path($target_dir, 'assets', 'css'), path($tmp_dir, 'base.less'));
copy_file(path($target_dir, 'assets', 'css'), path($tmp_dir, 'media.less'));
copy_file(path($target_dir, 'assets', 'css'), path($tmp_dir, 'styles.css.less'));

copy_file(path($target_dir, 'assets', 'js', 'lib'), path($tmp_dir, 'console.js'));
copy_file(path($target_dir, 'assets', 'js', 'lib'), path($tmp_dir, 'jquery.min.js'));
copy_file(path($target_dir, 'assets', 'js', 'lib'), path($tmp_dir, 'modernizr.min.js'));
copy_file(path($target_dir, 'assets', 'js'), path($tmp_dir, 'script.js.coffee'));

$name = camelcase(basename($target_dir));

create_file(path($target_dir, '.gitignore'), ".cache\nstatic/*");
create_file(path($target_dir, 'config.php'), '<' . "?php\n\n\$config['title'] = '$name';\n\$config['base_url'] = '';\n");
