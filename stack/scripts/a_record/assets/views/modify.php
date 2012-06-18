- javascript_for('app')
- stylesheet_for('app')

section
  = link_to('<?php echo ln('ar.cancel'); ?>', url_for::<?php echo $model; ?>s())
  = partial('<?php echo $model; ?>s/errors.html', compact('error'))
  = form::put(url_for::update_<?php echo $model; ?>(array(id => $<?php echo $model; ?>-><?php echo $pk; ?>)), ~>
<?php foreach ($fields as $key => $val) { ?>
    div = form::field(array(type => '<?php echo $val['type']; ?>', name => 'row[<?php echo $key; ?>]', label => '<?php echo $val['ln']; ?>', value => $<?php echo $model; ?>-><?php echo $key; ?>))
<?php } ?>
    = form::submit('update', '<?php echo ln('ar.update_record', array('name' => $model)); ?>')
