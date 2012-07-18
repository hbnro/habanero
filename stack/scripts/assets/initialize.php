<?php

i18n::load_path(__DIR__.DS.'locale', 'assets');

app_generator::usage('assets', ln('assets.usage'));

app_generator::alias('assets:prepare', 'precompile compile build');
app_generator::alias('assets:clean', 'clean clear');


// cleanup
app_generator::implement('assets:clean', function () {
  info(ln('assets.clean_up_resources'));

  notice(ln('assets.clean_up_files', array('path' => 'assets/_')));
  unfile(APP_PATH.DS.'assets'.DS.'_', '*', DIR_RECURSIVE | DIR_EMPTY);

  foreach (array('img', 'css', 'js') as $type) {
    notice(ln('assets.clean_up_files', array('path' => "static/$type")));
    unfile(APP_PATH.DS.'static'.DS.$type, '*', DIR_RECURSIVE | DIR_EMPTY);
  }


  foreach (array('resources', 'tables') as $one) {
    notice(ln('assets.removing_file', array('path' => "config/$one".EXT)));
    is_file($res_file = APP_PATH.DS.'config'.DS.$one.EXT) && unlink($res_file);
  }

  done();
});

// assets handling
app_generator::implement('assets:prepare', function () {
  static $css_min = NULL;

  if (is_null($css_min)) {
    $css_min = function ($text) {
      static $expr = array(
                '/;+/' => ';',
                '/;?[\r\n\t\s]*\}\s*/s' => '}',
                '/\/\*.*?\*\/|[\r\n]+/s' => '',
                '/\s*([\{;:,\+~\}>])\s*/' => '\\1',
                '/:first-l(etter|ine)\{/' => ':first-l\\1 {', //FIX
                '/(?<!=)\s*#([a-f\d])\\1([a-f\d])\\2([a-f\d])\\3/i' => '#\\1\\2\\3',
              );

      return preg_replace(array_keys($expr), $expr, $text);
    };
  }


  $base_path  = APP_PATH.DS.'assets';
  $static_dir = APP_PATH.DS.'static';
  $views_dir  = APP_PATH.DS.'views';

  $img_path   = $base_path.DS.'img';
  $img_dir    = $static_dir.DS.'img';

  if ($test = dir2arr($img_path, '*', DIR_RECURSIVE | DIR_MAP)) {
    foreach (array_filter($test, 'is_file') as $file) {
      $file_hash  = md5(md5_file($file) . filesize($file));
      $file_name  = str_replace($img_path.DS, '', extn($file)) . $file_hash . ext($file, TRUE);

      $static_img = $img_dir.DS.$file_name;

      ! is_dir(dirname($static_img)) && mkpath(dirname($static_img));
      ! is_file($static_img) && copy($file, $static_img);

      assets::assign($path = str_replace($base_path.DS, '', $file), $file_hash);
      success(ln('assets.compiling_asset', array('name' => $path, 'hash' => $file_hash)));
    }
  }


  $cache = array();

  if ($test = dir2arr($base_path, '*', DIR_MAP)) {
    foreach ($test as $file) {
      $out = array();
      $tmp = assets::extract($file);

      if ( ! empty($tmp['require'])) {
        foreach ($tmp['require'] as $old) {
          $key = str_replace($base_path.DS, '', $old);
          $new = $static_dir.DS.$key;

          if ( ! is_file($new) OR ! isset($cache[$key])) {
            copy($old, mkpath(dirname($new)).DS.basename($new));
            notice(ln('assets.copying_asset', array('name' => $key)));
            $cache[$key] = 1;
          }
        }
      }



      foreach ($tmp['include'] as $test) {
        $key = str_replace(APP_PATH.DS, '', $test);

        if (is_file($test)) {
          $out[$key] = ! empty($cache[$key]) ? $cache[$key] : $cache[$key] = read($test);
        } else {
          if ( ! empty($cache[$key])) {
            $out[$key] = $cache[$key];
          } else {
            $out[$key] = partial::parse(findfile(dirname($test), basename($test).'*', FALSE, 1));
          }
        }

        notice(ln('assets.appending_asset', array('name' => $key)));
      }


      if ( ! empty($out)) {
        $set = array_keys($out);
        $out = join("\n", $out);

        $out = preg_replace_callback('/\bimg\/\S+\.(?:jpe?g|png|gif)\b/i', function ($match) {
          return assets::resolve($match[0]);
        }, $out);

        write($tmp = TMP.DS.md5($file), ext($file) === 'css' ? $css_min($out) : jsmin::minify($out));

        $hash     = md5(md5_file($tmp) . filesize($tmp));
        $name     = str_replace($base_path.DS, '', $file);
        $min_file = $static_dir.DS.ext($file).DS.extn($name).$hash.ext($file, TRUE);

        rename($tmp, mkpath(dirname($min_file)).DS.basename($min_file));

        assets::assign($path = str_replace($base_path.DS, '', $file), $hash);
        success(ln('assets.compiling_asset', array('name' => $path, 'hash' => $hash)));
      }
    }
  }



  if ($test = dir2arr($views_dir, '*', DIR_RECURSIVE | DIR_MAP)) {
    foreach ($test as $partial_file) {
      if (ext($partial_file, TRUE) <> EXT) {
        $key = str_replace(APP_PATH.DS, '', $partial_file);
        $new = $base_path.DS.'_'.DS.$key;

        write(mkpath(dirname($new)).DS.basename($new), partial::parse($partial_file));
        notice(ln('assets.compiling_view', array('name' => $key)));
      }
    }
  }

  assets::save();

  done();
});

/* EOF: ./stack/scripts/assets/initialize.php */
