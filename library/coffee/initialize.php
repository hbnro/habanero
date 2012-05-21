<?php

/**
 * Coffee wrapper initialization
 */

if (class_exists('partial', FALSE)) {
  /**
   * @ignore
   */
  ! `coffee -v` && require __DIR__.DS.'vendor'.DS.'coffeescript'.EXT;

  // TODO: there is another solution?
  partial::register('coffee', function ($file, array $vars = array()) {
    return coffee::compile($file);
  });
}

if (class_exists('assets', FALSE)) {
  // assets compiler
  assets::compile('coffee', function ($file) {
    return partial::render($file);
  });
}


/**
 * Wrapper class
 */
class coffee
{
  // file render
  final public static function compile($file) {
    return static::parse(read($file));
  }
  // text parse
  final public static function parse($text) {
    if ( !! `coffee -v`) {
      $tmp_file = TMP.DS.uniqid('--coffee-input');
      $out_file = TMP.DS.uniqid('--coffee-output');

      write($tmp_file, $text);

      system("coffee -sbp < $tmp_file > $out_file");

      $out = read($out_file);

      @unlink($tmp_file);
      @unlink($out_file);

      return $out;
    } else {
      return Coffeescript\compile($text, array(
        'bare' => TRUE,
      ));
    }
  }
}

/* EOF: ./library/coffee/initialize.php */
