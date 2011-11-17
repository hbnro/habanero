<?php

i18n::load_path(__DIR__.DS.'locale', 'server');

app_generator::usage(ln('server.generator_title'), ln('server.generator_usage'));

app_generator::alias('compile', 'build');
app_generator::alias('cleanup', 'clean');


// clear compiled assets
app_generator::implement('cleanup', function () {
  foreach (array('css', 'js') as $type) {
    $min_file = getcwd().DS.'public'.DS.$type.DS."all.min.$type";

    if (is_file($min_file)) {
      notice(ln('server.removing_asset', array('type' => $type)));
      unlink($min_file);
    } else {
      error(ln('server.missing_asset', array('type' => $type)));
    }
  }
});


// build assets
app_generator::implement('compile', function () {
  foreach (array('css', 'js') as $type) {
    $base_file = getcwd().DS.'public'.DS.$type.DS."all.$type";
    $min_file  = getcwd().DS.'public'.DS.$type.DS."all.min.$type";

    if (is_file($base_file)) {
      success(ln('server.writing_asset', array('type' => $type)));

      $text = read($base_file);
    // TODO: use better minification?
      write($min_file, $type === 'css' ? minify_css($text) : minify_js($text));
    } else {
      error(ln('server.missing_asset', array('type' => $type)));
    }
  }
});


// TODO: improve compressors
function minify_js($text) {
  static $expr = array(
                    '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!:)\/\/.*$))/' => '',
                    #'/\s*([?!<(\[\])>=:,+]|if|else|for|while)\s*/' => '\\1',
                    #'/ {2,}/' => ' ',
                  );

  $text = preg_replace(array_keys($expr), $expr, $text);
  $text = str_replace('elseif', 'else if', $text);

  return $text;
}

function minify_css($text) {
  static $expr = array(
                    '/;+/' => ';',
                    '/;?[\r\n\t\s]*\}\s*/s' => '}',
                    #'/\/\*.*?\*\/|[\r\n]+/s' => '',
                    '/\s*([\{;:,\+~\}>])\s*/' => '\\1',
                    '/:first-l(etter|ine)\{/' => ':first-l\\1 {', //FIX
                    '/(?<!=)\s*#([a-f\d])\\1([a-f\d])\\2([a-f\d])\\3/i' => '#\\1\\2\\3',
                  );

  return preg_replace(array_keys($expr), $expr, $text);
}

/* EOF: ./library/server/generator.php */
