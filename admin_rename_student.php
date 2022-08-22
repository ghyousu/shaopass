<?php

   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   function getRadioButtonGroupName()  { return 'stud_id_radio_group_name'; }
   function getRadioBtnId($index)      { return 'radio_btn_id_' . $index; }
   function getRadioBtnLabelId($index) { return 'radio_btn_label_id_' . $index; }

   function getFNameHtmlId($index)     { return 'fname_' . $index; }
   function getLNameHtmlId($index)     { return 'lname_' . $index; }
   function getFNameHtmlName($index)   { return getFNameHtmlId($index); }
   function getLNameHtmlName($index)   { return getLNameHtmlId($index); }
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
      debugger;

      var common_id_index = decodeRadioBtnId(radio_btn.id);

      var label_id = getRadioBtnLabelIdPrefix() + common_id_index;

      var label_elem = document.getElementById(label_id);

      var selected_student_name = label_elem.innerText;

      // TODO: update "from" student name
   }

</script>

<table border=0>
   <form action='/admin.php?action=mod' method='POST'>

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

      <!-- show 'rename to' fname, lname and submit button -->
      <tr>
         <td>
            <br/>
            <label style="font-size: 1.5em" for="<?php echo getFNameHtmlId($i) ?>">First Name:</label>
            <input style="width: 120px" type="text"
                id="<?php echo getFNameHtmlId( $i ); ?>"
                name="<?php echo getFNameHtmlName( $i ); ?>" >
         </td>

         <td>
            <br/>
            <label style="font-size: 1.5em" for="<?php echo getLNameHtmlId( $i ); ?>">Last Name:</label>
            <input style="width: 120px" type="text"
               id="<?php echo getLNameHtmlId( $i ); ?>"
               name="<?php echo getLNameHtmlName( $i ); ?>" >
          </td>
      </tr>

   </form>
</table> <!-- admin_rename_student.php table -->
