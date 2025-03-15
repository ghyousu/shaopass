<?php
   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   if (!isset($_SESSION['LOGGED_IN']))
   {
      header("location: /login.php");
   }

   $NUM_ROWS_PER_CLASS  = 6;
   $NUM_COLUMNS_PER_ROW = 6;

   function getStudentNameChkboxId()     { return 'student_id_chk_boxes'; }
   function getStudentNameChkboxName()   { return getStudentNameChkboxId(); }
   function getColorSelRadioBtnGrpName() { return 'color_sel_radio_grp'; }
   function getColorSelRadioBtnGrpId()   { return getColorSelRadioBtnGrpName(); }
?>

<table border=0>
   <form action='/post/admin_hw_tracker.php' method='POST'>

   <tr>
      <td colspan=<?php echo $NUM_COLUMNS_PER_ROW; ?> >
         <div class='div_front_back' style='font-size: 2em; text-align: center; font-weight: bold;'>Back</div>
      </td>
   </tr>

   <?php
       $class_id = $_SESSION[getAdminPageClassSessionKey()];

       $stud_array = getStudentsForHWTracker($class_id);
       $array_index = 0;
   ?>

   <?php for ($row=$NUM_ROWS_PER_CLASS; $row>0;--$row): ?>
      <tr class='tr_student_name'>
      <?php for ($col=1; $col<=$NUM_COLUMNS_PER_ROW; ++$col): ?>
         <!-- get the student object from the array -->
         <?php $student = $stud_array[$array_index]; ?>

         <?php if ($student->seating_row == $row && $student->seating_col == $col) : ?>
            <td class='td_student_name'>
               <input type='submit'
                 name='<?php echo "submit_id_" . $student->student_id; ?>'
                 value='<?php echo $student->fname . " " . $student->lname; ?>'
               <?php if ($student->today_hw_status == 'incomplete') : ?>
                 style='width: 150px;height: 100px; background-color: red;'
               <?php elseif ($student->today_hw_status == 'semi-complete') : ?>
                 style='width: 150px;height: 100px; background-color: orange;'
               <?php elseif ($student->today_hw_status == 'completed') : ?>
                 style='width: 150px;height: 100px; background-color: green;'
               <?php else : ?>
                 style='width: 150px;height: 100px;'
               <?php endif ; ?>
               >
               </input>
            </td>
            <?php ++$array_index; ?>
         <?php else : ?>
            <td/> <!-- empty seat, put a blank cell and move on to the next -->
            <?php continue; ?>
         <?php endif; ?>
      <?php endfor; ?>
      </tr>
   <?php endfor; ?>

   <tr>
      <td colspan=<?php echo $NUM_COLUMNS_PER_ROW; ?> >
         <div class='div_front_back' style='font-size: 2em; text-align: center; font-weight: bold;'>Front</div>
      </td>
   </tr>

   </form>
</table>

