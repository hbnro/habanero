<?php

/**
 * Markdown initialization
 */

/**
 * @ignore
 */
require __DIR__.DS.'vendor'.DS.'markdown'.EXT;

// allow for Markdown files
if (class_exists('partial', FALSE)) {
	partial::register(array('md', 'markdown'), function ($file, array $vars = array()) {
		return Markdown(read($file));
	});
}


/**
 * Wrapper class
 */
class md
{
  // blocks
	function block(Closure $lambda) {
    ob_start() && $lambda();

    $indent = 0;
    $test   = ob_get_clean();

    preg_match('/^(\s*?)(?=\w+)/m', $test, $match);

    ! empty($match[1]) && $indent = strlen($match[1]);

    $indent && $test = preg_replace("/^\s{0,{$indent}}/m", '', $test);

    return static::parse($test);
  }
  // file render
  final public static function compile($file) {
    return Markdown(read($file));
  }
  // text parse
  final public static function parse($text) {
    return Markdown($text);
  }
}

/* EOF: ./library/md/initialize.php */
