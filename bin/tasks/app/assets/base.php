<?php echo '<' . '?php'; ?>


class base_controller extends \Sauce\App\Controller
{
  public $title = '<?php echo \Labourer\Web\Text::camelcase($app_name, TRUE); ?>';
}
