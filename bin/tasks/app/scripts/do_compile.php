<?php

$old = sizeof($cache);

foreach (array($source_dir, $assets_dir) as $from) {
  \IO\Dir::each($from, '*', function ($file)
    use (&$cache, $source_dir, $assets_dir, $output_dir) {
      $key  = md5_file($file);
      $key .= filemtime($file);

      if (preg_match('/\.(html|php|css|js)(?:\.\w+)*?$/', $file, $match)) {
        if (in_array($key, $cache)) {
          return;
        }

        $cache []= $key;

        $path = trim(dirname(str_replace(array($source_dir, $assets_dir), '', $file)), '.\\/');
        $type = $match[1];

        @list($name) = explode(".$type", basename($file));

        $base = str_replace("$name.$type", '', basename($file)) ?: FALSE;
        $out = path($output_dir, $path, "$name.$type");
        $dir = dirname($out);

        is_dir($dir) OR mkdir($dir, 0777, TRUE);

        if ( ! is_file($out) OR (filemtime($file) > filemtime($out))) {
          switch ($type) {
            case 'html';
              $uri  = "/$path";
              $uri .= $name <> 'index' ? "/$name.$type" : '';

              provide('current_url', $uri);

              if ($base) {
                $view = \Tailor\Base::compile($file);
              } else {
                $view = read($file);
              }

              $layout = yield('layout') ?: 'default';
              $layout = \Tailor\Helpers::resolve("layouts/$layout", 'views_dir');

              $hash  = "$layout@";
              $hash .= md5_file($file);
              $hash .= filemtime($file);

              if (is_file($layout)) {
                $header = \Tailor\Helpers::resolve('_/header', 'views_dir');
                $footer = \Tailor\Helpers::resolve('_/footer', 'views_dir');

                $header && $header = \Tailor\Base::compile($header);
                $footer && $footer = \Tailor\Base::compile($footer);

                $view = adjust_tags("$header\n$view\n$footer", \Tailor\Base::compile($layout));
              }

              status('partial', $out);
              write($out, $view);
            break;
            case 'css';
            case 'js';
            default;
              if ($base) {
                $view = \Tailor\Base::compile($file);
                status('partial', $out);
                write($out, $view);
              } else {
                status('copy', $out);
                copy($file, $out);
              }
            break;
          }
        }
      }
    });
}

$cache_file = path($target_dir, '.cache');

(sizeof($cache) <> $old) && write($cache_file, join("\n", $cache));

$timeout && sleep($timeout);
