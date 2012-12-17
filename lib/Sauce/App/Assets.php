<?php

namespace Sauce\App;

class Assets
{

  private static $cache = array();

  private static $path = array(
                    'fonts_dir' => array('woff', 'eot', 'ttf', 'svg'),
                    'images_dir' => array('jpeg', 'jpg', 'png', 'gif'),
                    'styles_dir' => array('css'),
                    'scripts_dir' => array('js'),
                  );

  private static $set = array(
                    'head' => array(),
                    'body' => array(),
                  );





  public static function save()
  {
    $file = path(APP_PATH, 'config', 'resources.php');
    $code = var_export(array_filter(static::$cache, 'is_md5'), TRUE);

    is_dir(dirname($file)) && file_put_contents($file, '<' . "?php return $code;\n");
  }

  public static function solve($name)
  {
    $ext = \IO\File::ext($name);

    if ((APP_ENV === 'production') && ($hash = static::fetch($name))) {
      $name = str_replace(basename($name), basename($name, ".$ext")."$hash.$ext", $name);
    }
    return $name;
  }

  public static function fetch($name)
  {
    if ( ! static::$cache) {
      $file = path(APP_PATH, 'config', 'resources.php');
      is_file($file) && static::$cache = include $file;
    }

    if ( ! empty(static::$cache[$name])) {
      return static::$cache[$name];
    }
  }

  public static function assign($key, $val)
  {
    static::$cache[$key] = $val;
  }

  public static function build($from, $on)
  {
    switch ($on) {
      case 'styles_dir';
        $ext = 'css';
      break;
      case 'scripts_dir';
        $ext = 'js';
      break;
      default;
        throw new \Exception("Cannot build '$from'");
    }

    $dir = \Tailor\Config::get($on);
    $file = path($dir, "$from.$ext");

    if ( ! is_file($file)) {
      throw new \Exception("The file '$file' does not exists");
    }

    $out = array();
    $tmp = static::extract($file, $on);

    foreach ($tmp as $group => $sub) {
      foreach ($sub as $file) {
        $path = str_replace($dir, '', preg_replace('/\.(css|js).*$/', '.\\1', $file));

        $old = ltrim($path, DIRECTORY_SEPARATOR);
        $inc = 0;

        switch ($group) {
          case 'head';
            ($ext === 'js') && static::script($old);
          break;
          case 'include';
            (APP_ENV === 'production') ? $inc += 1 : $out []= static::tag_for(static::solve($old));
          break;
          case 'require';
            $out []= static::tag_for(static::solve($old));
          break;
          default;
            throw new \Exception("Unknown group '$group' of assets for loading");
        }
      }

      $inc && $out []= static::tag_for(static::solve("$from.$ext"));
    }

    return $out;
  }

  public static function parse($file)
  {
    return static::extract($file, static::guess($file));
  }

  public static function read($path)
  {
    $ext  = \IO\File::ext($path);
    $type = \IO\Helpers::mimetype($ext);

    $dir  = \Tailor\Config::get(static::guess($path));

    $path = trim(strtr($path, '\\/', '//'), '/');
    $file = substr($path, strpos($path, '/') + 1);

    $base = path($dir, $file);
    $test = \Tailor\Helpers::findfile("$base*", 0);

    if ( ! is_file($test)) {
      throw new \Exception("File '$base' not found");
    }

    switch ($ext) {
      case 'css';
      case 'js';
        if (\IO\File::ext($test) === $ext) {
          $output = \IO\File::read($test);
        } else {
          $tmp = path(\Tailor\Config::get('cache_dir'), strtr($file, '\\/', '__'));

          if (is_file($tmp)) {
            if (filemtime($test) > filemtime($tmp)) {
              unlink($tmp);
            }
          }

          if ( ! is_file($tmp)) {
            $tpl = \Tailor\Base::compile($test);
            $now = date('Y-m-d H:i:s', filemtime($test));
            $output = "/* $now ./assets/$ext/$file */\n$tpl";

            \IO\File::write($tmp, $output);
          } else {
            $output = \IO\File::read($tmp);
          }
        }
      break;
      default;
        $output = \IO\File::read($test);
      break;
    }

    return compact('output', 'type');
  }

  public static function url_for($path, $on)
  {
    if (strpos($path, '://') !== FALSE) {
      return $path;
    }

    $dir = \Tailor\Config::get(preg_replace('/_dir$/', '_url', $on));

    return "$dir/$path";
  }

  public static function tag_for($path)
  {
    $type = \IO\File::ext($path);

    switch ($type) {
      case 'css';
        return \Labourer\Web\Html::link('stylesheet', static::url_for($path, 'styles_dir'), array('type' => 'text/css'));
      case 'js';
        return \Labourer\Web\Html::script(static::url_for($path, 'scripts_dir'));
      case 'jpeg';
      case 'jpg';
      case 'png';
      case 'gif';
        return \Labourer\Web\Html::img(static::url_for($path, 'images_dir'), $path);
      default;
        throw new \Exception("Unsupported tag for '$type'");
    }
  }

  public static function asset_url($path)
  {
    return static::url_for(static::solve($path), static::guess($path));
  }

  public static function inline($code, $to = '', $before = FALSE)
  {
    static::push($to ?: 'head', $code, $before);
  }

  public static function script($path, $to = '', $before = FALSE)
  {
    static::push($to ?: 'head', static::tag_for($path), $before);
  }

  public static function append($path, $to = 'body')
  {
    strpos($path, '<') !== FALSE ? static::push($to, $path) : static::script($path, $to);
  }

  public static function prepend($path, $to = 'head')
  {
    strpos($path, '<') !== FALSE ? static::push($to, $path, TRUE) : static::script($path, $to, TRUE);
  }

  public static function before()
  {
    return join("\n", static::$set['head']);
  }

  public static function after()
  {
    return join("\n", static::$set['body']);
  }



  private static function push($on, $test, $prepend = FALSE)
  {
    $prepend ? array_unshift(static::$set[$on], $test) : static::$set[$on] []= $test;
  }

  private static function guess($path)
  {
    $ext = \IO\File::ext($path);
    $out = 'images_dir';

    foreach (static::$path as $dir => $set) {
      if (in_array($ext, $set)) {
        $out = $dir;
        break;
      }
    }

    return $out;
  }

  private static function extract($from, $on)
  {
    $out = array();

    is_file($from) OR $from = \Tailor\Helpers::resolve($from, $on);

    if ( ! is_file($from)) {
      throw new \Exception("The file '$from' does not exists");
    }

    // TODO: accept other formats?
    if (preg_match_all('/\s+\*=\s+(\w+)\s+(\S+)/m', file_get_contents($from), $match)) {
      foreach ($match[1] as $i => $key) {
        $tmp = \Tailor\Helpers::resolve($match[2][$i], $on);

        if (is_dir($tmp)) {
          \IO\Dir::open($tmp, function ($file)
            use (&$out, $key) {
              $out[$key] []= $file;
            });
        } else {
          $tmp && $out[$key] []= $tmp;
        }
      }
    }

    return $out;
  }

}
