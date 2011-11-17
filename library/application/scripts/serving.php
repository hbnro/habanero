<?php

$type          = params('type');
$base_path     = getcwd().DS.'views'.DS.'assets';
$compress_file = getcwd().DS.'public'.DS.$type.DS."all.min.$type";


assets::compile('css', function ($file)
  use($base_path) {
    return partial($file);

  /*import('css');
  css::config('path', $base_path.DS.'css');
  return css::render($file, option('environment') === 'production');*/

});

assets::compile('js', function ($file)
  use($base_path) {
    return partial($file);
  #static $regex = array(
  #                '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/' => '',
  #                '/\s*([?!<(\[\])>=:,+]|if|else|for|while)\s*/' => '\\1',
  #                '/\s{2,}/' => '',
  #              );

  #$text = read($file);

  #if ($prod) {
  #  $text = preg_replace(array_keys($regex), $regex, $text);
  #  $text = str_replace('elseif', 'else if', $text);
  #}
  #return $text;
});

$base_file = $base_path.DS.$type.DS."app.$type";

$test = preg_replace_callback('/\s+\*=\s+(\S+)/m', function ($match)
  use($type) {
  assets::append("$match[1].$type");
}, read($base_file));

$test = preg_replace('/\/\*[*\s]*?\*\//s', '', $test);


// TODO: compression, gzip?
assets::$type($test);

/* EOF: ./library/application/scripts/serving.php */
