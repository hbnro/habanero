<?php

info(ln('copying_libraries'));

$tetl_path  = APP_PATH.DS.'tetlphp';

is_dir($tetl_path) && unfile($tetl_path, '*', DIR_RECURSIVE | DIR_EMPTY);
mkpath($tetl_path);


// auto-generation
$stub_file = APP_PATH.DS.'Stubfile';

if ( ! is_file($stub_file)) {
  $test = dir2arr(APP_PATH, '*'.EXT, DIR_RECURSIVE | DIR_MAP);
  $test = array_filter($test, 'is_file');

  $stub = array();

  foreach ($test as $file) {
    if (preg_match_all('/\bimport\s*\(([\'"])(\w+)\\1\)/', read($file), $matches)) {
      $stub += $matches[2];
    }
  }

  if (in_array('application', $stub)) {
    $stub []= 'server';
    $stub []= 'partial';
  }

  $text = join("\n- ", $stub);
  $text = "# dependencies\n- $text";

  write($stub_file, $text);
}


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

if ($test = dir2arr($path, '*', DIR_RECURSIVE | DIR_MAP)) {
  foreach ($test as $one) {
    $import = str_replace($path.DS, '', $one);

    if (in_array(extn($import), $libs)) {
      success(ln('copying_stub_path', array('name' => extn($import), 'path' => str_replace(APP_PATH, '.', $stub_path))));
      is_dir($one) ? cpfiles($one, $stub_path.DS.$import, '*', TRUE) : copy($one, mkpath($stub_path).DS.$import);
    }
  }
}

// compactize!
if ( !! `php -v`) {
  $test = dir2arr($tetl_path, '*'.EXT, DIR_RECURSIVE | DIR_MAP);
  $test = array_filter($test, 'is_file');

  cli::write('Compressing files...');

  foreach ($test as $one) {
    $in_file = escapeshellarg($one);
    $output  = `php -w $in_file`;
    write($one, "$output\n");
  }
  cli::writeln('OK');
}

done();

/* EOF: ./stack/scripts/stub.php */
