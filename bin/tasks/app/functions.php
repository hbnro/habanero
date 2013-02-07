<?php

function adjust_tags($from, $layout = FALSE)
{
  static $open = '/\s*<(script|style)[^>]*?>.*?<\/\\1>\s*/s',
         $close = '/\s*<(meta|link)[^>]*?\/?>\s*/s',
         $header = '/<(head)[^<>]*>(.+?)<\/\\1>/s',
         $descript = '/<title>(.+?)<\/title>/s';

  if (preg_match('/^(?:<html|["\'\[{])/', $from)) {
    return $from;
  }

  $separator = option('separator') ?: ' - ';
  $top_title = option('title');
  $sub_title = yield('title');

  $raw   =
  $head  =
  $body  =
  $title =
  $stack = array();

  $collect = function ($match)
    use(&$stack) {
    $stack []= array(
      'tag' => $match[1],
      'full' => trim($match[0]),
    );
  };

  $from = preg_replace_callback($header, function ($match)
    use(&$raw) {
      $raw []= $match[2];
    }, $from);

  preg_match($descript, $layout, $match) && $top_title = $match[1];
  preg_match($descript, $from, $match) && $sub_title = $match[1];

  $top_title && $title []= trim($top_title);
  $sub_title && $title []= trim($sub_title);

  $layout  = preg_replace($descript, '', $layout);
  $from = preg_replace($descript, '', $from);

  $from = preg_replace_callback($open, $collect, $from);
  $from = preg_replace_callback($close, $collect, $from);

  foreach ($stack as $one) {
    $one['tag'] === 'script' ? $body []= $one['full'] : $head []= $one['full'];
  }

  while ($head []= array_shift($raw));

  $from = preg_replace('/<(body)([^<>]*?)>/', "<\\1\\2>\n$from", $layout);
  $from = str_replace('</head>', sprintf("%s\n</head>", join("\n", $head)), $from);
  $from = str_replace('</body>', sprintf("%s\n</body>", join("\n", $body)), $from);
  $from = str_replace('</head>', sprintf("<title>%s</title>\n</head>", join($separator, $title)), $from);

  return $from;
}

function s3_handle()
{
  static $s3 = FALSE;

  if ( ! $s3 && ($test = option('assets'))) {
    $s3 = TRUE;

    foreach ((array) $test as $key => $val) {
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

  foreach ($set as $one => $ok) {
    if ($ok) {
      notice("Removing files from 's3://$name/$one'");
      $old = \Labourer\AS3::get_bucket($name, "$one/");

      foreach ($old as $file) {
        \Labourer\AS3::delete_object($name, $file['name']);
      }
    }
  }
}

function s3_upload_asset($file, $path)
{
  $mime = \IO\Helpers::mimetype($path);
  $bucket = \Labourer\Config::get('s3_bucket');

  \Labourer\AS3::put_object_file($file, $bucket, $path, S3::ACL_PUBLIC_READ, array(), $mime);
}

function solve_paths($text)
{
  static $test = array(
            '/(?<=font\/)\S+\.(?:woff|eot|ttf|svg)\b/i',
            '/(?<=img\/)\S+\.(?:jpe?g|png|gif)\b/i',
          );

  foreach ($test as $expr) {
    $text = preg_replace_callback($expr, function ($match) {
        return \Sauce\App\Assets::solve($match[0]);
      }, $text);
  }

  return $text;
}

function css_min($text)
{
  static $expr = array(
            '/;+/' => ';',
            '/:\s+/' => ':',
            '/;?[\r\n\t\s]*\}\s*/s' => '}',
            '/\/\*.*?\*\/|[\r\n]+/s' => '',
            '/\s*([\{;,\+\}>])\s*/' => '\\1',
            '/:first-l(etter|ine)\{/' => ':first-l\\1 {', //FIX
            '/(?<!=)\s*#([a-f\d])\\1([a-f\d])\\2([a-f\d])\\3/i' => '#\\1\\2\\3',
          );

  return preg_replace('/\s+/', ' ', preg_replace(array_keys($expr), $expr, $text));
}

function js_min($text)
{
  return \JShrink\Minifier::minify($text);
}
