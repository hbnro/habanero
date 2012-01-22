<?php

$type      = params('type');
$sheet     = request::get('src', 'app');

$base_path = APP_PATH.DS.'views'.DS.'assets';
$base_file = $base_path.DS.$type.DS."$sheet.$type";

$out_file  = APP_PATH.DS.'static'.DS.$type.DS."$sheet.$type";


// TODO: compression, caching, gzip?
switch (APP_ENV) {
  case 'production';
    $out_file = str_replace("$sheet.$type", "$sheet.min.$type", $out_file);

    if ( ! is_file($out_file)) {
      die(ln('file_not_exists', array('name' => str_replace(APP_PATH.DS, '', $out_file))));
    }
  break;
  default;
    // filters
    assets::compile('php', function ($file) {
      return partial::render($file);
    });

    // images
    $img_path   = APP_PATH.DS.'views'.DS.'assets'.DS.'img';
    $static_dir = APP_PATH.DS.'static'.DS.'img';

    ! is_dir($static_dir) && mkpath($static_dir);

    unfile($static_dir, '*', DIR_RECURSIVE);

    if ($test = dir2arr($img_path, '*.(jpe?g|png|gif)', DIR_RECURSIVE | DIR_MAP)) {
      foreach ($test as $file) {
        if (is_dir($file)) {
          mkpath($static_dir.DS.basename($file));
        } else {
          $file_hash  = md5(md5_file($file) . filesize($file));
          $file_name  = str_replace($img_path.DS, '', extn($file)) . $file_hash . ext($file, TRUE);

          $static_img = $static_dir.DS.$file_name;

          ! is_file($static_img) && copy($file, $static_img);
        }
      }
    }

    if (class_exists('cssp')) {
      // default path
      cssp::config('path', APP_PATH.DS.'views'.DS.'assets'.DS.'css');
    }

    // css and js
    $test = preg_replace_callback('/\s+\*=\s+(\S+)/m', function ($match)
      use($base_path, $type) {
        $test_file = $base_path.DS.$type.DS."$match[1].$type";

        @list($path, $name) = array(dirname($test_file), basename($test_file));

        assets::append(findfile($path, $name, FALSE, 1), $type);
    }, read($base_file));

    $test = preg_replace('/\/\*[*\s]*?\*\//s', '', $test);

    write($out_file, assets::$type($test));
  break;
}

response(read($out_file), array(
  'type' => mime($type),
));

/* EOF: ./library/application/scripts/serving.php */
