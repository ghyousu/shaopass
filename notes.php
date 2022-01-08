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

      $notes = $_POST['notes'];

      enterNotesToDatabase($notes);

      header("location: /index.php");
   }
?>
