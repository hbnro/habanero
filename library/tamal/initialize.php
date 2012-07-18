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
  return tag('script', array('type' => 'text/javascript'), "\n$value\n");
});

tamal_helper::implement('style', function ($value) {
  return tag('style', array('type' => 'text/css'), "\n$value\n");
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

/**#@-*/

/* EOF: ./library/tamal/initialize.php */
