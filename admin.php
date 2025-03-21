<html>

<head>
  <title>Admin</title>
   <?php
      require_once("common.php");

      setlocale(LC_ALL,'C.UTF-8');

      date_default_timezone_set('America/New_York');

      session_start();

      if (!isset($_SESSION['LOGGED_IN']))
      {
         header("location: /login.php");
      }

      if (isset($_GET['class']))
      {
         $_SESSION[getAdminPageClassSessionKey()] = $_GET['class'];
      }
      else
      {
         if (!isset($_SESSION[getAdminPageClassSessionKey()]))
         {
            $class_name_array = getEnumArray(getClassEnumName());

            $_SESSION[getAdminPageClassSessionKey()] =$class_name_array[0];
         }
      }

      function getClassDropDownName() { return 'class_drop_down_name'; }
      function getClassDropDownId()   { return 'class_drop_down_id'; }

   ?>

   <script type="text/javascript">
      function updateFilter(html_id, stored_value)
      {
         // debugger;
         var html_elem = document.getElementById(html_id);

         html_elem.value = stored_value;
      }

      function classDropDownSelectionChanged()
      {
         // debugger;
         var url = window.location.origin + window.location.pathname;

         var class_drop_down_sel = document.getElementById(<?php echo "'" . getClassDropDownId() . "'"; ?>);

         var selected_class_name = class_drop_down_sel.value;

         var new_url = url + '?class=' + selected_class_name;

         window.location.replace( new_url );
      }

      function getClassDropDownValueFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getAdminPageClassSessionKey()]))
               {
                  echo "'" . $_SESSION[getAdminPageClassSessionKey()] . "';";
               }
               else
               {
                  echo "'NA';";
               }
            ?>

         return value;
      }

      function on_page_loaded()
      {
         var user_role = <?php echo "'" . $_SESSION['user_role'] . "'";?>;
         if (user_role == 'teacher')
         {
            var class_name = getClassDropDownValueFromSession();
            if (class_name != 'NA')
            {
               updateFilter(
                     "<?php echo getClassDropDownId(); ?>",
                     class_name);
            }
         }
      }
   </script>
</head>

<body onload="on_page_loaded()">
   <?php if ($_SESSION['user_role'] != "teacher") : ?>
         <h1 align="center">
             Please log in to a teacher's account to access this page.
         </h1>
   <?php else : ?>
      <div style='padding-top: 30px;padding-left: 10%'>
      <table border=0 id='admin_main_table'>
         <tr>
            <td></td>
            <td style='font-size: 1.5em'>
               <?php
                  $on_change_callback = 'onchange="classDropDownSelectionChanged()"';

                  showEnumDropDown(
                     getClassEnumName(),
                     'Class: ',
                     getClassDropDownName(),
                     getClassDropDownId(),
                     false, // last arg: "show_all"
                     $on_change_callback);
               ?>
            </td>
         </tr>

         <tr>
            <td style='padding-right: 30px'>
               <?php
                  require_once("admin_left_panel.php");
               ?>
            </td>

            <td>
            <?php
               if (isset($_GET['action']))
               {
                  if ($_GET['action'] == 'add_remove')
                  {
                     require_once("admin_add_del_student.php");
                  }
                  else if ($_GET['action'] == 'mod')
                  {
                     require_once("admin_rename_student.php");
                  }
                  else if ($_GET['action'] == 'mov')
                  {
                     require_once("admin_mov_student.php");
                  }
                  else if ($_GET['action'] == 'seat_map')
                  {
                     require_once("admin_gen_seat_map.php");
                  }
                  else if ($_GET['action'] == 'seating')
                  {
                     require_once("admin_change_seat.php");
                  }
                  else if ($_GET['action'] == 'hw_tracker')
                  {
                     require_once("forms/admin_hw_tracker.php");
                  }
                  else // if ($_GET['action'] == 'rainbow') --> default case
                  {
                     require_once("admin_rainbow.php");
                  }
               } // end of isset($_GET['action'])
               else
               {
                  require_once("admin_rainbow.php");
               }
            ?>
            </td>
         </tr>
      </table>
      </div>
   <?php endif; ?>

</body>

</html>
