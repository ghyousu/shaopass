<html>
  <head>
     <title>Teacher's Notes</title>

<?php
   require_once("common.php");

   setlocale(LC_ALL,'C.UTF-8');

   date_default_timezone_set('America/New_York');

   session_start();

   function getStartDateFilterHtmlId() { return 'notes_date_range_start'; }
   function getStopDateFilterHtmlId()  { return 'notes_date_range_stop'; }
   function getClassFilterHtmlId()     { return 'class_drop_down'; }

   function getStartDateFilterHtmlName() { return getStartDateFilterHtmlId(); }
   function getStopDateFilterHtmlName()  { return getStopDateFilterHtmlId(); }
   function getClassFilterHtmlName()     { return getClassFilterHtmlId(); }

   if (!isset($_SESSION['LOGGED_IN']))
   {
      header("location: /login.php");
   }

   // create default date strings for the session. will be over-written by filtering
   if (!isset($_SESSION[getNotesStartDateSessionKey()]) ||
       !isset($_SESSION[getNotesStopDateSessionKey()]))
   {
      $stop_date_str  = date("Y-m-d");
      $num_days_ago_str = '-' . getDefaultNumberDaysToDisplay() . ' days';
      $start_date_str = date('Y-m-d', strtotime($stop_date_str . $num_days_ago_str));

      $_SESSION[getNotesStartDateSessionKey()] = $start_date_str;
      $_SESSION[getNotesStopDateSessionKey()]  = $stop_date_str;

      printDebug("auto-gen start date: " . $_SESSION[getNotesStartDateSessionKey()] );
      printDebug("auto-gen stop date:  " . $_SESSION[getNotesStopDateSessionKey()] );
   }

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
         $_SESSION[getNotesStartDateSessionKey()]   = $_POST[getStartDateFilterHtmlName()];
         $_SESSION[getNotesStopDateSessionKey()]    = $_POST[getStopDateFilterHtmlName()];
         $_SESSION[getNotesClassFilterSessionKey()] = $_POST[getClassFilterHtmlName()];

         printDebug("filtered start date: " . $_SESSION[getNotesStartDateSessionKey()] );
         printDebug("filtered stop date:  " . $_SESSION[getNotesStopDateSessionKey()] );
         printDebug("filtered class id:   " . $_SESSION[getNotesClassFilterSessionKey()] );
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

      // this function returns string 'NA' if key is not found in session store
      function getStartDateFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getNotesStartDateSessionKey()]))
               {
                  echo "'" . $_SESSION[getNotesStartDateSessionKey()] . "';";
               }
               else
               {
                  echo "'NA';";
               }
            ?>

         return value;
      }

      function getStopDateFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getNotesStopDateSessionKey()]))
               {
                  echo "'" . $_SESSION[getNotesStopDateSessionKey()] . "';";
               }
               else
               {
                  echo "'NA';";
               }
            ?>

         return value;
      }

      function getFilterClassIdFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getNotesClassFilterSessionKey()]))
               {
                  echo "'" . $_SESSION[getNotesClassFilterSessionKey()] . "';";
               }
               else
               {
                  echo "'All';";
               }
            ?>

         return value;
      }

      function updateFilter(html_id, stored_value)
      {
         debugger;
         var html_elem = document.getElementById(html_id);

         html_elem.value = stored_value;
      }

      function on_page_loaded()
      {
         updateFilter(
               "<?php echo getStartDateFilterHtmlId(); ?>",
               getStartDateFromSession());

         updateFilter(
               "<?php echo getStopDateFilterHtmlId(); ?>",
               getStopDateFromSession());

         updateFilter(
               "<?php echo getClassFilterHtmlId(); ?>",
               getFilterClassIdFromSession());
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
        <td>
           <a href="/index.php" style="font-size: 1.5em">
             Breaks History
           </a>
        </td>

        <td>
           <a href="/comments.php" style="font-size: 1.5em">
             Teacher's Comments
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
             <?php
               showEnumDropDown(
                     getClassEnumName(),
                     'Classes: ',
                     getClassFilterHtmlId(),
                     getClassFilterHtmlName());
             ?>
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
         $start_date_str = $_SESSION[getNotesStartDateSessionKey()];
         $stop_date_str  = $_SESSION[getNotesStopDateSessionKey()];

         printDebug("date range: " . $start_date_str . " to " . $stop_date_str);

         showNotesTable($start_date_str, $stop_date_str);
      ?>
<?php endif; ?>
</body>

</html>
