<?php

i18n::load_path(__DIR__.DS.'locale', 'app');

app_generator::usage(ln('app.generator_title'), ln('app.generator_usage'));

app_generator::alias('create', 'new');
app_generator::alias('status', 'st');
app_generator::alias('execute', 'exec run');
app_generator::alias('generate', 'make gen g');
app_generator::alias('configure', 'config conf');
app_generator::alias('precompile', 'build compile');


// create application
app_generator::implement('create', function () {
  info(ln('app.verifying_installation'));

  if ( ! cli::flag('force') && dirsize(getcwd())) {
    notice(ln('app.application'));

    $tmp = dir2arr(getcwd(), '*', DIR_RECURSIVE | DIR_EMPTY);
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

    error(ln('app.directory_must_be_empty'));
  } else {
    require __DIR__.DS.'scripts'.DS.'create_application'.EXT;
    done();
  }
});


// application status
app_generator::implement('status', function () {
  require __DIR__.DS.'scripts'.DS.'app_status'.EXT;
});


// script generation
app_generator::implement('generate', function($what = '', $name = '') {
  config(getcwd().DS.'config'.DS.'application'.EXT);

  if ( ! in_array($what, array(
    'controller',
    'action',
    'model',
  ))) {
    error(ln('missing_arguments'));
  } else {
    info(ln('app.verifying_generator'));

    if ( ! $name) {
      error(ln("app.missing_{$what}_name"));
    } else {
      switch ($what) {
        case 'controller';
        case 'action';
        case 'model';
          require __DIR__.DS.'scripts'.DS."create_$what".EXT;
        break;
        default;
        break;
      }
    }
    done();
  }
});


// task execution
app_generator::implement('execute', function ($name = '') {
  require __DIR__.DS.'scripts'.DS.'execute_task'.EXT;
});


// configuration status
app_generator::implement('configure', function () {
  require __DIR__.DS.'scripts'.DS.'configuration'.EXT;
});


// compress compiled assets
app_generator::implement('precompile', function () {
  foreach (array('css', 'js') as $type) {
    $base_file = getcwd().DS.'public'.DS.$type.DS."all.$type";

    if (is_file($base_file)) {
      success(ln('server.writing_asset', array('type' => $type)));

      $text = read($base_file);
      $text = $type === 'css' ? minify_css($text) : minify_js($text);

      write(str_replace("all.$type", "all.min.$type", $base_file), $text);
    } else {
      error(ln('server.missing_asset', array('type' => $type)));
    }
  }
});



// TODO: improve compressors
function minify_js($text) {
  static $expr = array(
                    '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!:)\/\/.*$))/' => '',
                    #'/\s*([?!<(\[\])>=:,+]|if|else|for|while)\s*/' => '\\1',
                    #'/ {2,}/' => ' ',
                  );

  $text = preg_replace(array_keys($expr), $expr, $text);
  $text = str_replace('elseif', 'else if', $text);

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
