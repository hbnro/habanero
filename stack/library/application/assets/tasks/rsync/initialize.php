<?php

require __DIR__.DS.'config.php';

i18n::load_path(__DIR__.DS.'locale', 'rsync');

class rsync_task extends app_task {

  public static $default = 'go';

  final public static function go() {
    $rsync = __DIR__;
    extract(static::$defs['dev']);
    notice(ln('rsync.default_deploy'));
    system("rsync --dry-run -avzlC --exclude-from $rsync/exclude.txt --stats --progress --delete -e '{$ssh_transport_beta}' . {$ssh_user_beta}:{$remote_root_beta}");
  }

  final public static function dev() {
    $rsync = __DIR__;
    extract(static::$defs['dev']);
    notice(ln('rsync.development_deploy'));
    system("rsync -avzlC --exclude-from $rsync/exclude.txt --stats --progress --delete -e '{$ssh_transport_beta}' . {$ssh_user_beta}:{$remote_root_beta}");
  }

  final public static function prod() {
    $rsync = __DIR__;
    extract(static::$defs['prod']);
    notice(ln('rsync.production_deploy'));
    system("rsync --dry-run -avzlC --exclude-from $rsync/exclude.txt --stats --progress --delete -e '{$ssh_transport}' . {$ssh_user}:{$remote_root}");
  }

  final public static function deploy() {
    $rsync = __DIR__;
    extract(static::$defs['prod']);
    notice(ln('rsync.final_deploy'));
    system("rsync -avlzC --exclude-from $rsync/exclude.txt --progress --stats --delete -e '{$ssh_transport}' . {$ssh_user}:{$remote_root}");
  }

}
