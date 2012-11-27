<?php

if (arg('v i c j views images styles scripts')) {
  $set = array(
    'img' => arg('i images'),
    'css' => arg('c styles'),
    'js' => arg('j scripts'),
  );

  if (s3_handle()) {
    s3_clean_bucket($set);
  } else {
    foreach ($set as $type => $ok) {
      if ($ok) {
        status('remove', "static/$type");
        $files_dir = path(APP_PATH, 'static', $type);
        \IO\Dir::unfile($files_dir, '*', TRUE);
        mkdir($files_dir, 0777);
      }
    }
  }

  if (arg('v views')) {
    status('remove', 'cache');
    \IO\Dir::unfile(path(APP_PATH, 'cache'), '*', TRUE);
    mkdir(path(APP_PATH, 'cache'), 0777);
  }

  $res_file = path(APP_PATH, 'config', 'resources.php');
  write($res_file, "<?php return array();\n");
  status('update', 'config/resources.php');
} else {
  error("\n  Nothing to do\n");
}
