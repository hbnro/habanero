mount(function () {
  get('/', '<?php echo $base; ?>#index', array('path' => '<?php echo $base; ?>'));
  get('/new', '<?php echo $base; ?>#create', array('path' => 'new_<?php echo $name; ?>'));
  post('/new', '<?php echo $base; ?>#create', array('path' => 'save_<?php echo $name; ?>'));
  get('/:id/edit', '<?php echo $base; ?>#modify', array('path' => 'edit_<?php echo $name; ?>'));
  put('/:id/update', '<?php echo $base; ?>#modify', array('path' => 'update_<?php echo $name; ?>'));
  delete('/:id/remove', '<?php echo $base; ?>#delete', array('path' => 'delete_<?php echo $name; ?>'));
  delete('/', '<?php echo $base; ?>#delete_all', array('path' => 'delete_all_<?php echo $base; ?>'));
}, array(
  'root' => '/<?php echo $base; ?>',
));
