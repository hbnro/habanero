<?php

info(ln('app.verifying_installation'));

$test  = array_filter(dir2arr(APP_PATH, '*', DIR_RECURSIVE), 'is_file');
$count = sizeof($test);
$size  = 0;

foreach ($test as $file) {
  $size += filesize($file);
}

success(ln('app.counting_files', array('length' => number_format($count))));
success(ln('app.sizing_files', array('size' => fmtsize($size))));

done();

/* EOF: ./stack/scripts/application/scripts/app_status.php */
