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

/* EOF: ./library/markdown/initialize.php */
