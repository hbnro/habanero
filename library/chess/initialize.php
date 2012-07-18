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
partial::register('chess', function ($context) {
  return chess::parse($context);
});

// utility goodies
require __DIR__.DS.'helpers'.DS.'color'.EXT;
require __DIR__.DS.'helpers'.DS.'image'.EXT;
require __DIR__.DS.'helpers'.DS.'number'.EXT;
require __DIR__.DS.'helpers'.DS.'string'.EXT;
/**#@-*/

/* EOF: ./library/chess/initialize.php */
