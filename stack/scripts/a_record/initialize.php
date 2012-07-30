<?php

import('db');
import('a_record');

i18n::load_path(__DIR__.DS.'locale', 'ar');

app_generator::usage('ar', ln('ar.usage'));

app_generator::alias('ar:scaffold', 'scaffold scaff crud');
app_generator::alias('ar:console', 'console c');
app_generator::alias('ar:backup', 'backup');
app_generator::alias('ar:model', 'model');


// models
app_generator::implement('ar:model', function ($name = '') {
  @list($name, $table) = explode(':', $name);

  if ( ! $name) {
    error(ln('ar.missing_model_name'));
  } else {
    $out_file = mkpath(APP_PATH.DS.'models').DS.$name.EXT;

    if (is_file($out_file)) {
      error(ln('ar.model_already_exists', array('name' => $name)));
    } else {
      success(ln('ar.model_class_building', array('name' => $name)));
      add_class($out_file, $name, cli::flag('parent') ?: 'db_model', '', $table ? compact('table') : array());
    }
  }
});

// backups
app_generator::implement('ar:backup', function ($model = '') {
  @list($model, $name) = explode(':', $model);

  if ( ! $model) {
    error(ln('ar.missing_model_name'));
  } else {
    $model_file = APP_PATH.DS.'models'.DS.$model.EXT;
    $name = $name ? "{$model}_$name": sprintf("{$model}_%s", date('YmdHis'));

    if ( ! is_file($model_file)) {
      error(ln('ar.missing_model_file', array('name' => $model)));
    } else {
      $php_file = mkpath(APP_PATH.DS.'database'.DS.'backup').DS.$name.EXT;
      $path = str_replace(APP_PATH.DS, '', $php_file);

      if (cli::flag('import')) {
        info(ln('ar.verifying_import'));

        if ( ! $model) {
          error(ln('ar.import_model_missing'));
        } else {
          if ( ! is_file($php_file)) {
            error(ln('ar.import_file_missing', array('path' => $path)));
          } else {
            success(ln('ar.importing', array('path' => $path)));

            cli::flag('delete-all') && $model::delete_all();

            $data = include $php_file;

            foreach ($data as $one) {
              $model::count($one) OR $model::create($one);
            }
            done();
          }
        }
      } else {
        info(ln('ar.verifying_export'));

        if ( ! $name) {
          error(ln('ar.export_model_missing'));
        } else {
          if (is_file($php_file)) {
            error(ln('ar.export_already_exists'));
          } else {
            success(ln('ar.exporting', array('path' => $path)));

            $data = array();

            $model::each(function ($one)
              use(&$data) {
              $data []= $one->fields();
            });

            write($php_file, sprintf('<' . "?php return %s;\n", var_export($data, TRUE)));
            done();
          }
        }
      }
    }
  }
});


// inspect records
app_generator::implement('ar:console', function () {
  /**
   * @ignore
   */
  function __set($val = NULL, array $vars = array()) {
    static $set = array();

    if (func_num_args() === 0) {
      return $set;
    } elseif (is_assoc($vars)) {
      $set = array_merge($set, $vars);
    }
    return $val;
  }


  $cache = array();

  if ($readline = function_exists('readline')) {
    readline_completion_function(function () {
      return array();
    });
  }

  $callback = $readline ? 'readline' : 'cli::readln';

  cli::main(function ()
    use($callback, $readline, &$cache) {

    $_ = trim(call_user_func($callback, '>>> '));

    if ($readline && $_ && ! in_array($_, $cache)) {
      readline_add_history($_);
      $cache []= $_;
    }

    if (in_array($_, array('exit', 'quit'))) {
      cli::quit();
    } else {// TODO: any less dirty solution?
      $out = (array) @eval("extract(__set());return __set($_,get_defined_vars());");

      pretty(function ()
        use($out) {
        foreach ($out as $key => $one) {
          $one = dump($one);
          echo is_numeric($key) ? "\bcyan(>>>)\b $one\n" : "\bgreen($key)\b $one\n";
        }
      });
    }
  });
});


// scaffolding
app_generator::implement('ar:scaffold', function () {
  info(ln('ar.verifying_structure'));

  $args  = func_get_args();
  $model = array_shift($args);

  if ( ! $model) {
    error(ln('ar.missing_model_name'));
  } else {
    $model_file = mkpath(APP_PATH.DS.'models').DS.$model.EXT;

    if ( ! is_file($model_file)) {
      add_class($model_file, $model, 'db_model');
    }


    $fail = FALSE;
    $out  = array();
    $old  = $model::columns();

    if ( ! empty($args)) {
      foreach ($args as $one) {
        @list($key, $type) = explode(':', $one);

        if ( ! isset($old[$key])) {
          $fail = TRUE;
          notice(ln('ar.unknown_field', array('name' => $key)));
        } elseif ( ! ($test = field_for($type ?: $old[$key]['type'], $key))) {
          $fail = TRUE;
          notice(ln('ar.unknown_field_type', array('name' => $key, 'type' => $type)));
        } else {
          $out[$key] = $test;
        }
      }
    } else {
      foreach ($old as $key => $val) {
        $out[$key] = field_for($val['type'], $key);
      }
    }

    foreach (array($model::pk(), 'created_at', 'modified_at') as $tmp) {
      if (isset($out[$tmp])) {
        unset($out[$tmp]);
      }
    }


    if ($fail OR ! $out) {
      error(ln('ar.missing_fields'));
    } else {
      build_scaffold($model::pk(), $model, $out);
      done();
    }
  }
});



function build_scaffold($pk, $model, $fields) {
  require __DIR__.DS.'scripts'.DS.'build_scaffold'.EXT;
}

function field_for($type, $key) {
  static $set = array(
            'primary_key' => array('type' => 'hidden'),
            'text' => array('type' => 'textarea'),
            'string' => array('type' => 'text'),
            'integer' => array('type' => 'number'),
            'numeric' => array('type' => 'number'),
            'float' => array('type' => 'number'),
            'boolean' => array('type' => 'checkbox'),
            'binary' => array('type' => 'file'),
            'timestamp' => array('type' => 'datetime'),
            'datetime' => array('type' => 'datetime'),
            'date' => array('type' => 'date'),
            'time' => array('type' => 'time'),
          );


  if ( ! empty($set[$type])) {
    $out = $set[$type];
    $out['ln'] = ucwords(strtr($key, '_', ' '));
    return $out;
  }

  return FALSE;
}

/* EOF: ./stack/scripts/a_record/initialize.php */
