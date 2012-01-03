<?php

info(ln('copying_libraries'));

$tetl_path  = APP_PATH.DS.'tetlphp';

is_dir($tetl_path) && unfile($tetl_path, '*', DIR_RECURSIVE | DIR_EMPTY);
mkpath($tetl_path);


$libs = array();
$base = dirname(LIB);
$stub = read(APP_PATH.DS.'Stubfile');

preg_match_all('/\s*-\s*(\S+)/m', $stub, $matches);

! empty($matches[1]) && $libs = $matches[1];

// TODO: allow + to copy full paths? i.e. + stack/console
success(ln('copying_stub_path', array('name' => 'framework', 'path' => $tetl_path)));
cpfiles(LIB, $tetl_path.DS.'framework', '*', TRUE);

$stub_path = $tetl_path.DS.'library';

foreach ((array) option('import_path', array()) as $path) {
  if ($test = dir2arr($path, '*', DIR_RECURSIVE | DIR_MAP)) {
    foreach ($test as $one) {
      $import = str_replace($path.DS, '', $one);

      if (in_array(extn($import), $libs)) {
        success(ln('copying_stub_path', array('name' => $import, 'path' => $stub_path)));
        is_dir($one) ? cpfiles($one, $stub_path.DS.$import, '*', TRUE) : copy($one, mkpath($stub_path).DS.$import);
      }
    }
  }
}

done();

/* EOF: ./stack/scripts/stub.php */
