<?php

   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   $NUM_ROWS_PER_CLASS  = 6;
   $NUM_COLUMNS_PER_ROW = 6;

   function getStudentNameChkboxId()     { return 'student_id_chk_boxes'; }
   function getStudentNameChkboxName()   { return getStudentNameChkboxId(); }
   function getColorSelRadioBtnGrpName() { return 'color_sel_radio_grp'; }
   function getColorSelRadioBtnGrpId()   { return getColorSelRadioBtnGrpName(); }

   if ($_SERVER['REQUEST_METHOD'] == 'POST')
   {
      // var_dump($_POST);
      // die('test');
      // sample $_POST
      //   array(3) { ["student_id_chk_boxes"]=> array(1) { [0]=> string(3) "360" } ["display_color"]=> string(3) "red" ["submit"]=> string(6) "Submit" }
      if ($_POST['submit'])
      {
         $stud_ids = $_POST[getStudentNameChkboxName()];
         $display_color = $_POST[getColorSelRadioBtnGrpName()];

         updateStudentDisplayColor($stud_ids, $display_color);
      }
   }

?>

<style>

.color_selection_label {
   font-size: 2.0em;
   margin-right: 30px;
}

.my_buttons_td {
   text-align: right;
}

.my_buttons_input {
   font-size: 1.5em;
}

.my_custom_alert {
   padding: 20px;
   background-color: fuchsia;
   position: fixed;
   width: 30%;
   top: 30%;
   left: 40%;
   display: none;
}

</style>

<script type="text/javascript">

   function atLeastOneChecked(radio_grp_name)
   {
      var radios = document.getElementsByName(radio_grp_name);

      for (var i = 0, len = radios.length; i < len; i++) {
         if (radios[i].checked) {
            return true;
         }
      }

      return false;
   }

   function selectAllClicked()
   {
      debugger;

      var btn_elem = document.getElementById('select_all_btn');
      var btn_text = btn_elem.value;

      var chk_boxes_elem = document.getElementsByName('<?php echo getStudentNameChkboxName() . "[]"; ?>');

      if (btn_text == 'Select All')
      {
         btn_elem.value = "De-Select All";

         for (var i in chk_boxes_elem)
         {
            chk_boxes_elem[i].checked = true;
         }
      }
      else
      {
         btn_elem.value = "Select All";

         for (var i in chk_boxes_elem)
         {
            chk_boxes_elem[i].checked = false;
         }
      }
   }

   function submitClicked(event)
   {
      debugger;
      var alert_text_elem = document.getElementById('alert_text');
      var show_alert = false;

      // var chk_boxes_elem = document.getElementsByName('<?php echo getStudentNameChkboxName() . "[]"; ?>');

      if (false == atLeastOneChecked('<?php echo getStudentNameChkboxName() . "[]"; ?>'))
      {
         alert_text_elem.innerText = "Select at least one student name";
         show_alert = true;
      }

      if (!show_alert && false == atLeastOneChecked('<?php echo getColorSelRadioBtnGrpName(); ?>'))
      {
         alert_text_elem.innerText = "You need to make a color selection";
         show_alert = true;
      }

      if (show_alert)
      {
         // debugger;
         var alertDivElem = document.getElementById('my_custom_alert_id');
         alertDivElem.style.display = "block";

         event.preventDefault();
         return ;
      }
   }

   function closeBtnClicked()
   {
      document.getElementById('my_custom_alert_id').style.display = "none";

      setBackgroundColorByClassName('breakTypesTable', 'none');
      setBackgroundColorByClassName('passTypesTable', 'none');

      event.preventDefault();
      return ;
   }

</script>

<table border=0>
   <form action='/admin.php?action=rainbow' method='POST'>

   <tr>
      <td colspan=<?php echo $NUM_COLUMNS_PER_ROW; ?> >
         <div align='center' style='font-size: 2em'><b>Back</b></div>
      </td>
   </tr>

   <?php
       $stud_array = getStudentNamesForRainbowPage($_SESSION[getAdminPageClassSessionKey()]);
       $array_index = 0;
   ?>

   <?php for ($row=$NUM_ROWS_PER_CLASS; $row>0;--$row): ?>
      <tr>
      <?php for ($col=1; $col<=$NUM_COLUMNS_PER_ROW; ++$col): ?>

         <!-- get the student object from the array -->
         <?php $student = $stud_array[$array_index]; ?>

         <?php if ($student->seating_row == $row && $student->seating_col == $col) : ?>
            <td width="200" height="50" align='center' style='padding-bottom: 20px; font-size: 1.5em; background-color: <?php echo $student->display_color; ?>'>
               <input style='width: 30px; height: 30px' type='checkbox'
                 name='<?php echo getStudentNameChkboxName() . "[]"; ?>'
                 value='<?php echo $student->student_id; ?>'
               >
               <?php echo "<br/>" . $student->fname . " " . $student->lname; ?>
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
         <div align='center' style='font-size: 2em'><b>Front</b></div>
      </td>
   </tr>

   <!-- add the 'Select All' and 'Submit' button -->
   <tr>
      <!-- color selection -->
      <td>
         <input class='colorSelection' type='radio' name='<?php echo getColorSelRadioBtnGrpName(); ?>' id='red_color_radio_btn' value='red' />
         <label class='color_selection_label' id='red_color_radio_btn_label' for='red_color_radio_btn'>Red</label>
      </td>
      <td>
         <input class='colorSelection' type='radio' name='<?php echo getColorSelRadioBtnGrpName(); ?>'  id='green_color_radio_btn' value='green' />
         <label class='color_selection_label' id='green_color_radio_btn_label' for='green_color_radio_btn'>Green</label>
      </td>
      <td>
         <input class='colorSelection' type='radio' name='<?php echo getColorSelRadioBtnGrpName(); ?>'  id='no_color_radio_btn' value='unset' />
         <label class='color_selection_label' id='no_color_radio_btn_label' for='no_color_radio_btn'>No Color</label>
      </td>

      <td class='my_buttons_td' colspan='1'>
         <input class='my_buttons_input' id="select_all_btn" type="button"
            value="Select All" onclick="selectAllClicked();" />
      </td>

      <td class='my_buttons_td' colspan='1'>
         <input class='my_buttons_input' type='submit' name='submit'
               onclick="submitClicked(event)" value='Submit' />
      </td>
   </tr>

   </form>
</table>

     <!-- ---------- alert box ---------- -->
     <div class='my_custom_alert' id='my_custom_alert_id'>
         <p id='alert_text'>Testing alert message</p>
         <button id='closeAlertBtn' class="closebtn" onclick="closeBtnClicked()">OK</button>
     </div>

</div>

