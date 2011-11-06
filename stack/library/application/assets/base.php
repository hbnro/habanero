<?php echo '<' . '?php'; ?>


class base_controller extends app_controller {
  public static $title = '<?php echo camelcase($app_name, TRUE); ?>';
}
