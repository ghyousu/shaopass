<?php

   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   $NUM_ROWS_PER_CLASS  = 6;
   $NUM_COLUMNS_PER_ROW = 6;

   function getDraggableHtmlId($index)   { return 'drag_div_' . $index; }
   function getDraggableHtmlName($index) { return getDraggableHtmlId($index); }
?>

<script type="text/javascript">
   function allowDrop(ev)
   {
      ev.preventDefault();
   }

   function drag(ev)
   {
      ev.dataTransfer.setData("text", ev.target.id);
   }

   function drop(ev)
   {
      debugger;
      ev.preventDefault();
      var data = ev.dataTransfer.getData("text");
      ev.target.innerText = "";
      ev.target.appendChild(document.getElementById(data));
   }
</script>

<p style='font-size: 1.5em'>Drag student names to the lower table to assign seat</p>
<table border=1>
      <?php
         $students = getStudentNamesPerClass($_SESSION[getAdminPageClassSessionKey()]);
         $num_students = count($students);
         $NUM_STUDENT_PER_ROW = 5;
         for ($i=0; $i<$num_students; ++$i):
      ?>

      <?php if ($i % $NUM_STUDENT_PER_ROW == 0) : ?> <tr> <?php endif; ?>
         <td width="150" height="31">
            <div id=<?php echo "'" . getDraggableHtmlId($i) . "'"?>  draggable="true" ondragstart="drag(event)">
               <span style="font-size: 1.5em">
                  <?php
                     echo $students[$i]->fname . " " . $students[$i]->lname;
                  ?>
               </span>
            </div>
         </td>
      <?php if ($i % $NUM_STUDENT_PER_ROW == $NUM_STUDENT_PER_ROW - 1) : ?> </tr> <?php endif; ?>

      <?php endfor; ?>
</table>

<div id='seating_div'>

<br/> <br/> <br/> <hr/>

<table border=1>
   <form action='/admin.php?action=add_remove' method='POST'>

   <?php for ($row=$NUM_ROWS_PER_CLASS; $row>0;--$row): ?>
      <tr>
      <?php for ($col=1; $col<=$NUM_COLUMNS_PER_ROW; ++$col): ?>
         <td width="200" height="50">
            <div id=<?php echo "'seat_" . $row . "_" . $col . "'"; ?>
               ondrop='drop(event)' ondragover='allowDrop(event)'>
               <?php echo "Row " . $row . ", Col " . $col; ?>
            </div>
         </td>
      <?php endfor; ?>
      </tr>
   <?php endfor; ?>

   </form>
</table>

</div>
