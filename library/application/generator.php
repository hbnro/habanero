<?php

i18n::load_path(__DIR__.DS.'locale', 'app');

app_generator::usage(ln('app.generator_title'), ln('app.generator_usage'));

app_generator::alias('app:create', 'create new');
app_generator::alias('app:status', 'status st');
app_generator::alias('app:action', 'action');
app_generator::alias('app:controller', 'controller');
app_generator::alias('app:execute', 'execute exec run');
app_generator::alias('app:configure', 'configure config conf');
app_generator::alias('app:precompile', 'build compile precompile');


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


// compress compiled assets
app_generator::implement('app:precompile', function () {
  foreach (array('css', 'js') as $type) {
    $base_path = APP_PATH.DS.'static'.DS.$type;

    foreach (dir2arr($base_path, "*.$type") as $one) {
      $text = read($one);
      $name = extn($one, TRUE);

      if (substr($name, -4) <> '.min') {
        success(ln('app.compiling_asset', array('name' => basename($one))));
        $test = ($type === 'css' ? minify_css($text) : minify_js($text));
        write(dirname($one).DS."$name.min.$type", $test ?: $text);
      }
    }
  }
});



function minify_js($text) {
  $tmp_file = TMP.DS.md5(uniqid('--jsmin-input'));
  $min_file = TMP.DS.md5(uniqid('--jsmin-output'));

  write($tmp_file, $text);

  // TODO: find out a better solution?
  @system("jsmin < $tmp_file > $min_file");

  if (filesize($min_file)) {
    $text = read($min_file);
  }
  @unlink($tmp_file);

  return $text;
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
