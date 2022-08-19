<!--
   this is the common menu/links on the left panel of the page for all admin pages
-->
<?php
   function printHtmlBreaks($num_break)
   {
      for ($i=0; $i<$num_break; ++$i)
      {
         echo "<br/>";
      }
      echo "\n";
   }

?>

<?php printHtmlBreaks(10); ?>

<table id='admin_left_panel_table'>
   <tr><td><h1>
      <a title="Add new student(s)" href='/admin.php?action=add'>Add Student(s)</a>
   </h1></td></tr>

   <tr><td><h1>
      <a title="Remove a student" href='/admin.php?action=del'>Remove Student</a>
   </h1></td></tr>

   <tr><td><h1>
      <a title="change student name" href='/admin.php?action=mod'>Rename Student</a>
   </h1></td></tr>

   <tr><td><h1>
      <a title="Move a student to a different class" href='/admin.php?action=mov'>Move Student</a>
   </h1></td></tr>

   <tr><td><h1>
      <a title="Change seat assignment for a given class" href='/admin.php?action=seating'>Seat Assignment</a>
   </h1></td></tr>
</table>

<?php printHtmlBreaks(10); ?>
