<?php

info(ln('app.verifying_installation'));

$test  = dir2arr(getcwd(), '*', DIR_RECURSIVE | DIR_MAP);
$count = sizeof($test);
$size  = 0;

foreach ($test as $file) {
  $size += filesize($file);
}

success(ln('app.counting_files', array('length' => number_format($count))));
success(ln('app.sizing_files', array('size' => fmtsize($size))));

done();

/* EOF: ./library/application/scripts/app_status.php */
