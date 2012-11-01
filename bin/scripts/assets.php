<?php

$action = arg('assets');

! is_string($action) && $action = '';

if ( ! $action) {
  error('Missing or incorrect action');
} else {
  if ( ! in_array($action, array('help', 'clean', 'precompile'))) {
    error("Unknown action '$action'");
  } else {
    info("Executing '$action'");
    call_user_func("assets_{$action}");
  }
}


function assets_help()
{
  $message = <<<INFO

  Clean and precompile application assets

  --assets \bgreen(clean)\b       \clight_gray(# Remove generated cached from partials)\c
  --assets \bgreen(precompile)\b  \clight_gray(# Prepare the application assets for production)\c

INFO;

  writeln(colorize($message));
}

function assets_clean()
{
  notice('Assets cache cleanup');

  \IO\Dir::unfile(path(getcwd(), 'cache'), '*', TRUE);

  mkdir(path(getcwd(), 'cache'), 0777);


  if (s3_handle()) {
    s3_clean_bucket();
  } else {
    foreach (array('img', 'css', 'js') as $type) {
      success("Removing files from 'static/$type'");
      $files_dir = path(getcwd(), 'static', $type);
      \IO\Dir::unfile($files_dir, '*', TRUE);
      mkdir($files_dir, 0777);
    }
  }

  success("Removing file 'config/resources.php'");
  is_file($res_file = path(getcwd(), 'config', 'resources.php')) && unlink($res_file);
}

function assets_precompile()
{
  $cache      = array();
  $cache_dir  = \Tailor\Config::get('cache_dir');

  $base_path  = path(getcwd(), 'assets');
  $static_dir = path(getcwd(), 'static');
  $views_dir  = path(getcwd(), 'views');

  $img_path   = path($base_path, 'img');
  $img_dir    = path($static_dir, 'img');


  // views
  arg('v', 'views') && \IO\Dir::each(path(getcwd(), 'views'), '*', function ($file) {
      if (is_file($file)) {
        $name = join('.', array_slice(explode('.', basename($file)), 0, 2));
        $path = str_replace(path(getcwd(), 'views').DIRECTORY_SEPARATOR, '', dirname($file));

        $cache_dir  = \Tailor\Config::get('cache_dir');
        $cache_file = path($cache_dir, strtr("$path/$name", '\\/', '__'));

        if ( ! is_file($cache_file) && (\IO\File::ext($file, TRUE) <> '.php')) {
          write(path(dirname($cache_file), basename($cache_file)), \Tailor\Base::compile($file));
          notice("Processing view '$path/$name'");
        }
      }
    });


  // images
  arg('i', 'images') && \IO\Dir::each($img_path, '*.{jpeg,jpg,png,gif}', function ($file)
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

        success("Hashing image '$path' with $file_hash");
      }
    });


  // scripts & styles
  foreach (array('css' => 'styles', 'js' => 'scripts') as $type => $option) {
    arg(substr($type, 0, 1), $option) && \IO\Dir::open(path($base_path, $type), function ($file)
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

                notice("Copying asset file '$key'");
              }
            }
          }


          // asset mashup, grouped
          foreach ($tmp['include'] as $test) {
            $ext = \IO\File::ext($test);
            $key = str_replace(getcwd().DIRECTORY_SEPARATOR, '', $test);

            $name = join('.', array_slice(explode('.', basename($test)), 0, 2));
            $path = str_replace(path(getcwd(), 'assets', $type).DIRECTORY_SEPARATOR, '', dirname($test));

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

            notice("Appending file '$key'");
          }


          // final integration
          if ( ! empty($out)) {
            $set = array_keys($out);
            $out = join("\n", $out);

            $out = preg_replace_callback('/\bimg\/(\S+\.(?:jpe?g|png|gif))\b/i', function ($match) {
                return \Sauce\App\Assets::solve($match[1]);
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

            success("Hashing asset file '$path' with $hash");
          }

        }
      });
  }

  \Sauce\App\Assets::save();
}

function s3_handle()
{
  static $s3 = FALSE;
return 0;
  if ( ! $s3 && ($test = option('assets'))) {
    $s3 = TRUE;

    foreach ($test as $key => $val) {
      \Labourer\Config::set($key, $val);
    }
    \Labourer\AS3::initialize();

    $set    = \Labourer\AS3::buckets();
    $name   = \Labourer\Config::get('s3_bucket');
    $region = \Labourer\Config::get('s3_location') ?: FALSE;

    if ( ! isset($set[$name])) {
      \Labourer\AS3::put_bucket($name, S3::ACL_PUBLIC_READ, $region);
    }
  }
  return $s3;
}

function s3_clean_bucket()
{
  $name = \Labourer\Config::get('s3_bucket');
  $test = array('img', 'css', 'js');

  foreach ($test as $one) {
    notice("Removing files from 's3://$name/$one'");
    $old = \Labourer\AS3::get_bucket($name, "$one/");

    foreach ($old as $file) {
      \Labourer\AS3::delete_object($name, $file['name']);
    }
  }
}

function s3_upload_asset($file, $path)
{
  $mime = \IO\Helpers::mimetype($path);
  $bucket = \Labourer\Config::get('s3_bucket');

  \Labourer\AS3::put_object_file($file, $bucket, $path, S3::ACL_PUBLIC_READ, array(), $mime);
}

function css_min($text)
{
  static $expr = array(
            '/;+/' => ';',
            '/;?[\r\n\t\s]*\}\s*/s' => '}',
            '/\/\*.*?\*\/|[\r\n]+/s' => '',
            '/\s*([\{;:,\+~\}>])\s*/' => '\\1',
            '/:first-l(etter|ine)\{/' => ':first-l\\1 {', //FIX
            '/(?<!=)\s*#([a-f\d])\\1([a-f\d])\\2([a-f\d])\\3/i' => '#\\1\\2\\3',
          );

  return preg_replace(array_keys($expr), $expr, $text);
}

function js_min($text)
{
  return \JShrink\Minifier::minify($text);
}
