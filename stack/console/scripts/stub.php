<?php

info('Copying entire library');

$tetl_path  = is_dir(CWD.DS.'lib') ? CWD.DS.'lib' : CWD;
$tetl_path .= DS.'tetlphp';

! is_dir($tetl_path) && mkpath($tetl_path);


$framework = LIB;
$library   = dirname(LIB).DS.'library';
$stack     = dirname(LIB).DS.'stack';


// TODO: allow to customize the stub-installation

foreach (array('framework', 'library', 'stack') as $path) {
  success("Copying /$path into $tetl_path");
  cpfiles($$path, $tetl_path.DS.$path);
}

bold('Done');
