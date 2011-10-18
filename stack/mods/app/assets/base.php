<?php echo '<' . '?php'; ?>


class base_controller extends controller {
  public static $title = '<?php echo camelcase($app_name, TRUE); ?>';
}
