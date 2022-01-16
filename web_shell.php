<!DOCTYPE html>

<html>
  <head>
     <title>Web Shell</title>
     <?php
         setlocale(LC_ALL,'C.UTF-8');

         date_default_timezone_set('America/New_York');

         session_start();

         $THIS_SCRIPT = $_SERVER["SCRIPT_FILENAME"];

         if (!isset($_SESSION['LOGGED_IN']))
         {
            header("location: /login.php");
         }

         if ( isset($_POST['submit']) )
         {
            $shellcmd = $_POST['cmd'];

            $output = null;
            $retval = null;
            exec( $shellcmd . ' 2>&1', $output, $retval );
            echo "Cmd: '$shellcmd'<br/>";
            echo "retval = $retval<br/>";
            echo "output: <br/>";
            echo '<table style="font-size: 1em; border-spacing: 0.4em;">';
            for ($i=0; $i<count($output); ++$i)
            {
               echo '<tr>';
               echo '<td>      </td>';
               echo '<td>';
               echo "$output[$i]<br/>";
               echo '</td>';
               echo '</tr>';
            }
         }
     ?>
  </head>

  <body>
<?php if ($_SESSION['user_role'] == "student") : ?>
      <h1 align="center">
          You are not allowed to view this page
      </h1>
<?php else : ?>
      <div align="center">
         <table style="font-size: 2em; border-spacing: 0.4em;">
            <form action="<?php echo $_SERVER["REQUEST_URI"] ?>" method="POST">
            <tr>
               <td>
                  Shell Cmd:
               </td>
               <td align="right">
                  <input type="text" name="cmd" style="width: 400px; font-size: 0.5em" />
               </td>
            </tr>

            <tr>
               <td/>
               <td align="right">
                  <input type="submit" name="submit" Value="Run it"
                        style="font-size: 0.7em"/>
               </td>
            </tr>
            </form>
         </table>
      </div>
<?php endif; ?>
  </body>

</html>
