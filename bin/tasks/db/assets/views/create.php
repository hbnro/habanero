- use \Labourer\Web\Form as form

section
  = link_to('Cancel', url_for('<?php echo $base; ?>'))
  = partial('<?php echo $base; ?>/errors.php', compact('error'))
  = form::post(url_for('save_<?php echo $name; ?>'), ~>
<?php foreach ($fields as $key => $val) { if (in_array($val['type'], array('hidden', 'text', 'textarea', 'number', 'file', 'datetime', 'date', 'time'))) { ?>
    div = form::field(array(type => '<?php echo $val['type']; ?>', name => 'row[<?php echo $key; ?>]', label => '<?php echo $val['title']; ?>'))
<?php } elseif ($val['type'] === 'checkbox') { ?>
    div = form::field(array(type => 'checkbox', name => 'row[<?php echo $key; ?>]', label => '<?php echo $val['title']; ?>', value => 'on'))
<?php } else { ?>
    div = form::field(array(type => '<?php echo $val['type'] === 'json' ? 'textarea' : 'text' ; ?>', name => 'row[<?php echo $key; ?>]', label => '<?php echo $val['title']; ?>'))
<?php } } ?>
    = form::submit('save', 'Create <?php echo $name; ?>')
