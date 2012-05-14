<?php

/**
 * CSS initialization
 */

/**#@+
 * @ignore
 */
require __DIR__.DS.'chess'.EXT;

class chess_helper extends prototype
{// fake class
}

// render callback
if (class_exists('partial', FALSE)) {
  partial::register('chess', function ($file, array $vars = array()) {
    return chess::render($file);
  });
}

// asset compiler
if (class_exists('assets', FALSE)) {
  assets::compile('chess', function ($file) {
    return partial::render($file);
  });
}

// utility goodies
require __DIR__.DS.'helpers'.DS.'color'.EXT;
require __DIR__.DS.'helpers'.DS.'image'.EXT;
require __DIR__.DS.'helpers'.DS.'number'.EXT;
require __DIR__.DS.'helpers'.DS.'string'.EXT;
/**#@-*/

/* EOF: ./library/chess/initialize.php */
