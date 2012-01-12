<?php

/**
 * Taml initialization
 */

call_user_func(function () {
  /**
   * @ignore
   */

  require __DIR__.DS.'taml'.EXT;

  i18n::load_path(__DIR__.DS.'locale', 'taml');


  // render callback
  if (class_exists('partial')) {
    partial::register('taml', function ($file, array $vars = array()) {
      return str_replace('\\"', '"', taml::render($file, $vars));//TODO: fixit!
    });
  }

  // common filters
  taml::shortcut('php', function ($args, $plain, $params) {
    return "<?php\n$plain;\n?>";
  });

  taml::shortcut('plain', function ($args, $plain, $params) {
    return $plain;
  });

  taml::shortcut('escape', function ($args, $plain, $params) {
    return ents($plain, TRUE);
  });

  taml::shortcut('cdata', function ($args, $plain, $params) {
    return sprintf('<![CDATA[%s]]>', $plain);
  });

  taml::shortcut('javascript', function ($args, $plain, $params) {
    return tag('script', '', $plain);
  });

  taml::shortcut('style', function ($args, $plain, $params) {
    return tag('style', '', $plain);
  });
});

/* EOF: ./library/taml/initialize.php */
