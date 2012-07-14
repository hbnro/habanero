<?php

/**
 * Markdown initialization
 */

/**
 * @ignore
 */
require __DIR__.DS.'vendor'.DS.'markdown'.EXT;

// allow for Markdown files
partial::register(array('md', 'markdown'), function ($file, array $vars = array()) {
	return Markdown(read($file));
});


/**
 * Wrapper class
 */
class md
{
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
