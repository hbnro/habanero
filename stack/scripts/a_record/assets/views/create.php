- javascript_for('app')
- stylesheet_for('app')

section
  = link_to('<?php echo ln('ar.cancel'); ?>', url_for::<?php echo $model; ?>s())
  = partial('<?php echo $model; ?>s/errors.html', compact('error'))
  = form::post(url_for::save_<?php echo $model; ?>(), ~>
<?php foreach ($fields as $key => $val) { ?>
    div = form::field(array(type => '<?php echo $val['type']; ?>', name => 'row[<?php echo $key; ?>]', label => '<?php echo $val['ln']; ?>'))
<?php } ?>
    = form::submit('save', '<?php echo ln('ar.create_record', array('name' => $model)); ?>')
