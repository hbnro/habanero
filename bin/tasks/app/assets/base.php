<?php echo '<' . '?php'; ?>


namespace <?php echo camelcase($app_name, TRUE, '\\'); ?>\App;

class Base extends \Sauce\App\Controller
{
  public $title = '<?php echo camelcase($app_name, TRUE, '\\'); ?>';
}
