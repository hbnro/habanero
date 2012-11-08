<?php

info("\n  Empty assets cache:\n");

\IO\Dir::unfile(path(APP_PATH, 'cache'), '*', TRUE);

mkdir(path(APP_PATH, 'cache'), 0777);


if (s3_handle()) {
  s3_clean_bucket();
} else {
  foreach (array('img', 'css', 'js') as $type) {
    success("  Removing files from 'static/$type'");
    $files_dir = path(APP_PATH, 'static', $type);
    \IO\Dir::unfile($files_dir, '*', TRUE);
    mkdir($files_dir, 0777);
  }
}

success("  Reset file 'config/resources.php'\n");
$res_file = path(APP_PATH, 'config', 'resources.php');
write($res_file, "<?php return array();\n");
