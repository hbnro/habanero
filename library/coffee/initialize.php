<?php

/**
 * Coffee wrapper initialization
 */

if ( ! `coffee -v`) {
  $old = error_reporting();
  error_reporting(0);

  /**
   * @ignore
   */
  require __DIR__.DS.'vendor'.DS.'coffeescript'.EXT;

  error_reporting($old);
}



// TODO: there is another solution?
partial::register('coffee', function ($context) {
  return coffee::parse($context);
});


/**
 * Wrapper class
 */
class coffee
{
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
