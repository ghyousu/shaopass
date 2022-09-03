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
   updated_seating_map = new Map();

   function allowDrop(ev)
   {
      ev.preventDefault();
   }

   function drag(ev)
   {
      // debugger;
      ev.dataTransfer.setData("src_elem_id", ev.target.id);
      ev.dataTransfer.setData("stud_name",   ev.target.innerText);
      console.log("moving elem " + ev.target.id + ", student name: " + ev.target.innerText);
   }

   function printUpdatedSeatMap()
   {
      console.log("current updated seating map: \n");
      updated_seating_map.forEach
      (
          function (value, key) {
             console.log("\t" + key + ": " + value +"\n");
          }
      )
   }

   function drop(ev)
   {
      // debugger;
      ev.preventDefault();
      var src_elem_id = ev.dataTransfer.getData("src_elem_id");
      var stud_name   = ev.dataTransfer.getData("stud_name");

      if (src_elem_id == ev.target.parentNode.id)
      {
         console.log("same src and target elements, do nothing");
      }
      else
      {
         ev.target.innerText = ""; // clear inner text
         ev.target.appendChild(document.getElementById(src_elem_id));

         console.log("moved " + stud_name + " to " + ev.target.id);
         updated_seating_map.set(src_elem_id, ev.target.id);
      }

      printUpdatedSeatMap();
   }

</script>

<p style='font-size: 1.5em'>Drag student names to the lower table to assign seat</p>
<table border=1>
      <?php
         $students = getStudentsWithoutSeatAssignment($_SESSION[getAdminPageClassSessionKey()]);
         $num_students = count($students);
         $NUM_STUDENT_PER_ROW = 5;
         for ($i=0; $i<$num_students; ++$i):
      ?>

      <?php if ($i % $NUM_STUDENT_PER_ROW == 0) : ?> <tr> <?php endif; ?>
         <td width="150" height="31">
            <div id=<?php echo "'" . getDraggableHtmlId($students[$i]->student_id) . "'"?>  draggable="true" ondragstart="drag(event)">
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

<br/> <hr/> <br/>

<table border=1>
   <form action='/admin.php?action=seating' method='POST'>

   <tr>
      <td colspan=<?php echo $NUM_COLUMNS_PER_ROW; ?> >
         <div align='center' style='font-size: 2em'><b>Back</b></div>
      </td>
   </tr>

   <?php $students = getStudentsWithSeatAssignment($_SESSION[getAdminPageClassSessionKey()]); ?>
   <?php for ($row=$NUM_ROWS_PER_CLASS; $row>0;--$row): ?>
      <tr>
      <?php for ($col=1; $col<=$NUM_COLUMNS_PER_ROW; ++$col): ?>
         <td width="200" height="50">
            <?php
               $array_index = $row * 10 + $col;
               if (isset($students[$array_index]))
               {
                  $stud = $students[$array_index];

                  echo '<div style="font-size: 1.5em" id="' . getDraggableHtmlId($stud->student_id) .
                       '"' .  " draggable='true' ondragstart='drag(event)'>\r";
                  echo $stud->fname . " " . $stud->lname;
                  echo "</div>\r";
               }
               else
               {
                  echo '<div style="color= gray" id="seat_' . $row . '_' . $col .
                       '"' .  " ondrop='drop(event)' ondragover='allowDrop(event)'>\r";
                  echo "Row " . $row . ", Col " . $col;
                  echo "</div>\r";
               }
            ?>
         </td>
      <?php endfor; ?>
      </tr>
   <?php endfor; ?>

   <tr>
      <td colspan=<?php echo $NUM_COLUMNS_PER_ROW; ?> >
         <div align='center' style='font-size: 2em'><b>Front</b></div>
      </td>
   </tr>

   </form>
</table>

</div>
