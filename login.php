<!DOCTYPE html>
<html>

<head>
  <title>Login</title>
   <?php
      require_once("common.php");

      function login($username, $pw)
      {
         $user_role = getUserRoleFromDB($username, $pw);
         if ( $user_role != "" )
         {
            $_SESSION['role'] = $user_role;
            return true;
         }
         else
         {
            return false;
         }
      }

      session_start();

      $thisScriptWeb = $_SERVER["SCRIPT_NAME"];
      $main_page     = '/index.php';

      // if ($_SESSION['role'] == 'teacher')
      // {
      //    $main_page = '/teacher_page.php';
      // }

      // echo "debug: thisScriptWeb = $thisScriptWeb <br/>";
      // echo "debug: mai_page = $main_page    <br/>";
      // var_dump( $_SERVER );

      if (isset($_SESSION['LOGGED_IN']))
      {
         header("location: $main_page");
      }
      else if (isset($_POST['username']) && isset($_POST['password']))
      {
         if (login($_POST['username'], $_POST['password']))
         {
            $_SESSION['msg'] = '';
            $_SESSION['LOGGED_IN'] = true;

            header("location: $main_page");
         }
         else
         {
            header("location: $thisScriptWeb");
            $_SESSION['msg'] = 'Bad username/password. Try again.<br/>';
         }
      }
      else
      {
         if (isset($_SESSION['msg']))
         {
            echo $_SESSION['msg'] . '<br/>';
            unset($_SESSION['msg']);
         }
      }
   ?>
</head>

<body>
  <div class="header">
   <h2>Login</h2>
  </div>

  <form method="POST" action="<?php echo $thisScriptWeb ?>">
      <label>Username</label>
      <input type="text" name="username" >
      <label>Password</label>
      <input type="password" name="password">
      <button type="submit" class="btn" name="login_user">Login</button>
  </form>
</body>
</html>
