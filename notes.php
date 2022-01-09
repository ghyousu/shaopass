<html>
  <head>
     <title>Teacher's Notes</title>

<?php
   require_once("common.php");

   setlocale(LC_ALL,'C.UTF-8');

   session_start();

   if ($_SERVER['REQUEST_METHOD'] === 'POST')
   {
      // var_dump($_POST);
      // die("<br/>temp");
      // sample output:
      //    array(2) {
      //       ["notes"]=> string(9) "asdfasdf"
      //       ["submit_notes"]=> string(6) "Submit"
      //    }

      if ( isset($_POST['note_checkbox']) )
      {
         $note_id_list = $_POST['note_checkbox'];
         deleteNotes($note_id_list);
      }
      else // assume it's note submission from the index page
      {
         $notes = $_POST['notes'];

         enterNotesToDatabase($notes);

         header("location: /index.php");
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
      <div align='center'>
      <table border='0' style='width: 60%'>
        <tr>
        <td>
           <a href="/index.php" style="font-size: 1.5em">
            Back to main page
           </a>
        </td>

        <td style='text-align: right;'>
           <a href="/logout.php" style="font-size: 1.5em">
            Log Out
           </a>
        </td>
        </tr>
      </table>
      </div>
      <?php
         showNotesTable();
      ?>
<?php endif; ?>
</body>


</html>
