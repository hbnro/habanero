routing::mount(function () {
  get('/', '<?php echo $model; ?>s#index', array('path' => '<?php echo $model; ?>s'));
  get('/new', '<?php echo $model; ?>s#create', array('path' => 'new_<?php echo $model; ?>'));
  post('/new', '<?php echo $model; ?>s#create', array('path' => 'save_<?php echo $model; ?>'));
  get('/:id/edit', '<?php echo $model; ?>s#modify', array('path' => 'edit_<?php echo $model; ?>'));
  put('/:id/update', '<?php echo $model; ?>s#modify', array('path' => 'update_<?php echo $model; ?>'));
  delete('/:id/remove', '<?php echo $model; ?>s#delete', array('path' => 'delete_<?php echo $model; ?>'));
  delete('/', '<?php echo $model; ?>s#delete_all', array('path' => 'delete_all_<?php echo $model; ?>s'));
}, array(
  'root' => '/<?php echo $model; ?>s',
  'protect' => TRUE,
));
