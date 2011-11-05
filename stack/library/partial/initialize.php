<?php

i18n::load_path(__DIR__.DS.'locale', 'partial');

require __DIR__.DS.'functions'.EXT;
require __DIR__.DS.'partial'.EXT;


// render callback
partial::register('php', function ($file, array $vars = array()) {
  return render($file, TRUE, array(
      'locals' => $vars,
    ));
});
