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
   <?php
      showNotesTable();
   ?>
</body>


</html>
