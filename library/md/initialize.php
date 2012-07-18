<?php

/**
 * Markdown initialization
 */

/**
 * @ignore
 */
require __DIR__.DS.'vendor'.DS.'markdown'.EXT;

// allow for Markdown files
partial::register(array('md', 'markdown'), function ($context) {
	return md::parse($context);
});


/**
 * Wrapper class
 */
class md
{
	// text parse
  final public static function parse($text) {
    return Markdown($text);
  }
}

/* EOF: ./library/md/initialize.php */
