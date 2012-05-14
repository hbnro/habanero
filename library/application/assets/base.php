<?php echo '<' . '?php'; ?>


class base_controller extends application {
  public static $title = '<?php echo camelcase($app_name, TRUE); ?>';
}
