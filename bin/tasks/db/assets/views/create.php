- use Labourer\Web\Form as Form

- javascript_for('app')
- stylesheet_for('app')

section
  = link_to('Cancel', url_for('<?php echo $base; ?>'))
  = partial('<?php echo $base; ?>/errors.php', compact('error'))
  = Form::post(url_for('save_<?php echo $name; ?>'), ~>
<?php foreach ($fields as $key => $val) { ?>
    div = Form::field(array(type => '<?php echo $val['type']; ?>', name => 'row[<?php echo $key; ?>]', label => '<?php echo $val['title']; ?>'))
<?php } ?>
    = Form::submit('save', 'Create <?php echo $name; ?>')
