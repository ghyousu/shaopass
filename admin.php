<html>

<head>
  <title>Admin</title>
   <?php
      require_once("common.php");

      setlocale(LC_ALL,'C.UTF-8');

      date_default_timezone_set('America/New_York');

      session_start();
   ?>

</head>

<body>
   <?php if ($_SESSION['user_role'] != "teacher") : ?>
         <h1 align="center">
             Please log in to a teacher's account to access this page.
         </h1>
   <?php else : ?>
      <table border=1 id='admin_main_table'>
          <?php
             require_once("admin_template.php");
          ?>
      </table>
   <?php endif; ?>

</body>

</html>
