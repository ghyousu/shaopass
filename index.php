<html>
  <head>
     <title>Student Break Tracker</title>

     <?php
         require_once("common.php");

         setlocale(LC_ALL,'C.UTF-8');

         session_start();

         if (!isset($_SESSION['LOGGED_IN']))
         {
            header("location: /login.php");
         }

         if ($_SERVER['REQUEST_METHOD'] === 'POST')
         {
            // var_dump($_POST);
            // die("<br/>temp");
            // sample output:
            //    array(4) {
            //       ["student_id"]=> string(9) "35"
            //       ["break_type"]=> string(8) "Bathroom"
            //       ["pass_type"]=> string(1) "E"
            //       ["submit"]=> string(6) "Check In"
            //    }

               $student_id  = $_POST['student_id'];
               $break_type  = $_POST['break_type'];
               $pass_type   = $_POST['pass_type'];
               $is_checkout = ($_POST['submit'] == "Check Out");

               $break_id_session_key = getBreakIdSessionKey($student_id);

               if ($is_checkout)
               {
                  $break_id = checkoutStudent($student_id, $break_type, $pass_type);

                  $_SESSION[$break_id_session_key] = $break_id;
               }
               else
               {
                  checkinStudent($student_id, $_SESSION[$break_id_session_key]);
               }
            }
     ?>

     <script type="text/javascript">
      function updateStudentNameColor()
      {
         var html_elem_id = "<?php echo getHiddenFieldId(); ?>"

         var id_list = document.getElementById(html_elem_id).value.split("_");

         var numElems = id_list.length;

         for (i=0; i<numElems; ++i)
         {
            var merged_id = id_list[i];
            if (merged_id == 0) { continue; }

            var student_id = id_list[i].split("@")[1];

            // change student-name's background color
            var td_id_name = "td_label_" + student_id;
            var td_elem = document.getElementById(td_id_name);
            td_elem.style.backgroundColor = "orange";
         }
      }

      // this function is called when page loads or when a student's name is
      // selected. It looks at what pass is taken out and disables it/them.
      function disableTakenPassTypes()
      {
         var html_elem_id = "<?php echo getHiddenFieldId(); ?>"

         var id_list = document.getElementById(html_elem_id).value.split("_");

         var numElems = id_list.length;

         for (i=0; i<numElems; ++i)
         {
            var merged_id = id_list[i];
            if (merged_id == 0) { continue; }

            var break_id   = id_list[i].split("@")[0];
            var student_id = id_list[i].split("@")[1];

            var pass_type_name = document.getElementById("pass_type_" + break_id).innerHTML;
            if (pass_type_name != "")
            {
               document.getElementById("pass_type_label_" + pass_type_name).style.color = "lightgray";
               document.getElementById("pass_type_" + pass_type_name).disabled = true;
            }
         }
      }

      function on_page_loaded()
      {
         updateStudentNameColor();

         disableTakenPassTypes();
      }

      function getBreakId(student_id)
      {
         var html_elem_id = "<?php echo getHiddenFieldId(); ?>"

         var id_list = document.getElementById(html_elem_id).value.split("_");

         var numElems = id_list.length;

         for (i=0; i<numElems; ++i)
         {
            var stud_id = id_list[i].split("@")[1];
            if (stud_id == student_id)
            {
               var break_id = id_list[i].split("@")[0];
               return break_id;
            }
         }

         return 0;
      }

      function deselectAllRadioButtons(chk_group_name)
      {
         const chbx = document.getElementsByName(chk_group_name);

         for(let i=0; i < chbx.length; i++)
         {
            chbx[i].checked = false;
         }
      }

      function disableAllRadioButtons(chk_group_name)
      {
         const chbx = document.getElementsByName(chk_group_name);

         for(let i=0; i < chbx.length; i++)
         {
            chbx[i].disabled = true;
         }
      }

      function enableAllRadioButtons(chk_group_name)
      {
         const chbx = document.getElementsByName(chk_group_name);

         for(let i=0; i < chbx.length; i++)
         {
            chbx[i].disabled = false;
         }
      }

      // this is callback when a sutdent's name is selected
      function studentNameSelected(radioBtn)
      {
         var student_id = radioBtn.value;
         var break_id   = getBreakId(student_id);

         if (break_id > 0)
         {
            console.log("student " + student_id + " is checked out");

            var break_type_name = document.getElementById("break_type_" + student_id).innerHTML;
            var pass_type_name  = document.getElementById("pass_type_"  + break_id).innerHTML;

            // debugger;

            // check the radio buttons and disable them
            document.getElementById("break_type_" + break_type_name).checked = true;
            if (pass_type_name != "")
            {
               document.getElementById("pass_type_"  + pass_type_name ).checked = true;
            }
            else
            {
               deselectAllRadioButtons("pass_type");
            }

            disableAllRadioButtons("break_type");
            disableAllRadioButtons("pass_type");

            document.getElementById("submit_btn").value = "Check In";
         }
         else
         {
            console.log("student " + student_id + " is NOT checked out");

            // clear selections
            deselectAllRadioButtons("break_type");
            deselectAllRadioButtons("pass_type");

            // enable radio buttons but disalbe taken passes
            enableAllRadioButtons("break_type");
            enableAllRadioButtons("pass_type");
            disableTakenPassTypes();

            document.getElementById("submit_btn").value = "Check Out";
         }
      }

      function atLeastOneRadioButtonChecked(radio_grp_name)
      {
         var radios = document.getElementsByName(radio_grp_name);

         for (var i = 0, len = radios.length; i < len; i++) {
            if (radios[i].checked) {
               return true;
            }
         }

         return false;
      }

      // verify at least name and break type is selected
      function submitClicked(event)
      {
         if (false == atLeastOneRadioButtonChecked("student_id"))
         {
            alert("You need to select your name first");
            event.preventDefault(); // DON'T SUBMIT with violation
            return ;
         }

         if (false == atLeastOneRadioButtonChecked("break_type"))
         {
            alert("You must select a type");
            event.preventDefault(); // DON'T SUBMIT with violation
            return ;
         }

         if (false == atLeastOneRadioButtonChecked("pass_type"))
         {
            alert("You must select a pass");
            event.preventDefault(); // DON'T SUBMIT with violation
            return ;
         }
      }

     </script>




  </head>

  <body onload="on_page_loaded()">

     <div align="center">
     <table border=0>
     <tr>
      <td>
<!-- student's page only start -->
<?php if ($_SESSION['user_role'] == 'student') : ?>
         <h1 style='text-align: center'>
            <?php
                echo "Class " . $_SESSION['class_id'];
            ?>
         </h1>
<?php endif; ?>
      </td>

      <td align="right">
           <a href="/logout.php" style="font-size: 1.5em">
              Log Out
           </a>
      </td>
     </tr>

     <tr>
<!-- student's page only start -->
<?php if ($_SESSION['user_role'] == 'student') : ?>
     <td rowspan="2">
        <form action='/index.php' method='POST' enctype='multipart/form-data'>
           <?php
              echo '<h2 style="margin-block-end: -0.5em">Select your name:</h2><br/>';
              displayStudentNamesFromDB($_SESSION['class_id']);
           ?>

           <?php
              echo '<h2 style="margin-block-end: -0.5em">Select the type:</h2><br/>';
              displayBreakTypes();
           ?>

           <?php
              echo '<h2 style="margin-block-end: -0.5em">Select pass type:</h2><br/>';
              displayPassTypes();
           ?>

           <div align="center">
           <input id="submit_btn" type="submit" name="submit" value="Check Out"
                  onclick="submitClicked(event)" style="font-size: 1.5em" />
           </div>
        </form>
     </td>
<?php endif; ?>

     <td style="vertical-align: baseline">
        <h2>Break History: </h2>
        <?php
           displayBreakHistory($_SESSION['class_id']);
        ?>
     </td>
     </tr>

     <tr>
     <td style="vertical-align: baseline; padding-top: 30px">
        <form action='/notes.php' method='POST' enctype='multipart/form-data'>
          <textarea id="notes_textarea" name="notes" placeholder="Enter teacher's comment here ..." style="font-size: 1.5em; width: 450px; height: 250px; resize: none"></textarea>
          <br/>
          <br/>
          <div align="right">
            <input type='submit' name='submit_notes' value='Submit' style='font-size: 1.5em' />
          </div>
        </form>
     </td>

     </tr>
     <table>
     </div>

  </body>
</html>
