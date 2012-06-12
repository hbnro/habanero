<?php

info(ln('copying_libraries'));

$tetl_path  = APP_PATH.DS.'tetlphp';

is_dir($tetl_path) && unfile($tetl_path, '*', DIR_RECURSIVE | DIR_EMPTY);
mkpath($tetl_path);


$stub_file = APP_PATH.DS.'Stubfile';

$libs = array();
$base = dirname(LIB);
$stub = read($stub_file);

preg_match_all('/\s*-\s*(\S+)/m', $stub, $matches);

! empty($matches[1]) && $libs = $matches[1];

// TODO: allow + to copy full paths? i.e. + stack/console
success(ln('copying_stub_path', array('name' => 'framework', 'path' => str_replace(APP_PATH, '.', $tetl_path))));
cpfiles(LIB, $tetl_path.DS.'framework', '*', TRUE);

$stub_path = $tetl_path.DS.'library';
$path      = dirname(LIB).DS.'library';

foreach ($libs as $one) {
  $old = "$path/$one";

  ! is_dir($old) && $old .= EXT;

  $import = str_replace($path.DS, '', $old);

  success(ln('copying_stub_path', array('name' => extn($import), 'path' => str_replace(APP_PATH, '.', $stub_path))));
  is_dir($old) ? cpfiles($old, $stub_path.DS.$import, '*', TRUE) : copy($old, mkpath($stub_path).DS.$import);
}


// compactize!
if ( !! `php -v`) {
  $test = dir2arr($tetl_path, '*'.EXT, DIR_RECURSIVE | DIR_MAP);
  $test = array_filter($test, 'is_file');

  if ( ! cli::flag('no-compress')) {
    notice(ln('compressing_files'));

    foreach ($test as $one) {
      $in_file = escapeshellarg($one);
      $output  = `php -w $in_file`;
      write($one, "$output\n");
    }
  }
}

done();

/* EOF: ./stack/scripts/stub.php */
