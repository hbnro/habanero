<?php

if (arg('v f i c j views fonts images styles scripts')) {
  $set = array(
    'font' => arg('f fonts'),
    'img' => arg('i images'),
    'css' => arg('c styles'),
    'js' => arg('j scripts'),
  );

  if (s3_handle()) {
    s3_clean_bucket($set);
  } else {
    foreach ($set as $type => $ok) {
      $files_dir = path(APP_PATH, 'static', $type);

      if (is_dir($files_dir) && $ok) {
        status('remove', "static/$type");
        \IO\Dir::unfile($files_dir, '*', TRUE);
      }

      is_dir($files_dir) OR mkdir($files_dir, 0777);
    }
  }

  if (arg('v views')) {
    status('remove', 'cache');

    $cache_dir = path(APP_PATH, 'cache');

    is_dir($cache_dir) && \IO\Dir::unfile($cache_dir, '*', TRUE);
    is_dir($cache_dir) OR mkdir($cache_dir, 0777);
  }


  // TODO: unset from cache?

  $res_file = path(APP_PATH, 'config', 'resources.php');
  write($res_file, "<?php return array();\n");
  status('update', 'config/resources.php');
} else {
  error("\n  Nothing to do\n");
}
