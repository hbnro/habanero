<?php

import('cache');

$type = params('type');
$env  = option('environment');
$prod = $env === 'production';

$base_path = CWD.DS.'app'.DS.'views'.DS.'assets';

cache::block("--$type-assets-$env", function ()
  use($base_path, $type, $prod) {
  import('assets');

  assets::setup('path', $base_path);
  assets::setup('root', CWD.DS.'public');

  assets::compile('css', function ($file)
    use($base_path, $prod) {
    import('tsss');
    tsss::setup('path', $base_path.DS.'css');
    return tsss::render($file, option('environment') === 'production');
  });

  assets::compile('js', function ($file)
    use($prod) {// TODO: use JSMin instead...
    static $regex = array(
                    '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/' => '',
                    '/\s*([?!<(\[\])>=:,+]|if|else|for|while)\s*/' => '\\1',
                    '/\s{2,}/' => '',
                  );


    $text = read($file);

    if ($prod) {
      $text = preg_replace(array_keys($regex), $regex, $text);
      $text = str_replace('elseif', 'else if', $text);
    }
    return $text;
  });

  $base_file = $base_path.DS.$type.DS."app.$type";

  $test = preg_replace_callback('/\s+\*=\s+(\S+)/m', function ($match)
    use($type, $prod) {
    assets::append("$match[1].$type");
  }, read($base_file));

  $test = preg_replace('/\/\*[*\s]*?\*\//s', '', $test);

  assets::$type(eval('?>' . trim($test)));
}, $prod ? time() : 0);
