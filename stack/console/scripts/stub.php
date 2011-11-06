<?php

info(ln('copying_libraries'));

$tetl_path  = CWD.DS.'tetlphp';

is_dir($tetl_path) && unfile($tetl_path, '*', DIR_RECURSIVE | DIR_EMPTY);
mkpath($tetl_path);


$framework = LIB;
$library   = dirname(LIB).DS.'library';
$stack     = dirname(LIB).DS.'stack';


// TODO: allow to customize the stub-installation

foreach (array('framework', 'library', 'stack') as $path) {
  success(ln('copying_stub_path', array('name' => $path, 'path' => $tetl_path)));
  cpfiles($$path, $tetl_path.DS.$path);
}

done();

/* EOF: ./stack/console/scripts/stub.php */
