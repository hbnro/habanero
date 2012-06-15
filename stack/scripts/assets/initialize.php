<?php

i18n::load_path(__DIR__.DS.'locale', 'assets');

app_generator::usage('assets', ln('assets.usage'));

// TODO: clean, etc?
app_generator::alias('assets:prepare', 'precompile compile build');


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


  $base_path  = APP_PATH.DS.'views'.DS.'assets';
  $static_dir = APP_PATH.DS.'static';
  $img_path   = $base_path.DS.'img';
  $img_dir    = $static_dir.DS.'img';


  foreach (array('css', 'img', 'js') as $one) {
    if (is_dir($path = $static_dir.DS.$one)) {
      foreach (dir2arr($path, '*', DIR_RECURSIVE | DIR_MAP) as $file) {
        if (preg_match('/^.+?([a-f0-9]{32})\.\w+$/', basename($file), $match)) {
          @unlink($file);
        }
      }
    }
  }

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


      $set = array_map(function ($val)
        use($base_path, $static_dir, &$cache, &$out) {

        $key = str_replace($base_path.DS, '', $val);

        is_file($val) OR $val = $static_dir.DS.$key;
        is_file($val) && $out[$key] = ! empty($cache[$key]) ? $cache[$key] : $cache[$key] = read($val);

        notice(ln('assets.appending_asset', array('name' => $key)));
      }, $tmp['include']);


      if ( ! empty($out)) {
        $set = array_keys($out);
        $out = join("\n", $out);

        $out = preg_replace_callback('/\bimg\/\S+\.(?:jpe?g|png|gif)\b/i', function ($match) {
          return assets::resolve($match[0]);
        }, $out);

        write($tmp = TMP.DS.md5($file), ext($file) === 'css' ? $css_min($out) : jsmin::minify($out));

        $hash     = md5(md5_file($tmp) . filesize($tmp));
        $name     = str_replace($base_path.DS, '', $file);
        $min_file = $static_dir.DS.extn($name).$hash.ext($file, TRUE);

        rename($tmp, mkpath(dirname($min_file)).DS.basename($min_file));

        assets::assign($path = str_replace($base_path.DS, '', $file), $hash);
        success(ln('assets.compiling_asset', array('name' => $path, 'hash' => $hash)));
      }
    }
  }

  assets::save();
});

/* EOF: ./stack/scripts/assets/initialize.php */
