<?php
   if ($_SERVER['REQUEST_METHOD'] == 'POST')
   {
      require_once("../common.php");

      session_start();
      // var_dump($_POST); die("debug");
      // array(1) { ["submit_id_65"]=> string(11) "Alan Teutle" }

      foreach ($_POST as $key => $value) {
         $key_2_array = explode('_', $key);

         if ($key_2_array[0] != 'submit' || $key_2_array[1] != 'id')
         {
            die("unexpected key '$key' from POST array");
         }

         // echo 'debug: student_id: ' . $student_id . ', Name: ' . $value;
         $student_id = $key_2_array[2];

         updateStudentHomeworkTracker($student_id);

         break;
      }

      header("location: /forms/admin_hw_tracker.php");
   }
?>
