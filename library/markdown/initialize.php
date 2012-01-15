<?php

/**
 * Markdown initialization
 */
call_user_func(function() {
	/**
   * @ignore
	 */
	require __DIR__.DS.'vendor'.DS.'markdown'.EXT;

	// allow for Markdown files
	if (class_exists('partial')) {
		partial::register(array('md', 'markdown'), function ($file, array $vars = array()) {
			return Markdown(read($file));
		});
	}
});

/* EOF: ./library/markdown/initialize.php */
