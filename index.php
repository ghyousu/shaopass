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
            var label_name_elem =  document.getElementById("label_name_" + id);

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

      function studentNameSelected(radioBtn)
      {
         var student_id = radioBtn.value;

         if (isStudentCheckedOut(student_id))
         {
            console.log("student " + student_id + " is checked out");
         }
         else
         {
            console.log("student " + student_id + " is NOT checked out");
         }
      }

/*
   {
      function selectAllClicked(e)
      {
         var checkboxes = document.getElementsByName( "check_list[]" );
         var numElems = checkboxes.length;
         for ( i=0; i<numElems; i++ )
         {
            if (checkboxes[i].value.split('.').pop() != "php")
            {
              checkboxes[i].checked = true;
            }
         }
         e.preventDefault(); // don't actually submit
      }

      function unselectAllClicked(e)
      {
         var checkboxes = document.getElementsByName( "check_list[]" );
         var numElems = checkboxes.length;
         for ( i=0; i<numElems; i++ )
         {
            checkboxes[i].checked = false;
         }
         e.preventDefault(); // don't actually submit
      }

      function rename_selected(e)
      {
         var from_str = document.getElementById("from_re").value;
         var to_str   = document.getElementById("to_re").value;

         if (from_str == "")
         {
            alert("You need to specify the 'From' text box");
            e.preventDefault(); // don't actually submit
            return ;
         }

         if (to_str == "")
         {
            alert("You need to specify the 'To' text box");
            e.preventDefault(); // don't actually submit
            return ;
         }

         if (from_str == to_str)
         {
            alert("'From' and 'To' are the same, do nothing");
            e.preventDefault(); // don't actually submit
            document.getElementById("to_re").value = "CHANGE_ME";
            return ;
         }

         // verify there's at least one file checked
         var checkboxes = document.getElementsByName( "check_list[]" );
         var numElems = checkboxes.length;
         var has_file_selected = false;
         for ( i=0; i<numElems; i++ )
         {
            if (checkboxes[i].checked)
            {
                has_file_selected = true;
                break;
            }
         }

         if (!has_file_selected)
         {
            alert("No files are selected. Select some files to be renamed");
            e.preventDefault(); // don't actually submit
         }

         console.log("Renaming files from '" + from_str + "' to '" + to_str + "'");
      }
      }
*/

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
            //       ["submit"]=> string(6) "Submit"
            //    }

            $student_id = $_POST['student_id'];
            $break_type = $_POST['break_type'];
            $pass_type  = $_POST['pass_type'];

            updateStudent($student_id, $break_type, $pass_type);

            // if (isset($_SERVER["HTTP_REFERER"]))
            // {
            //    header("Location: " . $_SERVER["HTTP_REFERER"]);
            // }
         }
     ?>
  </head>

  <body onload="on_page_loaded()">

     <table border=0>
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

           <input type="submit" name="submit" Value="Submit"/>
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
