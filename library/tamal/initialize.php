<?php

/**
 * Tamal initialization
 */

/**#
 * @ignore
 */
require __DIR__.DS.'tamal'.EXT;

class tamal_helper extends prototype
{// fake class
}

i18n::load_path(__DIR__.DS.'locale', 'tamal');

// allow for tamal files
partial::register('tamal', function ($context) {
  return tamal::parse($context);
});


// common helpers

tamal_helper::implement('script', function ($value) {
  return tag('script', array('type' => 'text/javascript'), $value);
});

tamal_helper::implement('style', function ($value) {
  return tag('style', array('type' => 'text/css'), $value);
});

tamal_helper::implement('escape', function ($value) {
  return htmlspecialchars($value);
});

tamal_helper::implement('plain', function ($value) {
  return $value;
});

tamal_helper::implement('php', function ($value) {
  return '<' . "?php $value ?>";
});



// other helpers

tamal_helper::implement('chess', function ($value) {
  return tag('style', array('type' => 'text/css'), chess::parse($value));
});

tamal_helper::implement('coffee', function ($value) {
  return tag('script', array('type' => 'text/javascript'), coffee::parse($value));
});

tamal_helper::implement('markdown', function ($value) {
  return md::parse($value);
});

/**#@-*/

/* EOF: ./library/tamal/initialize.php */
