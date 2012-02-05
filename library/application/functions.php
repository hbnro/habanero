<?php


function javascript_for($name) {
  assets::prepend(tag('script', array('src' => compile_assets($name, 'js'))), 'body');
}

function stylesheet_for($name) {
  assets::prepend(tag('link', array('rel' => 'stylesheet', 'href' => compile_assets($name, 'css'))), 'head');
}

function csrf_meta_tag() {
  echo tag('meta', array('name' => 'csrf-token', 'content' => TOKEN));
}

function compile_assets($from, $type) {
  $base_path = APP_PATH.DS.'views'.DS.'assets';
  $base_file = $base_path.DS.$type.DS."$from.$type";

  if (is_file($base_file)) {
    $test = read($base_file);

    if (preg_match('/\/\*([a-z0-9]{32})\*\//', $test, $match)) {
      $suffix   = APP_ENV === 'production' ? '.min' : '';
      $out_file = $out_path.DS.$from.$match[1].$suffix.".$type";
    } else {
      $tmp       = TMP.DS."$from.$type.tmp";
      $out_path  = APP_PATH.DS.'static'.DS.$type;


      // css and js
      $test = preg_replace_callback('/\s+\*=\s+(\S+)/m', function ($match)
        use($base_path, $type) {
          $test_file = $base_path.DS.$type.DS."$match[1].$type";

          @list($path, $name) = array(dirname($test_file), basename($test_file));

          assets::append(findfile($path, $name, FALSE, 1), $type);
      }, $test);

      $test = preg_replace('/\/\*[*\s]*?\*\//s', '', $test);

      write($tmp, assets::$type($test));

      $hash     = md5(md5_file($tmp) . filesize($tmp));
      $suffix   = APP_ENV === 'production' ? '.min' : '';
      $out_file = $out_path.DS.$from.$hash.$suffix.".$type";

      copy($tmp, $out_file);
      unlink($tmp);
    }
    return path_to($type.DS.basename($out_file));
  }
}

function compile_images() {
  $img_path   = APP_PATH.DS.'views'.DS.'assets'.DS.'img';
  $static_dir = APP_PATH.DS.'static'.DS.'img';

  ! is_dir($static_dir) && mkpath($static_dir);

  unfile($static_dir, '*', DIR_RECURSIVE);

  if ($test = dir2arr($img_path, '*.(jpe?g|png|gif)', DIR_RECURSIVE | DIR_MAP)) {
    foreach (array_filter($test, 'is_file') as $file) {
      $file_hash  = md5(md5_file($file) . filesize($file));
      $file_name  = str_replace($img_path.DS, '', extn($file)) . $file_hash . ext($file, TRUE);

      $static_img = $static_dir.DS.$file_name;

      ! is_dir(dirname($static_img)) && mkpath(dirname($static_img));
      ! is_file($static_img) && copy($file, $static_img);
    }
  }
}


