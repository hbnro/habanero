<?php echo '<' . '?php'; ?>


namespace <?php echo classify($app_name); ?>\App;

class Base extends \Sauce\App\Controller
{
  public static $title = '<?php echo titlecase($app_name); ?>';
}
