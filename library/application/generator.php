<?php

i18n::load_path(__DIR__.DS.'locale', 'app');

app_generator::usage('app', ln('app.generator_title'), ln('app.generator_usage'));

app_generator::alias('app:create', 'create new');
app_generator::alias('app:status', 'status st');
app_generator::alias('app:action', 'action');
app_generator::alias('app:controller', 'controller');
app_generator::alias('app:prepare', 'precompile compile build');


// create application
app_generator::implement('app:create', function ($name = '') {
  info(ln('app.verifying_installation'));

  if ( ! $name) {
    error(ln('missing_arguments'));
  } else {
    $app_path = APP_PATH.DS.$name;

    if ( ! cli::flag('force') && dirsize($app_path)) {
      error(ln('app.directory_must_be_empty'));
    } else {
      require __DIR__.DS.'scripts'.DS.'create_application'.EXT;
      done();
    }
  }
});


// application status
app_generator::implement('app:status', function () {
  require __DIR__.DS.'scripts'.DS.'app_status'.EXT;
});


// controllers
app_generator::implement('app:controller', function($name = '') {
  if ( ! $name) {
    error(ln('app.missing_controller_name'));
  } else {
    require __DIR__.DS.'scripts'.DS.'create_controller'.EXT;
  }
  done();
});


// actions
app_generator::implement('app:action', function($name = '') {
  if ( ! $name) {
    error(ln('app.missing_action_name'));
  } else {
    require __DIR__.DS.'scripts'.DS.'create_action'.EXT;
  }
  done();
});


// assets handling
app_generator::implement('app:prepare', function () {
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
      success(ln('app.compiling_asset', array('name' => $path, 'hash' => $file_hash)));
    }
  }

  foreach (array('css', 'js') as $type) {
    if ($test = dir2arr($base_path.DS.$type, "*.$type")) {
      foreach ($test as $file) {
        $out = array();
        $set = array_map(function ($val)
          use($base_path, $static_dir, &$out) {

          $key = str_replace($base_path.DS, '', $val);

          is_file($val) OR $val = $static_dir.DS.$key;
          is_file($val) && $out[$key] = read($val);
        }, assets::extract($file, $type));

        if ( ! empty($out)) {
          $set = array_keys($out);
          $out = join("\n", $out);

          $out = preg_replace_callback('/\bimg\/\S+\.(?:jpe?g|png|gif)\b/i', function ($match) {
            return assets::resolve($match[0]);
          }, $out);

          write($tmp = TMP.DS.md5($file), $type === 'css' ? $css_min($out) : jsmin::minify($out));

          $hash     = md5(md5_file($tmp) . filesize($tmp));
          $name     = str_replace($base_path.DS, '', $file);
          $min_file = $static_dir.DS.extn($name).$hash.ext($file, TRUE);

          rename($tmp, mkpath(dirname($min_file)).DS.basename($min_file));

          assets::assign($path = str_replace($base_path.DS, '', $file), $hash);
          success(ln('app.compiling_asset', array('name' => $path, 'hash' => $hash)));

          foreach ($set as $one) {
            notice(ln('app.appending_asset', array('name' => $one, 'hash' => $hash)));
          }
        }
      }
    }
  }

  assets::save();
});



/* EOF: ./library/application/generator.php */
