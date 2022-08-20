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

      function getClassDropDownName() { return 'class_drop_down_name'; }
      function getClassDropDownId()   { return 'class_drop_down_id'; }

   ?>

</head>

<body>
   <?php if ($_SESSION['user_role'] != "teacher") : ?>
         <h1 align="center">
             Please log in to a teacher's account to access this page.
         </h1>
   <?php else : ?>
      <div style='padding-top: 30px;padding-left: 15%'>
      <table border=0 id='admin_main_table'>
         <tr>
            <td></td>
            <td>
               <?php
                  $on_change_callback = 'onchange="self.location=self.location+' .
                     "'?class='" . 'this.selectedIndex"';

                  showEnumDropDown(
                     getClassEnumName(),
                     'Class: ',
                     getClassDropDownName(),
                     getClassDropDownId(),
                     false,
                     $on_change_callback); // last arg: "show_all"
               ?>
            </td>
         </tr>

         <tr>
            <td style='padding-right: 30px'>
               <?php
                  require_once("admin_template.php");
               ?>
            </td>

            <td>
            <?php
               if ($_GET['action'] == 'add_remove')
               {
                  require_once("admin_add_student.php");
               }
               else if ($_GET['action'] == 'mod')
               {
                  require_once("admin_rename_student.php");
               }
               else if ($_GET['action'] == 'mov')
               {
                  require_once("admin_mov_student.php");
               }
               else if ($_GET['action'] == 'seating')
               {
                  require_once("admin_change_seat.php");
               }
               else
               {
                  echo "Unkown action: " . $_GET['action'] . "<br/>\n";
               }
            ?>
            </td>
         </tr>
      </table>
      </div>
   <?php endif; ?>

</body>

</html>
