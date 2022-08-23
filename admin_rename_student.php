<?php

   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   function getRadioButtonGroupName()  { return 'stud_id_radio_group_name'; }
   function getRadioBtnId($index)      { return 'radio_btn_id_' . $index; }
   function getRadioBtnLabelId($index) { return 'radio_btn_label_id_' . $index; }

   function getNewFNameHtmlId()     { return 'new_fname'; }
   function getNewLNameHtmlId()     { return 'new_lname'; }
   function getNewFNameHtmlName()   { return getNewFNameHtmlId(); }
   function getNewLNameHtmlName()   { return getNewLNameHtmlId(); }

   if ($_SERVER['REQUEST_METHOD'] === 'POST')
   {
      // var_dump($_POST);
      // die('test');
      // sample $_POST
      // array(4) {
      //    ["stud_id_radio_group_name"]=> string(3) "181"
      //    ["new_fname"]=> string(5) "Mason"
      //    ["new_lname"]=> string(3) "You"
      //    ["rename"]=> string(15) "Rename Selected"
      // }
      $stud_id   = $_POST[getRadioButtonGroupName()];
      $new_fname = trim($_POST[getNewFNameHtmlName()]);
      $new_lname = trim($_POST[getNewLNameHtmlName()]);

      printDebug("Renaming student with id $stud_id to '$new_fname' '$new_lname'", 0);

      renameStudent($stud_id, $new_fname, $new_lname);
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
      debugger;

      var common_id_index = decodeRadioBtnId(radio_btn.id);

      var label_id = getRadioBtnLabelIdPrefix() + common_id_index;

      var label_elem = document.getElementById(label_id);

      var selected_student_name = label_elem.innerText;

      // dispaly the "rename" from "to"
      var orig_name_label_elem = document.getElementById('orig_name_label');
      var new_fname_elem       = document.getElementById('new_fname_td');
      var new_lname_elem       = document.getElementById('new_lname_td');
      var rename_btn_elem      = document.getElementById('rename_btn_id');

      orig_name_label_elem.innerHTML = "<span style='font-size: 1.5em'>Renaming<br/><B>" + selected_student_name + "</B><br/>To:</span> ";
      new_fname_elem.style.display   = "table-cell";
      new_lname_elem.style.display   = "table-cell";
      rename_btn_elem.style.display  = "table-cell";
   }

   function rename_btn_clicked(event)
   {
      debugger;

      var newFnameId = <?php echo "'" . getNewFNameHtmlId() . "'"; ?>;
      var newLnameId = <?php echo "'" . getNewLNameHtmlId() . "'"; ?>;

      var newFnameElem = document.getElementById(newFnameId);
      var newLnameElem = document.getElementById(newLnameId);

      var fname_value = newFnameElem.value.trim();
      var lname_value = newLnameElem.value.trim();

      if (fname_value == '' || lname_value == '')
      {
         event.preventDefault();
         alert("You must fill in both First Name and Last Name");
         return ;
      }
   }

</script>

<table border=0>
   <form action='/admin.php?action=mod' method='POST'>
      <p style='font-size: 1.5em'>Select a student to be renamed. </p>

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

      <!-- show 'rename to' fname, lname and submit button -->
      <tr>
         <td id='orig_name_label'>
            <!-- hidden field, filled by javascript when a radio button's selected -->
         </td>

         <td id='new_fname_td' style="display: none">
            <br/>
            <label style="font-size: 1.5em" for="<?php echo getNewFNameHtmlId(); ?>">First Name:</label>
            <input style="width: 120px" type="text"
                id="<?php echo getNewFNameHtmlId(); ?>"
                name="<?php echo getNewFNameHtmlName(); ?>" >
         </td>

         <td id='new_lname_td' style="display: none">
            <br/>
            <label style="font-size: 1.5em" for="<?php echo getNewLNameHtmlId(); ?>">Last Name:</label>
            <input style="width: 120px" type="text"
               id="<?php echo getNewLNameHtmlId(); ?>"
               name="<?php echo getNewLNameHtmlName(); ?>" >
          </td>

          <td style='padding-top: 10px;padding-left:80px'>
             <input id='rename_btn_id' name='rename' onclick="rename_btn_clicked(event)"
                style='font-size: 1.5em; display: none'
                type='submit' value='Rename Selected' />
          </td>
      </tr>

   </form>
</table> <!-- admin_rename_student.php table -->
