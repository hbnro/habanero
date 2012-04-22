<?php

$type      = params('type');
$sheet     = params('name');

$base_path = APP_PATH.DS.'views'.DS.'assets';
$base_file = $base_path.DS.$type.DS."$sheet.$type";

$out_file  = APP_PATH.DS.'static'.DS.$type.DS."$sheet.$type";

#die($out_file);
// TODO: compression, caching, gzip?

if (APP_ENV <> 'development') {
  $out_file = str_replace("$sheet.$type", "$sheet.min.$type", $out_file);

  if ( ! is_file($out_file)) {
    die(ln('file_not_exists', array('name' => str_replace(APP_PATH.DS, '', $out_file))));
  }
}

response(read($out_file), array(
  'type' => mime($type),
));

/* EOF: ./library/application/scripts/serving.php */
