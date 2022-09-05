<html>
<head>
<style>

table {
   table-layout: fixed;
   width: 100%;
   height:100%;
}

td {
   text-align: center;
}

.class_name {
   font-size: 7em;
   font-weight: bold;
}

.student_name {
   font-size: 5em;
   font-weight: bold;
}

.cell_id {
   text-align: right;
   padding-right: 5%;
   padding-bottom: 2%;
   vertical-align: bottom;
   font-size: 0.5em;
   height: 10px;
   color: gray;
}

.singlePagePrintable {
   break-after:page;
}

</style>

</head>

<?php
   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   if (!isset($_SESSION['LOGGED_IN']))
   {
      header("location: /login.php");
   }

   $selected_cells = array();

   $unit_test = 1;

   if ($_SERVER['REQUEST_METHOD'] == 'POST')
   {
      // var_dump($_POST);
      // die('test');
      // sample $_POST
      // array(2)
      // {
      //    ["seating_cell_chk_boxes"]=> array(2)
      //       {
      //          [0]=> string(3) "3_3"
      //          [1]=> string(3) "3_4"
      //       }
      //    ["gen_seating_map"]=> string(21) "Generated Seating Map"
      // }

      $cell_ids = $_POST['seating_cell_chk_boxes'];

      for ($i=0; $i<count($cell_ids); ++$i)
      {
         $row = explode("_", $cell_ids[$i])[0];
         $col = explode("_", $cell_ids[$i])[1];

         printDebug("row = $row, col = $col <br/>", 0);

         $students = getStudentsPerSeat($row, $col);

         // var_dump($students);

         array_push($selected_cells, $students);
      }

      // var_dump($selected_cells);
   }
   else if ($unit_test == 1)
   {
      printDebug("unit testing... ", 0);
      $students = getStudentsPerSeat(6,6);
      array_push($selected_cells, $students);

      $students = getStudentsPerSeat(1,1);
      array_push($selected_cells, $students);

      printDebug("num_students = " . count($students) . "<br/>", 0);
   }
?>

<body>

<?php for ($i=0; $i<count($selected_cells); ++$i): ?>
<table class='singlePagePrintable' border=1>
   <?php
      $NUM_NAMES_PER_ROW = 2;
      $class_enums = getEnumArray(getClassEnumName());
      $cell_content = $selected_cells[$i];
      $row = 0;
      $col = 0;
   ?>

   <?php for ($j=0; $j<count($class_enums); ++$j): ?>

      <?php if ($j % $NUM_NAMES_PER_ROW == 0) : ?>
      <tr>
      <?php endif; ?>

      <!--  show td data -->
      <td>
         <?php if (isset($cell_content[$class_enums[$j]])): ?>
            <div class='class_name'>
               <?php echo $class_enums[$j];  ?>
            </div>

            <br/>
            <br/>
            <br/>

            <div class='student_name'>
               <?php
                  $stud = $cell_content[$class_enums[$j]];
                  $row = $stud->seating_row;
                  $col = $stud->seating_col;
                  echo $stud->fname . "<br/>" . substr($stud->lname, 0, 2) . ".";
               ?>
            </div>
         <?php endif; ?>
      </td>

      <?php if ($j % $NUM_NAMES_PER_ROW == 1) : ?>
      </tr>
      <?php endif; ?>

   <?php endfor; ?>

</table>

<!-- show the row / col id on the next page -->
<table class='singlePagePrintable' border=0>
   <tr>
      <td class="cell_id">
      <?php
         echo "R" . $row . "C" . $col;
      ?>
      </td>
   </tr>
</table>

<?php endfor; ?>

</body>

</html>
