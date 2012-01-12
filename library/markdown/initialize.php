<?php

/**
 * Markdown initialization
 */
call_user_func(function() {
	/**
   * @ignore
	 */
	require __DIR__.DS.'vendor'.DS.'markdown'.EXT;

	// :markdown filter
	if (class_exists('taml')) {
		taml::shortcut('markdown', function ($args, $plain, $params) {
	    return Markdown($plain);
	  });
	}

	// allow for Markdown files
	if (class_exists('partial')) {
		partial::register(array('md', 'markdown'), function ($file, array $vars = array()) {
			return Markdown(read($file));
		});
	}
});

/* EOF: ./library/markdown/initialize.php */
