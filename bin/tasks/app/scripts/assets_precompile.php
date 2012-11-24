<?php

if (arg('v', 'i', 'c', 'j', 'views', 'images', 'styles', 'scripts')) {
  $cache      = array();
  $cache_dir  = \Tailor\Config::get('cache_dir');

  $base_path  = path(APP_PATH, 'app', 'assets');
  $static_dir = path(APP_PATH, 'static');
  $views_dir  = path(APP_PATH, 'app', 'views');

  $img_path   = path($base_path, 'img');
  $img_dir    = path($static_dir, 'img');


  // views
  if (arg('v', 'views') && is_dir($views_dir)) {
    \IO\Dir::each($views_dir, '*', function ($file) {
        if (is_file($file)) {
          $name = join('.', array_slice(explode('.', basename($file)), 0, 2));
          $path = str_replace(path(APP_PATH, 'app', 'views').DIRECTORY_SEPARATOR, '', dirname($file));
          $path = str_replace(path(APP_PATH, 'app', 'views').DIRECTORY_SEPARATOR, '', dirname($file));

          $cache_dir  = \Tailor\Config::get('cache_dir');
          $cache_file = path($cache_dir, strtr("$path/$name", '\\/', '__'));

          if (\IO\File::ext($file, TRUE) <> '.php') {
            write(path(dirname($cache_file), basename($cache_file)), \Tailor\Base::compile($file));
            status('prepare', "$path/$name");
          }
        }
      });
  }


  // images
  if (arg('i', 'images') && is_dir($img_path)) {
    \IO\Dir::each($img_path, '*.{jpeg,jpg,png,gif}', function ($file)
      use ($base_path, $static_dir, $img_path, $img_dir) {
        if (is_file($file)) {
          $file_hash  = md5(md5_file($file) . filesize($file));
          $file_name  = str_replace($img_path.DIRECTORY_SEPARATOR, '', \IO\File::extn($file)) . $file_hash . \IO\File::ext($file, TRUE);
          $static_img = path($img_dir, $file_name);

          if (s3_handle()) {
            s3_upload_asset($file, str_replace($static_dir.DIRECTORY_SEPARATOR, '', $static_img));
          } else {
            ! is_dir(dirname($static_img)) && mkdir(dirname($static_img), 0777, TRUE);
            ! is_file($static_img) && copy($file, $static_img);
          }

          \Sauce\App\Assets::assign($path = str_replace(path($base_path, 'img').DIRECTORY_SEPARATOR, '', $file), $file_hash);

          status('hashing', "$path [$file_hash]");
        }
      });
  }


  // scripts & styles
  foreach (array('css' => 'styles', 'js' => 'scripts') as $type => $option) {
    if (arg(substr($type, 0, 1), $option) && is_dir(path($base_path, $type))) {
      \IO\Dir::open(path($base_path, $type), function ($file)
        use ($base_path, $static_dir, $type, $cache_dir, $cache) {
          if (is_file($file)) {
            $out = array();
            $tmp = \Sauce\App\Assets::parse($file);

            $test = array();

            isset($tmp['head']) && $test = array_merge($test, $tmp['head']);
            isset($tmp['require']) && $test = array_merge($test, $tmp['require']);

            // required scripts, stand-alone
            if ($test) {
              foreach ($test as $old) {
                $key = str_replace($base_path.DIRECTORY_SEPARATOR, '', $old);
                $new = path($static_dir, $key);

                if ( ! isset($cache[$key])) {
                  if (s3_handle()) {
                    s3_upload_asset($old, $key);
                  } elseif ( ! is_file($new)) {
                    is_dir(dirname($new)) OR mkdir(dirname($new), 0777, TRUE);
                    copy($old, $new);
                  }

                  $cache[$key] = 1;

                  status('prepare', $key);
                }
              }
            }


            // asset mashup, grouped
            if ( ! empty($tmp['include'])) {
              foreach ($tmp['include'] as $test) {
                $ext = \IO\File::ext($test);
                $key = str_replace(APP_PATH.DIRECTORY_SEPARATOR, '', $test);

                $name = join('.', array_slice(explode('.', basename($test)), 0, 2));
                $path = str_replace(path(APP_PATH, 'app', 'assets', $type).DIRECTORY_SEPARATOR, '', dirname($test));

                if ($ext <> $type) {
                  $cache_file = path($cache_dir, strtr("$path/$name", '\\/', '__'));

                  if (is_file($cache_file)) {
                    $out[$key] = ! empty($cache[$key]) ? $cache[$key] : $cache[$key] = read($cache_file);
                  } else {
                    if ( ! empty($cache[$key])) {
                      $out[$key] = $cache[$key];
                    } else {
                      $out[$key] = \Tailor\Base::compile($test);
                      write($cache_file, $out[$key]);
                    }
                  }
                } else {
                  $out[$key] = read($test);
                }

                status('prepare', "$key");
              }
            }


            // final integration
            if ( ! empty($out)) {
              $set = array_keys($out);
              $out = join("\n", $out);

              $out = preg_replace_callback('/(?<=img\/)\S+\.(?:jpe?g|png|gif)\b/i', function ($match) {
                  return \Sauce\App\Assets::solve($match[0]);
                }, $out);


              write($tmp = path(TMP, md5($file)), $out = $type === 'css' ? css_min($out) : js_min($out));

              $hash     = md5(md5_file($tmp) . filesize($tmp));
              $name     = str_replace($base_path.DIRECTORY_SEPARATOR, '', $file);
              $min_file = path($static_dir, \IO\File::extn($name)."$hash.$type");


              if (s3_handle()) {
                s3_upload_asset($tmp, str_replace($static_dir.DIRECTORY_SEPARATOR, '', $min_file));
              } else {
                is_dir(dirname($min_file)) OR mkdir(dirname($min_file), 0777, TRUE);
                rename($tmp, $min_file);
              }

              \Sauce\App\Assets::assign($path = str_replace(path($base_path, $type).DIRECTORY_SEPARATOR, '', $file), $hash);

              status('hashing', "$path [$hash]");
            }

          }
        });
    }
  }

  status('update', 'config/resources.php');
  \Sauce\App\Assets::save();
} else {
  error("\n  Nothing to do\n");
}
