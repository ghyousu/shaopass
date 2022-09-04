<?php

   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   $NUM_ROWS_PER_CLASS  = 6;
   $NUM_COLUMNS_PER_ROW = 6;

   function getUnseatedSpanId($stud_id)  { return 'unseated_stud_' . $stud_id; }

   function getDraggableHtmlId($index)   { return 'drag_div_' . $index; }
   function getDraggableHtmlName($index) { return getDraggableHtmlId($index); }

   function getUpdatedSeatingMapId()     { return 'hidden_updated_seating_map'; }
   function getUpdatedSeatingMapName()   { return getUpdatedSeatingMapId(); }

   function getApplyBtnId()              { return 'apply_seating_btn'; }
   function getApplyBtnName()            { return getApplyBtnId(); }

   if ($_SERVER['REQUEST_METHOD'] == 'POST')
   {
      // var_dump($_POST);
      // die('test');
      // sample $_POST
      // array(2) { ["hidden_updated_seating_map"]=> string(26) "11,6,1,1 24,6,2,0 61,6,3,0" ["apply_seating_btn"]=> string(5) "Apply" }
      if ($_POST[getApplyBtnName()])
      {
         $new_seating_array = explode(" ", $_POST[getUpdatedSeatingMapId()]);

         for ($i=0; $i<count($new_seating_array); ++$i)
         {
            $new_seating = explode(",", $new_seating_array[$i]);

            insertUpdateSeatAssignment(
                  $new_seating[0],  // stud_id
                  $new_seating[1],  // row
                  $new_seating[2],  // col
                  $new_seating[3]); // is_unseated / is_insert
         }
      }
   }
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

   function getUnseatedSpanId(stud_id)
   {
      // debugger;
      var dummy_id = <?php echo '"' . getUnseatedSpanId(0) . '"'; ?>;
      var elem_id = dummy_id.replace("0", "" + stud_id + "");

      return elem_id;
   }

   function apply_btn_callback(event)
   {
      // debugger;

      var hidden_seating_elem = document.getElementById('<?php echo getUpdatedSeatingMapId(); ?>');

      updated_seating_map.forEach
      (
          function (value, key) {
             var stud_id = key.split("_")[2];
             var row_id  = value.split("_")[1];
             var col_id  = value.split("_")[2];

             var unseated_span_id = getUnseatedSpanId(stud_id);
             var span_elem = document.getElementById(unseated_span_id);

             var is_unseated = 1;

             if (span_elem == null)
             {
                is_unseated = 0;
             }

             console.log("\t" + key + ": " + value + ", is_unseated = " + is_unseated + "\n");
             hidden_seating_elem.value += stud_id + "," + row_id + "," + col_id + "," + is_unseated + " ";
          }
      )

      hidden_seating_elem.value = hidden_seating_elem.value.trim();

      if (hidden_seating_elem.value == "")
      {
         alert('no changes to the assigned seat');
         event.preventDefault();
      }
      else
      {
         console.log('"' + hidden_seating_elem.value + '"');
      }
   }


</script>

<p style='font-size: 1.5em'>Students without seat assignment</p>
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
               <span id='<?php echo getUnseatedSpanId($students[$i]->student_id); ?>' style="font-size: 1.5em">
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

   <!-- add the Apply button -->
   <tr>
      <td colspan='6' style="padding-top: 30px; padding-bottom: 30px; padding-left: 85%">
         <!-- NOTE: value is filled by javascript after apply button is clicked -->
         <input type="hidden" value='' name='<?php echo getUpdatedSeatingMapName(); ?>'
                id='<?php echo getUpdatedSeatingMapId(); ?>' />

         <input type='submit' name='<?php echo getApplyBtnName(); ?>'
               onclick="apply_btn_callback(event)" value='Apply' style='font-size: 1.5em' />
      </td>
   </tr>

   </form>
</table>

</div>
