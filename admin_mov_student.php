<?php

   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   function getRadioButtonGroupName()  { return 'stud_id_radio_group_name'; }
   function getRadioBtnId($index)      { return 'radio_btn_id_' . $index; }
   function getRadioBtnLabelId($index) { return 'radio_btn_label_id_' . $index; }

   function getMoveBtnHtmlId()      { return 'move_btn'; }
   function getMoveBtnHtmlName()    { return getMoveBtnHtmlId(); }
   function getFromClassTdHtmlId()  { return 'from_class_td'; }
   function getToClassTdHtmlId()    { return 'to_class_td'; }
   function getToClassTdHtmlName()  { return 'to_class_name'; }

   function getToClassDropDownName(){ return 'to_class_drop_down_name'; }
   function getToClassDropDownId()  { return 'to_class_drop_down_id'; }

   function getNewFNameHtmlId()     { return 'new_fname'; }
   function getNewLNameHtmlId()     { return 'new_lname'; }
   function getNewFNameHtmlName()   { return getNewFNameHtmlId(); }
   function getNewLNameHtmlName()   { return getNewLNameHtmlId(); }

   if ($_SERVER['REQUEST_METHOD'] === 'POST')
   {
      // var_dump($_POST);
      // die('test');
      // sample $_POST
      // array(3) {
      //    ["stud_id_radio_group_name"]=> string(3) "185"
      //    ["to_class_drop_down_name"]=> string(3) "901"
      //    ["move_btn"]=> string(12) "Move Student"
      // }
      $stud_id   = $_POST[getRadioButtonGroupName()];
      $new_class = $_POST[getToClassDropDownName()];

      printDebug("Moving student with id $stud_id to '$new_class'", 0);

      moveStudent($stud_id, $new_class);
   }
?>

<script type="text/javascript">
   function decodeRadioBtnId(radio_btn_id)
   {
      var splited_array = radio_btn_id.split("_");

      var id = splited_array[ splited_array.length-1 ];

      return id;
   }

   function getRadioBtnLabelIdPrefix()
   {
      var dummy_label = <?php echo "'" . getRadioBtnLabelId(1) . "'" ?>;

      var splited_array = dummy_label.split("_");

      var prefix = "";

      for (i=0; i<splited_array.length-1; ++i)
      {
         prefix += splited_array[i] + "_";
      }

      return prefix;
   }

   function radioGroupChangeCallback(radio_btn)
   {
      // debugger;

      var common_id_index = decodeRadioBtnId(radio_btn.id);

      var label_id = getRadioBtnLabelIdPrefix() + common_id_index;

      var label_elem = document.getElementById(label_id);

      var selected_student_name = label_elem.innerText;

      // dispaly the "Move" from "to"
      var orig_name_label_elem = document.getElementById('orig_name_label');
      var from_class_elem      = document.getElementById('<?php echo getFromClassTdHtmlId(); ?>');
      var to_class_elem        = document.getElementById('<?php echo getToClassTdHtmlId(); ?>');
      var move_btn_elem        = document.getElementById('<?php echo getMoveBtnHtmlId(); ?>');

      orig_name_label_elem.innerHTML = "<span style='font-size: 1.5em'>Move<br/><B>" +
                                       selected_student_name + "</B></span> ";
      from_class_elem.style.display = "table-cell";
      to_class_elem.style.display   = "table-cell";
      move_btn_elem.style.display   = "table-cell";
   }

   function move_btn_clicked(event)
   {
      debugger;

      var fromClassValue = '<?php echo $_SESSION[getAdminPageClassSessionKey()]; ?>';

      var toClassDropDownId   = <?php echo "'" . getToClassDropDownId() . "'"; ?>;
      var toClassDropDownElem = document.getElementById(toClassDropDownId);
      var toClassValue        = toClassDropDownElem.value;

      if (fromClassValue == toClassValue)
      {
         event.preventDefault();
         alert("You can't move student from the same class to the same class");
         return ;
      }
   }

</script>

<table border=0>
   <form action='/admin.php?action=mov' method='POST'>
      <p style='font-size: 1.5em'>Select a student to be moved. </p>

      <!-- show existing student names as radio buttons -->
      <?php
         $students = getStudentNamesPerClass($_SESSION[getAdminPageClassSessionKey()]);
         $num_students = count($students);
         $NUM_STUDENT_PER_ROW = 5;
         for ($i=0; $i<$num_students; ++$i):
      ?>

         <?php if ($i % $NUM_STUDENT_PER_ROW == 0) : ?> <tr> <?php endif; ?>

         <td style='font-size: 1.5em'>
            <input style='width: 30px; height: 30px' type='radio'
              name=<?php echo "'" . getRadioButtonGroupName() . "'"; ?>
              id=<?php echo "'" . getRadioBtnId($i) . "'"; ?>
              value=<?php echo "'" . $students[$i]->student_id . "'"; ?>
              onchange="radioGroupChangeCallback(this)"
            />
            <label for=<?php echo "'" . getRadioBtnId($i) . "'"; ?>
               id=<?php echo "'" . getRadioBtnLabelId($i) . "'"; ?>
            >
            <?php
               echo $students[$i]->fname . " " . $students[$i]->lname;
            ?>
            </label>
         </td>

         <?php if (($i % $NUM_STUDENT_PER_ROW == $NUM_STUDENT_PER_ROW - 1) || $i >= $num_students -1) : ?>
            </tr> <!-- <?php echo "i = " . $i . " out of " . $num_students; ?> -->
         <?php endif; ?>

      <?php endfor; ?>

      <!-- add a artificial line break -->
      <tr>
        <td colspan="100%" style='border-bottom: 1px solid black; padding-top: 10px; padding-bottom: 10px; padding-left: 80%'>
        </td>
      </tr>

      <!-- show 'Move  to' fname, lname and submit button -->
      <tr>
         <td id='orig_name_label'>
            <!-- hidden field, filled by javascript when a radio button's selected -->
         </td>

         <td id='<?php echo getFromClassTdHtmlId(); ?>' style="display: none; font-size: 1.5em">
            From:<br/>
            <b><?php echo $_SESSION[getAdminPageClassSessionKey()]; ?></b>
         </td>

         <td id='<?php echo getToClassTdHtmlId(); ?>' style="display: none; font-size: 1.5em">
            To:<br/>
            <b>
               <?php
                  showEnumDropDown(
                     getClassEnumName(),
                     '', // label
                     getToClassDropDownName(),
                     getToClassDropDownId(),
                     false, // last arg: "show_all"
                     ""); // on_change callback function
               ?>
            </b>
         </td>

          <td style='padding-top: 10px;padding-left:80px'>
             <input id='<?php echo getMoveBtnHtmlId(); ?>' name='<?php echo getMoveBtnHtmlName(); ?>'
                 onclick="move_btn_clicked(event)" style='font-size: 1.5em; display: none'
                 type='submit' value='Move Student' />
      </tr>

   </form>
</table> <!-- admin_move_student.php table -->
