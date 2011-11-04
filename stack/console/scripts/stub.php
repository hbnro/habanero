<?php

info('Copying entire library');

$tetl_path  = CWD.DS.'tetlphp';

is_dir($tetl_path) && unfile($tetl_path, '*', DIR_RECURSIVE | DIR_EMPTY);
mkpath($tetl_path);


$framework = LIB;
$library   = dirname(LIB).DS.'library';
$stack     = dirname(LIB).DS.'stack';


// TODO: allow to customize the stub-installation

foreach (array('framework', 'library', 'stack') as $path) {
  success("Copying /$path into $tetl_path");
  cpfiles($$path, $tetl_path.DS.$path);
}

bold('Done');
