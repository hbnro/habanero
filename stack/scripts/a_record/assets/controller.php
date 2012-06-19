<?php echo '<' . "?php\n"; ?>

class <?php echo $model; ?>s_controller extends base_controller
{
  public static function index() {
    $to = 10;

    pager::config('count_page', $to);
    pager::config('link_root', url_for::<?php echo $model; ?>s());

    $set  = <?php echo $model; ?>::get('<?php echo join("', '", array_keys($fields)); ?>');
    $from = pager::offset(<?php echo $model; ?>::count(), request::get('p'));

    static::$view['<?php echo $model; ?>s'] = $set->offset($from)->limit($to);
    static::$view['pages'] = pager::page_all();
  }
  public static function create() {
    static::$view['error'] = array();

    if (request::is_post('save')) {
      if (static::validate(request::post('row'))) {
        <?php echo $model; ?>::create(valid::data());
        redirect_to('<?php echo $model; ?>s', array('success' => '<?php echo ln('ar.a_record_created'); ?>'));
      }
    }
  }
  public static function modify() {
    static::$view['error'] = array();

    if (request::is_put('update')) {
      if (static::validate(request::post('row'))) {
        <?php echo $model; ?>::update_all(valid::data(), array('<?php echo $pk;  ?>' => params('id')));
        redirect_to('<?php echo $model; ?>s', array('success' => '<?php echo ln('ar.a_record_updated'); ?>'));
      }
    }

    if ( ! ($row = <?php echo $model; ?>::find(params('id')))) {
      redirect_to('<?php echo $model; ?>s', array('error' => '<?php echo ln('ar.a_record_missing'); ?>'));
    }
    static::$view['<?php echo $model; ?>'] = $row;
  }
  public static function delete() {
    if (<?php echo $model; ?>::delete_all(array('<?php echo $pk; ?>' => params('id')))) {
      redirect_to('<?php echo $model; ?>s', array('success' => '<?php echo ln('ar.a_record_deleted'); ?>'));
    }
    redirect_to('<?php echo $model; ?>s', array('error' => '<?php echo ln('ar.a_record_missing'); ?>'));
  }
  public static function delete_all() {
    if ($set = request::post('pk')) {
      <?php echo $model; ?>::delete_all(array('<?php echo $pk; ?>' => $set));
      redirect_to('<?php echo $model; ?>s', array('success' => '<?php echo ln('ar.a_record_set_deleted'); ?>'));
    }
    redirect_to('<?php echo $model; ?>s', array('notice' => '<?php echo ln('ar.a_record_set_missing'); ?>'));
  }
  private static function validate($data) {
    valid::setup(array(
<?php foreach ($fields as $key => $val) { ?>
      '<?php echo $key; ?>' => array('<?php echo ln('ar.a_record_required_field', array('name' => $key)); ?>' => 'required'),
<?php } ?>
    ));

    if (valid::done($data)) {
      return TRUE;
    }

    static::$view['error'] = valid::error();
  }
}
