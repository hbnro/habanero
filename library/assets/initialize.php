<?php

/**
 * Assets initialization
 */

/**#@+
 * @ignore
 */
require __DIR__.DS.'assets'.EXT;
require __DIR__.DS.'functions'.EXT;
/**#@-*/


// static files
assets::implement('read', function ($path) {
  $static_file = APP_PATH.DS.'static'.DS.strtr($path, '/', DS);

  if (is_file($static_file)) {
    $out = read($static_file);
  } else {
    $asset_file = APP_PATH.DS.'assets'.DS.$path;
    $asset_file = findfile(dirname($asset_file), basename($asset_file) . '*', FALSE, 1);

    if (is_file($asset_file)) {
      if (preg_match('/\.(jpe?g|png|gif|css|js)$/', $asset_file)) {
        $out = read($asset_file);
      } else {
        $old_file = TMP.DS.md5($path);

        if (is_file($old_file)) {
          if (filemtime($asset_file) > filemtime($old_file)) {
            unlink($old_file);
          }
        }

        if ( ! is_file($old_file)) {
          $text = partial::parse($asset_file);
          $now  = date('Y-m-d H:i:s', filemtime($asset_file));
          $out  = "/* $now ./" . strtr($path, '\\', '/') . " */\n$text";

          write($old_file, $out);
        } else {
          $out = read($old_file);
        }
      }
    } else {
      raise(ln('file_not_exists', array('name' => $path)));
    }
  }

  return array(
    'output' => $out,
    'type' => mime(ext($path)),
  );
});

/* EOF: ./library/assets/initialize.php */
