<?php

/**
 * CSS initialization
 */

/**#@+
 * @ignore
 */
require __DIR__.DS.'cssp'.EXT;

class cssp_helper extends prototype
{// fake class
}

// render callback
if (class_exists('partial', FALSE)) {
  partial::register('cssp', function ($file, array $vars = array()) {
    return cssp::render($file);
  });
}

// asset compiler
if (class_exists('assets', FALSE)) {
  assets::compile('cssp', function ($file) {
    return partial::render($file);
  });
}

// utility goodies
require __DIR__.DS.'helpers'.DS.'color'.EXT;
require __DIR__.DS.'helpers'.DS.'image'.EXT;
require __DIR__.DS.'helpers'.DS.'number'.EXT;
require __DIR__.DS.'helpers'.DS.'string'.EXT;
/**#@-*/

/* EOF: ./library/cssp/initialize.php */
