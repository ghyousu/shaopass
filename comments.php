<html>
  <head>
     <title>Teacher's Comments</title>

     <style>
      .cmt_td_reward {
         background-color: cyan;
         font-size: 1.5em;
      }
      .cmt_td_warn   {
         background-color: yellow;
         font-size: 1.5em;
      }
      li {
         float: left;
         padding: 10px;
         list-style: none;
      }
      select[name="comment_type"] {
         font-size: 1.5em;
      }
      select[name="cmt_type_sel"] {
         font-size: 1.5em;
      }
      select[name="cmt_templates_sel"] {
         font-size: 1.5em;
      }
     </style>

<?php
   require_once("common.php");

   setlocale(LC_ALL,'C.UTF-8');

   date_default_timezone_set('America/New_York');

   session_start();

   function getCommentTypeHtmlId() { return 'comment_type'; }

   function getFNameFilterHtmlId()     { return 'cmt_fname_filter'; }
   function getLNameFilterHtmlId()     { return 'cmt_lname_filter'; }
   function getStartDateFilterHtmlId() { return 'comments_date_range_start'; }
   function getStopDateFilterHtmlId()  { return 'comments_date_range_stop'; }
   function getClassFilterHtmlId()     { return 'class_drop_down'; }

   function getRedeemBtnHtmlName()  { return 'redeem_btn'; }
   function getCommentTypeHtmlName()  { return getCommentTypeHtmlId(); }
   function getHiddenStudIdHtmlName() { return 'student_id'; }
   function getCommentTextAreaHtmlName()  { return 'comment_body'; }

   function getFNameFilterHtmlName()     { return getFNameFilterHtmlId(); }
   function getLNameFilterHtmlName()     { return getLNameFilterHtmlId(); }
   function getStartDateFilterHtmlName() { return getStartDateFilterHtmlId(); }
   function getStopDateFilterHtmlName()  { return getStopDateFilterHtmlId(); }
   function getClassFilterHtmlName()     { return getClassFilterHtmlId(); }

   function showWarningRewardTypes($stud_id)
   {
      $enum_array = getEnumArray(getCommentTypeEnumName());

      $html_id = 'cmt_type_' . $stud_id;
      echo "\n\t<select name='cmt_type_sel' id='$html_id' onchange='commentTypeSelected(this);'>\n";

      $num_types = count($enum_array);
      for ($i=0; $i<$num_types; ++$i)
      {
         $val = $enum_array[$i];
         echo "\t\t<option value='$val'>$val</option>\n";
      }

      echo "\n\t</select>\n";
   }

   function showCommentTemplateDropdown($stud_id)
   {
      $cmt_templates = getCommentTemplates();

      $html_id = 'cmt_template_' . $stud_id;
      echo "\n\t<select name='cmt_templates_sel' id='$html_id' onchange='commentTemplateSelected(this);'>\n";

      $num_temps = count($cmt_templates);
      for ($i=0; $i<$num_temps; ++$i)
      {
         $val = $cmt_templates[$i];
         echo "\t\t<option value='$val'>$val</option>\n";
      }

      echo "\t\t<option value='Other'>Other</option>\n";

      echo "\n\t</select>\n";
   }

   function showCommentsPerStudent($stud)
   {
      echo "<table name='table_'" . $stud->student_id . "' border=1>\n";
      echo "<form action='/comments.php' method='POST'>\n";

      // table header
      echo "\t<tr>\n";
      echo "\t\t<th>Class</th>\n";
      echo "\t\t<th>Name</th>\n";
      echo "\t\t<th></th>\n"; // check box if not yet redeemed
      echo "\t\t<th style='width:60px;'>Day of Week</th>\n";
      echo "\t\t<th>Time</th>\n";
      echo "\t\t<th style='width: 400px;'>Comment</th>\n";
      echo "\t</tr>\n";

      $num_comments = count($stud->comments);

      echo "\t<tr>\n";
      // class and student name
      echo "\t\t<td rowspan=" . ($num_comments + 3) . " style='font-size: 1.5em;'> $stud->class </td>\n";
      echo "\t\t<td rowspan=" . ($num_comments + 3) . " style='font-size: 1.5em;'> $stud->fname <br/> $stud->lname </td>\n";
      echo "\t</tr>\n";

      $num_comments = count($stud->comments);
      for ($i=0; $i<$num_comments; ++$i)
      {
         $comment = $stud->comments[$i];

         $td_class_name = 'cmt_td_reward';
         if ($comment->cmt_type == 'warning')
         {
            $td_class_name = 'cmt_td_warn';
         }

         if ($comment->is_active)
         {
            echo "\t<tr>\n";
         }
         else // redeemed
         {
            echo "\t<tr name='inactive_tr'>\n";
         }

         // show checkbox if not yet redeemed
         if ($comment->is_active)
         {
            echo "\t\t<td>\n" .
                 "\t\t\t<input style='width: 30px; height: 30px' type='checkbox' " .
                 "name='cmt_checkbox[]' value='" . $comment->cmt_id . "'>\n" .
                 "</td>\n";
         }
         else
         {
            echo "\t\t<td></td>\n";
         }

         // day of week
         echo "\t\t<td class='$td_class_name' style='text-align:center;'> $comment->cmt_dow </td>\n";

         // full time stamp
         echo "\t\t<td class='$td_class_name'> $comment->full_ts </td>\n";

         // comments
         echo "\t\t<td class='$td_class_name'> $comment->cmt_text </td>\n";

         echo "\t</tr>\n";
      }

      // add Redeem button on a row by itself
      // echo "<tr>\n<td align="center">\n";
      echo "<tr>\n<td colspan=4 >\n";
      echo '<input type="submit" style="font-size: 1.5em" name="' .
           getRedeemBtnHtmlName() . '" value="Redeem"/>' . "\n";
      echo "</td>\n</tr>\n";

      // ---------------------- LAST ROW new warning/reward -------------------------------
      echo "\t<tr>\n";

      echo "\t\t<td colspan=4>\n";

      echo "\t<ul>\n";
      echo "\t<li>\n";
      showWarningRewardTypes($stud->student_id);
      echo "\t</li>\n";

      echo "\t<li>\n";
      echo "\n\t<span style='display:grid'>\n";
      showCommentTemplateDropdown($stud->student_id);

      $cmt_ta_id = 'cmt_ta_' . $stud->student_id;
      echo "\t\t" .
           '<textarea id="' . $cmt_ta_id . '" name="' . getCommentTextAreaHtmlName() .
           '" placeholder="Enter your comment here ..." style="display:none; font-size: 1.5em; width: 500px; height: 150px; resize: none"></textarea>' .
           "\n";
      echo "\n\t</span>\n";
      echo "\n\t</li>\n";

      // add the submit button
      echo "\t<li>\n";
      echo '<input type="submit" style="font-size: 1.5em" ' .
           'name="add_reward_warning" value="Submit"/>' . "\n";
      echo "\t</li>\n";
      echo "\t</ul>\n";

      echo '<input type="hidden" value="' . $stud->student_id . '" ' .
           'name="' . getHiddenStudIdHtmlName() . '" />';

      echo "</td>\n";
      echo "</tr>\n";
      // ---------------------- END OF LAST ROW -------------------------------

      echo "</form>\n";
      echo "</table>\n";
   }

   function showCommentHistory($students)
   {
      echo "<table name='cmt_table_outter' border=1>\n";

      foreach ($students as $id => $stud)
      {
         echo "<tr><td>\n";

         showCommentsPerStudent($stud);

         echo "</td></tr>\n";
      }

      echo "</table>\n";
   }

   if (!isset($_SESSION['LOGGED_IN']))
   {
      header("location: /login.php");
   }

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
         $_SESSION[getCmtFNameFilterSessionKey()] = $_POST[getFNameFilterHtmlName()];
         $_SESSION[getCmtLNameFilterSessionKey()] = $_POST[getLNameFilterHtmlName()];

         printDebug("filtered fname:      " . $_SESSION[getCmtFNameFilterSessionKey()], 0);
         printDebug("filtered lname:      " . $_SESSION[getCmtLNameFilterSessionKey()], 0);
      }
      else if (isset($_POST[getRedeemBtnHtmlName()]))
      {
         $num_cmt_ids = count($_POST['cmt_checkbox']);
         for ($i=0; $i<$num_cmt_ids; ++$i)
         {
            $cmt_id = $_POST['cmt_checkbox'][$i];

            printDebug('comment_id: ' . $cmt_id, 0);

            markCommentsInactive($cmt_id);
         }
      }
      else if (isset($_POST['add_reward_warning']))
      {
         // sample inputs:
         // array(4) {
         //     ["cmt_type_sel"]=> string(7) "warning"
         //     ["cmt_templates_sel"]=> string(7) "Wander Around classroom"
         //     ["comment_body"]=> string(7) "testing"
         //     ["add_reward_warning"]=> string(6) "Submit"
         //     ["student_id"]=> string(2) "95"
         // }
         $stud_id      = $_POST[getHiddenStudIdHtmlName()];
         $comment_type = $_POST['cmt_type_sel'];
         $comment_body = $_POST[getCommentTextAreaHtmlName()];

         if ($comment_type == 'warning' && $_POST['cmt_templates_sel'] != 'Other')
         {
            $comment_body = $_POST['cmt_templates_sel'];
         }

         insertRewardWarning(
               $comment_type, $stud_id, $comment_body);
      }
   }
?>

     <script type="text/javascript">

      function commentTypeSelected(sel_elem)
      {
         // debugger;
         var html_id = sel_elem.id;
         var stud_id = html_id.split("_")[2];

         var sel_option = sel_elem[sel_elem.selectedIndex];

         var sel_text = sel_option.text;

         var cmt_ta_id = 'cmt_ta_' + stud_id;
         var cmt_text_area = document.getElementById(cmt_ta_id);

         var cmt_temp_id = 'cmt_template_' + stud_id;
         var cmt_temp_elem = document.getElementById(cmt_temp_id);

         if (sel_text == "reward") // show the input text area
         {
            cmt_text_area.style.display = "table";
            cmt_temp_elem.style.display = "none";
         }
         else
         {
            cmt_text_area.style.display = "none";
            cmt_temp_elem.style.display = "inline-block";
         }
      }

      function commentTemplateSelected(sel_elem)
      {
         // debugger;
         var html_id = sel_elem.id;
         var stud_id = html_id.split("_")[2];

         var sel_option = sel_elem[sel_elem.selectedIndex];

         var sel_text = sel_option.text;

         var cmt_ta_id = 'cmt_ta_' + stud_id;
         var cmt_text_area = document.getElementById(cmt_ta_id);

         if (sel_text == "Other") // show the input text area
         {
            cmt_text_area.style.display = "inline-block";
         }
         else
         {
            cmt_text_area.style.display = "none";
         }
      }

      function hide_redeemed_rows()
      {
         // debugger;
         var chkbox = document.getElementById('hide_redeemed_chkbox');

         var is_checked = chkbox.checked;

         var redeemed_rows = document.getElementsByName('inactive_tr');

         for (let i=0; i<redeemed_rows.length; i++)
         {
            if (is_checked)
            {
               redeemed_rows[i].style.display = "none";
            }
            else
            {
               redeemed_rows[i].style.display = "table-row";
            }
         }
      }

      function updateFilter(html_id, stored_value)
      {
         // debugger;
         var html_elem = document.getElementById(html_id);

         html_elem.value = stored_value;
      }

      function getFilterFNameFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getCmtFNameFilterSessionKey()]))
               {
                  echo "'" . $_SESSION[getCmtFNameFilterSessionKey()] . "';";
               }
               else
               {
                  echo "'';";
               }
            ?>

         return value;
      }

      function getFilterLNameFromSession()
      {
         var value =
            <?php
               if (isset($_SESSION[getCmtLNameFilterSessionKey()]))
               {
                  echo "'" . $_SESSION[getCmtLNameFilterSessionKey()] . "';";
               }
               else
               {
                  echo "'';";
               }
            ?>

         return value;
      }

      function on_page_loaded()
      {
         updateFilter(
               "<?php echo getFNameFilterHtmlId(); ?>",
               getFilterFNameFromSession());

         updateFilter(
               "<?php echo getLNameFilterHtmlId(); ?>",
               getFilterLNameFromSession());
      }
     </script>

  </head>

<body onload="on_page_loaded()">
<?php if ($_SESSION['user_role'] != "teacher") : ?>
      <h1 align="center">
          Please log in to a teacher's account to access this page.
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
            <label for="hide_redeemed">Hide Redeemed:</label>
            <input style="width: 120px" type="checkbox"
               id="hide_redeemed_chkbox" onclick="hide_redeemed_rows()" />
        </td>

        <td>
            <input type="submit" name="search_name_btn" Value="Search"/>
        </td>
     </tr>
     </form>
   </table>

   <hr/>

   <?php
      $filter_by_name = isset($_SESSION[getCmtFNameFilterSessionKey()]) ||
         isset($_SESSION[getCmtLNameFilterSessionKey()]);

      // display the comment's action table for selected students
      if ($filter_by_name)
      {
         $fname = $_SESSION[getCmtFNameFilterSessionKey()];
         $lname = $_SESSION[getCmtLNameFilterSessionKey()];

         $students = searchCommentsFromDB($fname, $lname);

         if (count($students) == 0)
         {
            echo '<p style="color:red">Did not find any students matching the serach critia. Try again</p>';
         }
         else
         {
            showCommentHistory($students);
         }
      }
   ?>
<?php endif; ?>

</body>

</html>
