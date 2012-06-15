<?php

import('a_record');

i18n::load_path(__DIR__.DS.'locale', 'ar');

app_generator::usage('ar', ln('ar.usage'));

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

      $type   = cli::flag('parent') ?: 'db_model';
      $parent = $table ? "\n  public static \$table = '$table';" : '';
      $code   = "<?php\n\nclass $name extends $type"
              . "\n{{$parent}\n}\n";

      write($out_file, $code);
    }
  }
});

// backups
app_generator::implement('ar:backup', function ($model = '') {
  @list($model, $name) = explode(':', $model);

  if ( ! $name) {
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

            foreach ($model::all() as $one) {
              $data []= $one->fields();
            }

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


  cli::main(function () {

    $_ = cli::readln('>>> ');

    if (in_array($_, array('exit', 'quit'))) {
      cli::quit();
    } else {// TODO: any less dirty solution?
      $out = (array) @eval("extract(__set());return __set($_,get_defined_vars());");

      pretty(function ()
        use($out) {
        foreach ($out as $key => $one) {
          $one = dump($one);
          echo is_num($key) ? "\bcyan(>>>)\b $one\n" : "\bgreen($key)\b $one\n";
        }
      });
    }
  });
});

/* EOF: ./stack/scripts/a_record/initialize.php */
