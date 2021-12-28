<html>
  <head>
     <title>Student Break Tracker</title>

     <?php require_once("common.php"); ?>

     <script type="text/javascript">

      function updateStudentNameColor()
      {
         var html_elem_id = "<?php echo getHiddenFieldId(); ?>"

         var id_list = document.getElementById(html_elem_id).value.split("_");

         var numElems = id_list.length;

         for (i=0; i<numElems; ++i)
         {
            if (id_list[i] == 0)
            {
               continue; // skip leading 0
            }

            var td_id_name = "td_label_" + id_list[i];
            var td_elem = document.getElementById(td_id_name);
            td_elem.style.backgroundColor = "orange";
         }
      }

      function updateStudentNameInBreakHistory()
      {
         var html_elem_id = "<?php echo getHiddenFieldId(); ?>"

         var id_list = document.getElementById(html_elem_id).value.split("_");

         var numElems = id_list.length;

         for (i=0; i<numElems; ++i)
         {
            id = id_list[i];
            if (id == 0)
            {
               continue; // skip leading 0
            }

            var id_to_name_elem = document.getElementById("id_to_name_" + id);
            var label_name_elem = document.getElementById("label_name_" + id);

            // update the id with actual student's name
            id_to_name_elem.innerHTML = label_name_elem.innerHTML;
         }
      }

      function on_page_loaded()
      {
         updateStudentNameColor();

         updateStudentNameInBreakHistory();
      }

      function isStudentCheckedOut( id )
      {
         var html_elem_id = "<?php echo getHiddenFieldId(); ?>"

         var id_list = document.getElementById(html_elem_id).value.split("_");

         var numElems = id_list.length;

         for (i=0; i<numElems; ++i)
         {
            if (id_list[i] == id)
            {
               return true;
            }
         }

         return false;
      }

      function deselectAllRadioButtons(chk_group_name)
      {
         const chbx = document.getElementsByName(chk_group_name);

         for(let i=0; i < chbx.length; i++)
         {
            chbx[i].checked = false;
         }
      }

      function studentNameSelected(radioBtn)
      {
         var student_id = radioBtn.value;

         if (isStudentCheckedOut(student_id))
         {
            console.log("student " + student_id + " is checked out");

            var break_type_name = document.getElementById("break_type_" + student_id).innerHTML;
            var pass_type_name  = document.getElementById("pass_type_"  + student_id).innerHTML;

            // check the radio buttons
            document.getElementById("break_type_" + break_type_name).checked = true;
            document.getElementById("pass_type_"  + pass_type_name ).checked = true;

            document.getElementById("submit_btn").value = "Check In";
         }
         else
         {
            console.log("student " + student_id + " is NOT checked out");

            deselectAllRadioButtons("break_type");
            deselectAllRadioButtons("pass_type");

            document.getElementById("submit_btn").value = "Check Out";
         }
      }

     </script>

     <?php
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
  </head>

  <body onload="on_page_loaded()">

     <table border=1>
     <tr>

     <td>
        <form action='/index.php' method='POST' enctype='multipart/form-data'>
           <?php
              echo '<h2 style="margin-block-end: -0.5em">Select your name:</h2><br/>';
              displayStudentNamesFromDB();
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
           <input id='submit_btn' type="submit" name="submit" Value="Check Out"/>
           </div>
        </form>
     </td>

     <td style="vertical-align: baseline">
        <h2>Break History: </h2>
        <?php
           displayTodaysHistory("901");
        ?>
     </td>

     </tr>
     <table>
  </body>

</html>
