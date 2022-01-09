<html>
  <head>
     <title>Teacher's Notes</title>

<?php
   require_once("common.php");

   setlocale(LC_ALL,'C.UTF-8');

   session_start();

   function getStartDateFilterHtmlId() { echo 'date_range_start'; }

   function getStopDateFilterHtmlId()  { echo 'date_range_stop'; }

   if ($_SERVER['REQUEST_METHOD'] === 'POST')
   {
      // var_dump($_POST);
      // die("<br/>temp");
      // sample output:
      //    array(2) {
      //       ["notes"]=> string(9) "asdfasdf"
      //       ["submit_notes"]=> string(6) "Submit"
      //    }

      if (isset($_POST['note_checkbox']))
      {
         $note_id_list = $_POST['note_checkbox'];
         deleteNotes($note_id_list);
      }
      else if (isset($_POST['apply_filter']))
      {
         // array(3) { ["date_range_start"]=> string(10) "2021-12-09" ["date_range_stop"]=> string(10) "2022-01-08" ["apply_filter"]=> string(12) "Apply Filter" }
         printDebug("start: " . $_POST['date_range_start']);
         printDebug("stop:  " . $_POST['date_range_stop']);
      }
      else // assume it's note submission from the index page
      {
         $notes = $_POST['notes'];

         enterNotesToDatabase($notes);

         header("location: /index.php");
      }
   }
?>

     <script type="text/javascript">
      function getDateString(date_obj)
      {
         var yyyy = date_obj.getFullYear().toString();
         var mm   = (date_obj.getMonth()+1).toString().padStart(2, '0');
         var dd   = date_obj.getDate().toString().padStart(2, '0');

         var date_str = yyyy + "-" + mm + "-" + dd;

         return date_str;
      }

      function updateDates()
      {
         debugger;
         var start_date_id = "<?php echo getStartDateFilterHtmlId(); ?>"
         var start_date_elem = document.getElementById(start_date_id);
         var stop_date_id = "<?php echo getStopDateFilterHtmlId(); ?>"
         var stop_date_elem = document.getElementById(stop_date_id);

         var today = new Date();
         var thirty_dates_ago = new Date();
         thirty_dates_ago.setDate(thirty_dates_ago.getDate() - 30);

         start_date_elem.value = getDateString(thirty_dates_ago);
         stop_date_elem.value  = getDateString(today);
      }

      function on_page_loaded()
      {
         updateDates();
      }
     </script>

  </head>

<body onload="on_page_loaded()">
<?php if ($_SESSION['user_role'] == "student") : ?>
      <h1 align="center">
          You are not allowed to view this page
      </h1>
<?php else : ?>
      <div align='center'>
      <table border='0' style='width: 60%'>
        <tr>
        <td colspan='2'>
           <a href="/index.php" style="font-size: 1.5em">
            Back to main page
           </a>
        </td>

        <td colspan='2' style='text-align: right;'>
           <a href="/logout.php" style="font-size: 1.5em">
            Log Out
           </a>
        </td>
        </tr>

        <form action='/notes.php' method='POST'>
        <tr>
           <td>
              <br/>
              <label for="start">Date start:</label>
              <input type="date" value="2022-01-30"
                 name="<?php echo getStartDateFilterHtmlId(); ?>"
                 id="<?php echo getStartDateFilterHtmlId(); ?>"
                 min="2021-12-01"
                 max="2050-12-31" />
           </td>

           <td>
              <br/>
              <label for="stop">Date stop:</label>
              <input type="date" value="2023-01-30"
                 name="<?php echo getStopDateFilterHtmlId(); ?>"
                 id="<?php echo getStopDateFilterHtmlId(); ?>"
                 min="2021-12-01"
                 max="2050-12-31" />
           </td>

           <td>
             <br/>
             place holder class drop down filter
           </td>

           <td>
               <br/>
               <input type="submit" name="apply_filter" Value="Apply Filter"/>
           </td>
        </tr>
        </form>
      </table>
      </div>
      <?php
         showNotesTable();
      ?>
<?php endif; ?>
</body>


</html>
