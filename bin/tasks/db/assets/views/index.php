- use Labourer\Web\Form as Form,
      Labourer\Web\Html as Html

- javascript_for('app')
- stylesheet_for('app')

section
  = link_to('New <?php echo $name; ?>', url_for('new_<?php echo $name; ?>'))
  - $options = array(action => url_for('delete_all_<?php echo $base; ?>'), confirm => '¿Are you sure?')
  = Form::delete($options, ~>
    table
      thead
        tr
          th <?php echo strtoupper($pk) . "\n"; ?>
<?php foreach ($fields as $key => $val) { ?>
          th <?php echo $val['title']; ?>

<?php } ?>
      tfoot
        tr
          td { colspan => 99 }
            = Form::submit('delete_all', 'Delete selected items')
            = Html::ul($<?php echo $base; ?>->links())
      tbody
        - $<?php echo $base; ?>->each(($row) ~>
          tr
            td = Form::checkbox("pk[$row-><?php echo $pk; ?>]", $row-><?php echo $pk; ?>)
<?php foreach (array_keys($fields) as $one) { ?>
            td = Form::label("pk[$row-><?php echo $pk; ?>]", $row-><?php echo $one; ?>)
<?php } ?>
            td = link_to('Edit', url_for('edit_<?php echo $name; ?>', array(':id' => $row-><?php echo $pk; ?>)))
            td
              - $action = url_for('delete_<?php echo $name; ?>', array(':id' => $row-><?php echo $pk; ?>))
              - $params = array(action => $action, method => 'delete', confirm => '¿Are you sure?')
              = link_to('Delete', $params)
