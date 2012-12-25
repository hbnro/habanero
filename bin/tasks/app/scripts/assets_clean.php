<?php

if (arg('r v f i c j a reset views fonts images styles scripts all')) {
  $set = array(
    'font' => arg('f fonts all'),
    'img' => arg('i images all'),
    'css' => arg('c styles all'),
    'js' => arg('j scripts all'),
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

  if (arg('v views all')) {
    status('remove', 'cache');

    $cache_dir = path(APP_PATH, 'cache');

    is_dir($cache_dir) && \IO\Dir::unfile($cache_dir, '*', TRUE);
    is_dir($cache_dir) OR mkdir($cache_dir, 0777);
  }


  // TODO: unset from cache?
  if (arg('r reset all')) {
    $res_file = path(APP_PATH, 'config', 'resources.php');
    write($res_file, "<?php return array();\n");
    status('update', 'config/resources.php');
  }
} else {
  error("\n  Nothing to do\n");
}
