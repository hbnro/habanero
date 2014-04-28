<?php

if (arg('v f i c j a views fonts images styles scripts all')) {
  $cache      = array();
  $cache_dir  = \Tailor\Config::get('cache_dir');

  $base_path  = path(APP_PATH, 'app', 'assets');
  $views_dir  = path(APP_PATH, 'app', 'views');
  $static_dir = path(APP_PATH, 'static');

  // views
  if (arg('v views all') && is_dir($views_dir)) {
    \IO\Dir::each($views_dir, '*', function ($file) {
        if (is_file($file)) {
          $name = join('.', array_slice(explode('.', basename($file)), 0, 2));
          $path = str_replace(path(APP_PATH, 'app', 'views').DIRECTORY_SEPARATOR, '', dirname($file));

          $cache_dir  = \Tailor\Config::get('cache_dir');
          $cache_file = path($cache_dir, strtr("$path/$name", '\\/', '__'));

          write(path(dirname($cache_file), basename($cache_file)), \Tailor\Base::compile($file));
          status('prepare', "$path/$name");
        }
      });
  }

  $handler = function ($on)
    use ($base_path, $static_dir) {
      $dir  = path($static_dir, $on);
      $path = path($base_path, $on);

      return function ($file)
        use ($on, $dir, $path, $base_path, $static_dir) {
          if (is_file($file)) {
            $file_hash = md5(md5_file($file) . filesize($file));
            $file_name = str_replace($path.DIRECTORY_SEPARATOR, '', \IO\File::extn($file)) . $file_hash . \IO\File::ext($file, TRUE);
            $file_path = path($dir, $file_name);

            if (s3_handle()) {
              s3_upload_asset($file, str_replace($static_dir.DIRECTORY_SEPARATOR, '', $file_path));
            } else {
              ! is_dir(dirname($file_path)) && mkdir(dirname($file_path), 0777, TRUE);
              ! is_file($file_path) && copy($file, $file_path);
            }

            \Sauce\App\Assets::assign($path = str_replace(path($base_path, $on).DIRECTORY_SEPARATOR, '', $file), $file_hash);

            status('hashing', "$path [$file_hash]");
          }
        };
      };

  $test = array(
    'f fonts all' => 'font *.{woff,eot,ttf,svg}',
    'i images all' => 'img *.{jpeg,jpg,png,gif}',
  );

  // fonts + images
  foreach ($test as $flags => $set) {
    @list($path, $filter) = explode(' ', $set);

    $dir = path($base_path, $path);
    if (arg($flags) && is_dir($dir)) {
      \IO\Dir::each($dir, $filter, $handler($path));
    }
  }

  // scripts & styles
  foreach (array('css' => 'c styles all', 'js' => 'j scripts all') as $type => $option) {
    if (arg($option) && is_dir(path($base_path, $type))) {
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
                if (! is_file($old)) {
                  status('warning', "$old [not found]");
                  continue;
                }

                $key = str_replace($base_path.DIRECTORY_SEPARATOR, '', $old);
                $new = path($static_dir, $key);

                if (($type === 'js') && ! preg_match('/\.(min|pack)\.js$/', $key)) {
                  $min = path(TMP, md5($key));

                  write($min, js_min(read($old)));

                  $old = $min;
                }


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
                if (! is_file($test)) {
                  status('warning', "$test [not found]");
                  continue;
                }

                $ext = \IO\File::ext($test);
                $key = str_replace(APP_PATH.DIRECTORY_SEPARATOR, '', $test);

                $name = join('.', array_slice(explode('.', basename($test)), 0, 2));
                $path = str_replace(path(APP_PATH, 'app', 'assets', $type), '__', dirname($test));

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

                if ($type === 'css') {
                  $out[$key] = css_min(solve_paths($out[$key]));
                } else {
                  if ( ! preg_match('/\.(min|pack)\.js$/', $test)) {
                    $out[$key] = js_min($out[$key]);
                  }
                }

                status('prepare', "$key");
              }
            }

            // final integration
            if ( ! empty($out)) {
              $set = array_keys($out);

              write($tmp = path(TMP, md5($file)), join("\n", $out));

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
