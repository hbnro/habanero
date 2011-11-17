<?php

// filters
assets::compile('css', function ($file) {
  return partial::render($file);
});

assets::compile('js', function ($file) {
  return partial::render($file);
});


$type      = params('type');

$base_path = getcwd().DS.'views'.DS.'assets';
$base_file = $base_path.DS.$type.DS."app.$type";

$out_file  = getcwd().DS.'public'.DS.$type.DS."all.$type";


// TODO: compression, caching, gzip?
switch (option('environment')) {
  case 'production';
    $out_file = str_replace("all.$type", "all.min.$type", $out_file);

    if ( ! is_file($out_file)) {
      die(ln('file_not_exists', array('name' => str_replace(getcwd().DS, '', $out_file))));
    }
  break;
  default;
    $test = preg_replace_callback('/\s+\*=\s+(\S+)/m', function ($match)
      use($base_path, $type) {
      assets::append($base_path.DS.$type.DS."$match[1].$type");
    }, read($base_file));

    $test = preg_replace('/\/\*[*\s]*?\*\//s', '', $test);

    write($out_file, assets::$type($test));
  break;
}

redirect(path_to($type.DS.basename($out_file)));

/* EOF: ./library/application/scripts/serving.php */
