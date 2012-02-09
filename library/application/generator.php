<?php

i18n::load_path(__DIR__.DS.'locale', 'app');

app_generator::usage(ln('app.generator_title'), ln('app.generator_usage'));

app_generator::alias('app:create', 'create new');
app_generator::alias('app:status', 'status st');
app_generator::alias('app:action', 'action');
app_generator::alias('app:controller', 'controller');
app_generator::alias('app:execute', 'execute exec run');
app_generator::alias('app:configure', 'configure config conf');


// create application
app_generator::implement('app:create', function ($name = '') {
  info(ln('app.verifying_installation'));

  if ( ! $name) {
    error(ln('missing_arguments'));
  } else {
    $app_path = APP_PATH.DS.$name;

    if ( ! cli::flag('force') && dirsize($app_path)) {
      /*notice(ln('app.application'));

      $tmp = dir2arr($app_path, '*', DIR_RECURSIVE | DIR_EMPTY);
      $map = function ($tree, $self, $depth = 0) {
        foreach ($tree as $key => $val) {
          $pre = str_repeat(' ', $depth);

          if (is_array($val)) {
            cli::writeln("$pre  \clight_gray,black($key/)\c");
            $self($val, $self, $depth + 2);
          } else {
            $size = fmtsize(filesize($val));
            $val  = basename($val);

            cli::writeln("$pre  \bwhite($val)\b \clight_gray($size)\c");
          }
        }
      };

      $map($tmp, $map);
*/
      error(ln('app.directory_must_be_empty'));
    } else {
      require __DIR__.DS.'scripts'.DS.'create_application'.EXT;
      done();
    }
  }
});


// application status
app_generator::implement('app:status', function () {
  require __DIR__.DS.'scripts'.DS.'app_status'.EXT;
});


// controllers
app_generator::implement('app:controller', function($name = '') {
  if ( ! $name) {
    error(ln('app.missing_controller_name'));
  } else {
    require __DIR__.DS.'scripts'.DS.'create_controller'.EXT;
  }
  done();
});


// actions
app_generator::implement('app:action', function($name = '') {
  if ( ! $name) {
    error(ln('app.missing_action_name'));
  } else {
    require __DIR__.DS.'scripts'.DS.'create_action'.EXT;
  }
  done();
});


// task execution
app_generator::implement('app:execute', function ($name = '') {
  require __DIR__.DS.'scripts'.DS.'execute_task'.EXT;
});


// configuration status
app_generator::implement('app:configure', function () {
  require __DIR__.DS.'scripts'.DS.'configuration'.EXT;
});



function minify_js($text) {
  return jsmin::minify($text);
}

function minify_css($text) {
  static $expr = array(
                    '/;+/' => ';',
                    '/;?[\r\n\t\s]*\}\s*/s' => '}',
                    '/\/\*.*?\*\/|[\r\n]+/s' => '',
                    '/\s*([\{;:,\+~\}>])\s*/' => '\\1',
                    '/:first-l(etter|ine)\{/' => ':first-l\\1 {', //FIX
                    '/(?<!=)\s*#([a-f\d])\\1([a-f\d])\\2([a-f\d])\\3/i' => '#\\1\\2\\3',
                  );

  return preg_replace(array_keys($expr), $expr, $text);
}

/* EOF: ./library/application/generator.php */
