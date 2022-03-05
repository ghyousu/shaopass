<html>
  <head>
     <title>Teacher's Comments</title>

<?php
   require_once("common.php");

   setlocale(LC_ALL,'C.UTF-8');

   date_default_timezone_set('America/New_York');

   session_start();

   function getCommentTypeHtmlId() { return 'comment_type'; }

   function getFNameFilterHtmlId()     { return 'fname_filter'; }
   function getLNameFilterHtmlId()     { return 'lname_filter'; }
   function getStartDateFilterHtmlId() { return 'comments_date_range_start'; }
   function getStopDateFilterHtmlId()  { return 'comments_date_range_stop'; }
   function getClassFilterHtmlId()     { return 'class_drop_down'; }

   function getCommentTypeHtmlName()  { return getCommentTypeHtmlId(); }
   function getCommentTextAreaHtmlName()  { return 'comment_body'; }
   function getHiddenStudIdHtmlName() { return 'stud_id_for_comment'; }

   function getFNameFilterHtmlName()     { return getFNameFilterHtmlId(); }
   function getLNameFilterHtmlName()     { return getLNameFilterHtmlId(); }
   function getStartDateFilterHtmlName() { return getStartDateFilterHtmlId(); }
   function getStopDateFilterHtmlName()  { return getStopDateFilterHtmlId(); }
   function getClassFilterHtmlName()     { return getClassFilterHtmlId(); }

   function showAddRewardWarningTable($stud)
   {
      echo "<table border=1>\n";
      echo "<form action='/comments.php' method='POST'>\n";

      // table header
      echo "\t<tr>\n";
      echo "\t\t<th>Name</th>\n";
      echo "\t\t<th>Type</th>\n";
      echo "\t\t<th>Comment</th>\n";
      echo "\t</tr>\n";

      // student name
      echo "\t<tr>\n";
      echo "\t\t<td style='font-size: 1.5em;'> $stud->fname $stud->lname </td>\n";

      // comment types (enum from database)
      echo "\t\t<td>\n";
      showEnumDropDown(
            getCommentTypeEnumName(),
            '', // empty label
            getCommentTypeHtmlName(),
            getCommentTypeHtmlId(),
            false); // don't show "All" option
      echo "\t\t</td>\n";

      // comment text area
      echo "\t\t<td>\n";
      echo "\t\t\t" .
           '<textarea name="' . getCommentTextAreaHtmlName() .
           '" placeholder="Enter your comment here ..." style="font-size: 1.5em; width: 500px; height: 150px; resize: none"></textarea>' .
           "\n";
      echo "\t\t</td>\n";

      echo "\t</tr>\n";

      // add the submit button
      echo "<tr>\n";
      echo "<td/><td/>\n"; // myou: not sure why colspan is not working here
      echo "<td>\n";
      echo '<div align="right"><input type="submit" style="font-size: 1.5em" name="add_reward_warning" value="Submit"/></div>' . "\n";
      echo "</td></tr>\n";

      // hidden student id that's not being displayed
      echo '<input type="hidden" name="' . getHiddenStudIdHtmlName() . '" value="' . $stud->student_id . '">';

      echo "</form>\n";
      echo "</table>\n";
   }


   if (!isset($_SESSION['LOGGED_IN']))
   {
      header("location: /login.php");
   }
/*
   // create default date strings for the session. will be over-written by filtering
   if (!isset($_SESSION[getCommentsStartDateSessionKey()]) ||
       !isset($_SESSION[getCommentsStopDateSessionKey()]))
   {
      $stop_date_str  = date("Y-m-d");
      $num_days_ago_str = '-' . getDefaultNumberDaysToDisplay() . ' days';
      $start_date_str = date('Y-m-d', strtotime($stop_date_str . $num_days_ago_str));

      $_SESSION[getCommentsStartDateSessionKey()] = $start_date_str;
      $_SESSION[getCommentsStopDateSessionKey()]  = $stop_date_str;

      printDebug("auto-gen start date: " . $_SESSION[getCommentsStartDateSessionKey()] );
      printDebug("auto-gen stop date:  " . $_SESSION[getCommentsStopDateSessionKey()] );
   }
*/
   if ($_SERVER['REQUEST_METHOD'] === 'POST')
   {
      // var_dump($_POST);
      // die("<br/>temp");
      // sample output:
      //    array(3) {
      //       ["fname_filter"]=> string(9) "asdfasdf"
      //       ["lname_filter"]=> string(0) "asdfasdf"
      //       ["search_name_btn"]=> string(6) "Search"
      //    }

      if (isset($_POST['search_name_btn']))
      {
         // the processing is moved down to the "body" so
         // table can be displayed properly on the page
      }
      else if (isset($_POST['add_reward_warning']))
      {
         // sample inputs:
         // array(4) {
         //     ["comment_type"]=> string(7) "warning"
         //     ["comment"]=> string(7) "testing"
         //     ["add_reward_warning"]=> string(6) "Submit"
         //     ["stud_id_for_comment"]=> string(2) "95"
         // }
         insertRewardWarning(
               $_POST[getCommentTypeHtmlName()],
               $_POST[getHiddenStudIdHtmlName()],
               $_POST[getCommentTextAreaHtmlName()]);
      }
/*
      else if (isset($_POST['note_checkbox']))
      {
         $note_id_list = $_POST['note_checkbox'];
         deleteNotes($note_id_list);
      }
      else if (isset($_POST['apply_filter']))
      {
         // array(3) { ["date_range_start"]=> string(10) "2021-12-09" ["date_range_stop"]=> string(10) "2022-01-08" ["apply_filter"]=> string(12) "Apply Filter" }
         $_SESSION[getCommentsStartDateSessionKey()]   = $_POST[getStartDateFilterHtmlName()];
         $_SESSION[getCommentsStopDateSessionKey()]    = $_POST[getStopDateFilterHtmlName()];
         $_SESSION[getCommentsClassFilterSessionKey()] = $_POST[getClassFilterHtmlName()];

         printDebug("filtered start date: " . $_SESSION[getCommentsStartDateSessionKey()] );
         printDebug("filtered stop date:  " . $_SESSION[getCommentsStopDateSessionKey()] );
         printDebug("filtered class id:   " . $_SESSION[getCommentsClassFilterSessionKey()] );
      }
      else // assume it's note submission from the index page
      {
         $notes = $_POST['notes'];

         enterNotesToDatabase($notes);

         header("location: /index.php");
      }
*/
   }
?>

     <script type="text/javascript">
      // function getDateString(date_obj)
      // {
      //    var yyyy = date_obj.getFullYear().toString();
      //    var mm   = (date_obj.getMonth()+1).toString().padStart(2, '0');
      //    var dd   = date_obj.getDate().toString().padStart(2, '0');

      //    var date_str = yyyy + "-" + mm + "-" + dd;

      //    return date_str;
      // }

      // this function returns string 'NA' if key is not found in session store
      // function getStartDateFromSession()
      // {
      //    var value =
      //       <?php
      //          if (isset($_SESSION[getCommentsStartDateSessionKey()]))
      //          {
      //             echo "'" . $_SESSION[getCommentsStartDateSessionKey()] . "';";
      //          }
      //          else
      //          {
      //             echo "'NA';";
      //          }
      //       ?>

      //    return value;
      // }

      // function getStopDateFromSession()
      // {
      //    var value =
      //       <?php
      //          if (isset($_SESSION[getCommentsStopDateSessionKey()]))
      //          {
      //             echo "'" . $_SESSION[getCommentsStopDateSessionKey()] . "';";
      //          }
      //          else
      //          {
      //             echo "'NA';";
      //          }
      //       ?>

      //    return value;
      // }

      // function getFilterClassIdFromSession()
      // {
      //    var value =
      //       <?php
      //          if (isset($_SESSION[getCommentsClassFilterSessionKey()]))
      //          {
      //             echo "'" . $_SESSION[getCommentsClassFilterSessionKey()] . "';";
      //          }
      //          else
      //          {
      //             echo "'All';";
      //          }
      //       ?>

      //    return value;
      // }

      // function updateFilter(html_id, stored_value)
      // {
      //    debugger;
      //    var html_elem = document.getElementById(html_id);

      //    html_elem.value = stored_value;
      // }

      // function on_page_loaded()
      // {
      //    updateFilter(
      //          "<?php echo getStartDateFilterHtmlId(); ?>",
      //          getStartDateFromSession());

      //    updateFilter(
      //          "<?php echo getStopDateFilterHtmlId(); ?>",
      //          getStopDateFromSession());

      //    updateFilter(
      //          "<?php echo getClassFilterHtmlId(); ?>",
      //          getFilterClassIdFromSession());
      // }
     </script>

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
            Breaks History
           </a>
        </td>

        <td>
           <a href="/notes.php" style="font-size: 1.5em">
             Teacher's Notes
           </a>
        </td>

        <td colspan='2' style='text-align: right;'>
           <a href="/logout.php" style="font-size: 1.5em">
            Log Out
           </a>
        </td>
        </tr>
      </table>
   </div>

   <div align='center'>
<!-- student name search box -->
   <table border='0' style='width: 60%'>
     <br/>
     <hr/>
     <form action='/comments.php' method='POST'>
     <tr>
        <td>
            <label for="fname_filter">First Name:</label>
            <input style="width: 120px" type="text"
               id="<?php echo getFNameFilterHtmlId(); ?>"
               name="<?php echo getFNameFilterHtmlName(); ?>" >
        </td>

        <td>
            <label for="lname_filter">Last Name:</label>
            <input style="width: 120px" type="text"
               id="<?php echo getLNameFilterHtmlId(); ?>"
               name="<?php echo getLNameFilterHtmlName(); ?>" >
        </td>

        <td>
            <input type="submit" name="search_name_btn" Value="Search"/>
        </td>
     </tr>
     </form>
   </table>

   <?php
      // display the comment's action table for selected students
      if (isset($_POST['search_name_btn']))
      {
         $fname = $_POST['fname_filter'];
         $lname = $_POST['lname_filter'];

         $students = searchStudents($fname, $lname);

         if (count($students) == 0)
         {
            echo '<p style="color:red">Did not find any students matching the serach critia. Try again</p>';
         }
         else if (count($students) > 1)
         {
            echo '<p style="color:red">Found multiple students matching the search critia. Please narrow down your search to a single student</p>';
         }
         else
         {
            showAddRewardWarningTable($students[0]);
         }
      }
   ?>

<?php if (!isset($_POST['search_name_btn'])) : ?>
<!-- date filtering -->
        <form action='/comments.php' method='POST'>
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
         $start_date_str = $_SESSION[getCommentsStartDateSessionKey()];
         $stop_date_str  = $_SESSION[getCommentsStopDateSessionKey()];

         printDebug("date range: " . $start_date_str . " to " . $stop_date_str);

         showCommentsTable($start_date_str, $stop_date_str);
      ?>
<?php endif; ?> <!-- end of if "search_name_btn" -->
<?php endif; ?>
</body>

</html>
