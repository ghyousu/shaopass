<html>
  <head>
     <title>Student Break Tracker</title>

     <?php
         require_once("common.php");

         setlocale(LC_ALL,'C.UTF-8');

         date_default_timezone_set('America/New_York');

         session_start();

         function getStartDateFilterHtmlId() { return 'start_date_filter'; }
         function getStopDateFilterHtmlId()  { return 'stop_date_filter'; }
         function getClassFilterHtmlId()     { return 'class_drop_down'; }
         function getBreakTypeFilterHtmlId() { return 'break_type_drop_down'; }
         function getFNameFilterHtmlId()     { return 'fname_filter'; }
         function getLNameFilterHtmlId()     { return 'lname_filter'; }
         function getDurationFilterHtmlId()  { return 'duration_filter'; }

         function getStartDateFilterHtmlName() { return getStartDateFilterHtmlId(); }
         function getStopDateFilterHtmlName()  { return getStopDateFilterHtmlId(); }
         function getClassFilterHtmlName()     { return getClassFilterHtmlId(); }
         function getBreakTypeFilterHtmlName() { return getBreakTypeFilterHtmlId(); }
         function getFNameFilterHtmlName()     { return getFNameFilterHtmlId(); }
         function getLNameFilterHtmlName()     { return getLNameFilterHtmlId(); }
         function getDurationFilterHtmlName()  { return getDurationFilterHtmlId(); }

         if (!isset($_SESSION['LOGGED_IN']))
         {
            header("location: /login.php");
         }

         // create default date strings for the session. will be over-written by filtering
         if (!isset($_SESSION[getStartDateSessionKey()]) ||
             !isset($_SESSION[getStopDateSessionKey()]))
         {
            $stop_date_str  = date("Y-m-d");
            $num_days_ago_str = '-' . getDefaultNumberDaysToDisplay() . ' days';
            $start_date_str = date('Y-m-d', strtotime($stop_date_str . $num_days_ago_str));

            $_SESSION[getStartDateSessionKey()] = $start_date_str;
            $_SESSION[getStopDateSessionKey()]  = $stop_date_str;

            printDebug("auto-gen start date: " . $_SESSION[getStartDateSessionKey()] );
            printDebug("auto-gen stop date:  " . $_SESSION[getStopDateSessionKey()] );
         }

         if ($_SERVER['REQUEST_METHOD'] === 'POST')
         {
            // var_dump($_POST);
            // die("<br/>temp");

            if ($_SESSION['user_role'] == 'teacher')
            {
               if (isset($_POST['break_checkbox']) )
               {
                  $break_id_list = $_POST['break_checkbox'];
                  deleteBreaks($break_id_list);
               }
               else if (isset($_POST['apply_filter']))
               {
                  // _POST {
                  //    ["break_date_range_start"]=> string(10) "2021-12-09"
                  //    ["break_date_range_stop"]=> string(10)
                  //    ["class_drop_down"]=> string(10) "901"
                  //    ["fname_filter"]=> string(10) "fname"
                  //    ["lname_filter"]=> string(10) "lname"
                  //    ["duration_filter"]=> string(10) "15"
                  //    "2022-01-08" ["apply_filter"]=> string(12) "Apply Filter" }
                  $_SESSION[getStartDateSessionKey()] = $_POST[getStartDateFilterHtmlName()];
                  $_SESSION[getStopDateSessionKey()]  = $_POST[getStopDateFilterHtmlName()];
                  $_SESSION[getClassFilterSessionKey()]    = $_POST[getClassFilterHtmlName()];
                  $_SESSION[getBreakTypeFilterSessionKey()]= $_POST[getBreakTypeFilterHtmlName()];
                  $_SESSION[getFNameFilterSessionKey()]    = $_POST[getFNameFilterHtmlName()];
                  $_SESSION[getLNameFilterSessionKey()]    = $_POST[getLNameFilterHtmlName()];
                  $_SESSION[getDurationFilterSessionKey()] = $_POST[getDurationFilterHtmlName()];

                  printDebug("filtered start date: " . $_SESSION[getStartDateSessionKey()] );
                  printDebug("filtered stop date:  " . $_SESSION[getStopDateSessionKey()] );
                  printDebug("filtered class id:   " . $_SESSION[getClassFilterSessionKey()] );
                  printDebug("filtered break type: " . $_SESSION[getBreakTypeFilterSessionKey()] );
                  printDebug("filtered fname:      " . $_SESSION[getFNameFilterSessionKey()] );
                  printDebug("filtered lname:      " . $_SESSION[getLNameFilterSessionKey()] );
                  printDebug("filtered duration:   " . $_SESSION[getDurationFilterSessionKey()] );
               }
            }
            else
            {
               // sample _POST
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
         }
     ?>

     <script type="text/javascript">
      // this function returns string 'NA' if key is not found in session store
      function getStartDateFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getStartDateSessionKey()]))
               {
                  echo "'" . $_SESSION[getStartDateSessionKey()] . "';";
               }
               else
               {
                  echo "'NA';";
               }
            ?>

         return value;
      }

      function getFilterClassIdFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getClassFilterSessionKey()]))
               {
                  echo "'" . $_SESSION[getClassFilterSessionKey()] . "';";
               }
               else
               {
                  echo "'All';";
               }
            ?>

         return value;
      }

      function getFilterBreakTypeFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getBreakTypeFilterSessionKey()]))
               {
                  echo "'" . $_SESSION[getBreakTypeFilterSessionKey()] . "';";
               }
               else
               {
                  echo "'All';";
               }
            ?>

         return value;
      }

      function getFilterFNameFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getFNameFilterSessionKey()]))
               {
                  echo "'" . $_SESSION[getFNameFilterSessionKey()] . "';";
               }
               else
               {
                  echo "'';";
               }
            ?>

         return value;
      }

      function getFilterLNameFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getLNameFilterSessionKey()]))
               {
                  echo "'" . $_SESSION[getLNameFilterSessionKey()] . "';";
               }
               else
               {
                  echo "'';";
               }
            ?>

         return value;
      }

      function getFilterDurationFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getDurationFilterSessionKey()]))
               {
                  echo "'" . $_SESSION[getDurationFilterSessionKey()] . "';";
               }
               else
               {
                  echo "'';";
               }
            ?>

         return value;
      }

      function getStopDateFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getStopDateSessionKey()]))
               {
                  echo "'" . $_SESSION[getStopDateSessionKey()] . "';";
               }
               else
               {
                  echo "'NA';";
               }
            ?>

         return value;
      }

      function updateFilter(html_id, stored_value)
      {
         debugger;
         var html_elem = document.getElementById(html_id);

         html_elem.value = stored_value;
      }

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

            // allow unlimited pass type of "Special and L"
            if (pass_type_name == "Special")
            {
               continue;
            }
            if (pass_type_name == "L")
            {
               continue;
            }

            if (pass_type_name != "")
            {
               document.getElementById("pass_type_label_" + pass_type_name).style.color = "lightgray";
               document.getElementById("pass_type_" + pass_type_name).disabled = true;
            }
         }
      }

      function on_page_loaded()
      {
         var user_role = <?php echo "'" . $_SESSION['user_role'] . "'";?>;

         if (user_role == 'teacher')
         {
            updateFilter(
                  "<?php echo getStartDateFilterHtmlId(); ?>",
                  getStartDateFromSession());

            updateFilter(
                  "<?php echo getStopDateFilterHtmlId(); ?>",
                  getStopDateFromSession());

            updateFilter(
                  "<?php echo getClassFilterHtmlId(); ?>",
                  getFilterClassIdFromSession());

            updateFilter(
                  "<?php echo getBreakTypeFilterHtmlId(); ?>",
                  getFilterBreakTypeFromSession());

            updateFilter(
                  "<?php echo getFNameFilterHtmlId(); ?>",
                  getFilterFNameFromSession());

            updateFilter(
                  "<?php echo getLNameFilterHtmlId(); ?>",
                  getFilterLNameFromSession());

            updateFilter(
                  "<?php echo getDurationFilterHtmlId(); ?>",
                  getFilterDurationFromSession());
         }

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
<?php if ($_SESSION['user_role'] == 'student') : ?>
      <td>
         <h1 style='text-align: center'>
            <?php
                echo "Class " . $_SESSION['class_id'];
            ?>
         </h1>
      </td>

      <td align="right">
<?php else : ?>
      <td>
        <a href="/notes.php" style="font-size: 1.5em">
           Teacher's <br/> Notes
        </a>
      </td>

      <td>
        <a href="/comments.php" style="font-size: 1.5em">
           Teacher's <br/> Comments
        </a>
      </td>

      <td>
        <a href="/admin.php" style="font-size: 1.5em">
           Adamin <br/> Page
        </a>
      </td>
<?php endif; ?>
      <td>
           <a href="/logout.php" style="font-size: 1.5em">
              Log Out
           </a>
      </td>
     </tr>

<?php if ($_SESSION['user_role'] == 'teacher') : ?>
     <tr>
        <form action='/index.php' method='POST'>
           <td>
              <br/>
              <label for="start">Date start:</label>
              <input type="date" value="2022-01-30"
                 name="<?php echo getStartDateFilterHtmlId(); ?>"
                 id="<?php echo getStartDateFilterHtmlId(); ?>"
                 min="2021-12-01"
                 max="2050-12-31" />
           </td>

           <td>
              <br/>
              <label for="stop">Date stop:</label>
              <input type="date" value="2023-01-30"
                 name="<?php echo getStopDateFilterHtmlId(); ?>"
                 id="<?php echo getStopDateFilterHtmlId(); ?>"
                 min="2021-12-01"
                 max="2050-12-31" />
           </td>

           <td>
             <br/>
             <?php
               showEnumDropDown(
                     getClassEnumName(),
                     'Classes: ',
                     getClassFilterHtmlId(),
                     getClassFilterHtmlName());
             ?>
           </td>

           <td>
             <br/>
             <?php
               showEnumDropDown(
                     getBreakTypeEnumName(),
                     'Break Types: ',
                     getBreakTypeFilterHtmlId(),
                     getBreakTypeFilterHtmlName());
             ?>
           </td>


     </tr>

     <!-- filter row line two -->
     <tr>
           <td>
               <br/>
               <label for="fname_filter">First Name:</label>
               <input style="width: 120px" type="text"
                  id="<?php echo getFNameFilterHtmlId(); ?>"
                  name="<?php echo getFNameFilterHtmlName(); ?>" >
           </td>

           <td>
               <br/>
               <label for="lname_filter">Last Name:</label>
               <input style="width: 120px" type="text"
                  id="<?php echo getLNameFilterHtmlId(); ?>"
                  name="<?php echo getLNameFilterHtmlName(); ?>" >
           </td>

           <td>
               <br/>
               <label for="duration_filter">Duration(min):</label>
               <input style="width: 120px" type="text"
                  id="<?php echo getDurationFilterHtmlId(); ?>"
                  name="<?php echo getDurationFilterHtmlName(); ?>" >
           </td>

           <td>
               <br/>
               <input style="font-size: 1.3em;" type="submit" name="apply_filter" Value="Apply Filter"/>
           </td>
        </form>
     </tr>
<?php endif; ?>

     <tr>
<?php if ($_SESSION['user_role'] == 'student') : ?>
     <td rowspan="2" style="padding-right: 20px">
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

<?php if ($_SESSION['user_role'] == 'student') : ?>
     <td style="vertical-align: baseline">
<?php else : ?>
     <td colspan='4' style="vertical-align: baseline">
     <br/>
<?php endif; ?>
        <h2>Break History: </h2>
        <?php
           displayBreakHistory($_SESSION['class_id']);
        ?>
     </td>
     </tr>

<?php if ($_SESSION['user_role'] == 'student') : ?>
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
<?php endif; ?>

     </tr>
     </table>
     </div>

  </body>
</html>
