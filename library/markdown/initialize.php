<?php

require __DIR__.DS.'vendor'.DS.'markdown'.EXT;

if (class_exists('taml')) {
	taml::shortcut('markdown', function ($args, $plain, $params) {
    return Markdown($plain);
  });
}

if (class_exists('partial')) {
	partial::register(array('md', 'markdown'), function ($file, array $vars = array()) {
		return Markdown(read($file));
	});
}

if (class_exists('HamlParser')) {
	require dirname(__DIR__).DS.'phamlp'.DS.'vendor'.DS.'haml'.DS.'filters'.DS.'HamlBaseFilter'.EXT;
	require dirname(__DIR__).DS.'phamlp'.DS.'vendor'.DS.'haml'.DS.'filters'.DS.'_HamlMarkdownFilter'.EXT;
	class HamlMarkdownFilter extends _HamlMarkdownFilter {
		public function init() {
			$this->vendorPath = __DIR__.DS.'vendor'.DS.'markdown'.EXT;
			parent::init();
		}
	}
}
