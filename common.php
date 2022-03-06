<?php

function printDebug($str, $debug = 0)
{
   if ($debug == 1)
   {
      echo "Debug: $str <br/>";
   }
}

// use public schema for now, can be renamed to other names if needed
function getCommonSchemaName() { return 'common'; }

// common schema tables
function getUsersTableName() { return getCommonSchemaName() . ".users"; }
function getStudentTableName() { return getCommonSchemaName() . "." . "student"; }

// enum types in common schema
function getClassEnumName() { return getCommonSchemaName() . ".youclassname"; }
function getCommentTypeEnumName() { return getCommonSchemaName() . ".commentType"; }

function getIndividualSchemaName() { return $_SESSION['schema_name']; }

// enum types in individual schemas
function getBreakTypeEnumName() { return getIndividualSchemaName() . ".youbreaktype"; }
function getPassTypeEnumName() { return getIndividualSchemaName() . ".youpasstype"; }

// table names in individual schemas
function getNotesTableName() { return getIndividualSchemaName() . "." . "notes"; }
function getBreaksTableName() { return getIndividualSchemaName() . "." . "breaks"; }
function getSeatingTableName() { return getIndividualSchemaName() . "." . "seating"; }
function getCommentsTableName() { return getIndividualSchemaName() . "." . "teacherComment"; }

function getStudentNameChkboxHtmlId($id) { return 'student_id_' . $id; }

function getHiddenFieldId() { return 'checkedout_student_ids'; }

function getBreakIdSessionKey($student_id) { return "break_id_" . $student_id; }

function getDefaultNumberDaysToDisplay() { return 0; }
function getNotesStartDateSessionKey()   { return 'notes_date_start'; }
function getNotesStopDateSessionKey()    { return 'notes_date_stop'; }
function getNotesClassFilterSessionKey() { return 'notes_class_id'; }
function getCommentsStartDateSessionKey()   { return 'comments_date_start'; }
function getCommentsStopDateSessionKey()    { return 'comments_date_stop'; }
function getCommentsClassFilterSessionKey() { return 'comments_class_id'; }

// session variable keys
function getStartDateSessionKey()       { return 'filter_date_start'; }
function getStopDateSessionKey()        { return 'filter_date_stop'; }
function getClassFilterSessionKey()     { return 'filter_class_id'; }
function getBreakTypeFilterSessionKey() { return 'filter_break_type'; }
function getFNameFilterSessionKey()     { return 'filter_fname'; }
function getLNameFilterSessionKey()     { return 'filter_lname'; }
function getDurationFilterSessionKey()  { return 'filter_duration'; }

class tcStudent
{
   public $student_id = 0;
   public $fname = "";
   public $lname = "";
   public $class = "";
}

// return a working connection, caller is responsible to close
// connection when done
function getDBConnection()
{
   $db_url = getenv("DATABASE_URL");

   if ($db_url == "") { die("Unable to get database URL!"); }

   $conn = pg_connect($db_url);

   if ($conn)
   {
      return $conn;
   }
   else
   {
      die("Failed to connect to database<br/>");
   }
}

function fetchQueryResults($query)
{
   $conn = getDBConnection();

   printDebug("query: '$query'<br/>");
   $result = pg_query($conn, $query);

   if ($result == false)
   {
      pg_close($conn);
      die('Failed to query from database');
   }

   pg_close($conn);
   return $result;
}

function authenticateUser($username, $pw)
{
   $query = "SELECT role,auth_class,schema_name from " . getUsersTableName() .
            " WHERE user_name = '$username' AND pw = '" . sha1($pw) . "'";

   printDebug("query: '$query'");

   $result = fetchQueryResults($query);

   if ($result == false)
   {
      die("Failed to get user info from database <br/>");
   }
   else
   {
      $row  = pg_fetch_row($result);
      $role = $row[0];
      $auth_class = $row[1];
      $schema_name= $row[2];

      $_SESSION['user_role'] = $role;
      $_SESSION['user_name'] = $username;
      $_SESSION['schema_name'] = $schema_name;

      if ($role == '')
      {
         return false; // failed to find login into
      }
      else if ($role == 'student')
      {
         // this is only applicable to student account
         $_SESSION['class_id']  = $auth_class;
      }

      return true;
   }
}

function checkoutStudent($student_id, $break_type, $pass_type)
{
   $insert_query = "INSERT INTO " . getBreaksTableName() . " (student_id, break_type, pass_type) " .
      "VALUES ('$student_id', '$break_type', '$pass_type') RETURNING break_id";

   $result = fetchQueryResults($insert_query);

   if ($result == false)
   {
      die("Failed to write to database <br/>");
   }
   else
   {
      $break_id = pg_fetch_row($result)[0];
      printDebug("successfully inserted break id: $break_id <br/>");
   }

   return $break_id;
}

function checkinStudent($student_id, $break_id)
{
   $update_query = "UPDATE " . getBreaksTableName() . " SET time_in = NOW() " .
                   "WHERE break_id = " . $break_id;

   printDebug($update_query);

   $result = fetchQueryResults($update_query);

   if ($result == false)
   {
      die("Failed to check in student with id: $student_id and break_id: $break_id<br/>");
   }
}

function deleteBreaks($break_id_list)
{
   $del_query = "DELETE FROM " . getBreaksTableName() . " WHERE break_id in (";

   $num_ids = count($break_id_list);

   $id_list_str = "";
   for ($i=0; $i<$num_ids; $i++)
   {
      $id = $break_id_list[$i];
      $id_list_str = $id_list_str . $id . ",";
   }
   // remove the last character
   $id_list_str = substr($id_list_str, 0, -1);

   $del_query = $del_query . $id_list_str . ")";

   fetchQueryResults($del_query);
}

function enterNotesToDatabase($notes)
{
   $insert_query = "INSERT INTO " . getNotesTableName() . " (note_body, class) " .
      "VALUES ('" . $notes . "', '" . $_SESSION['class_id'] . "')";

   $result = fetchQueryResults($insert_query);

   if ($result == false)
   {
      echo 'You entered: <br/>';
      echo $notes . '<br/>';
      die("Failed to enter notes to database <br/>");
   }
}

function deleteNotes($note_id_list)
{
   $del_query = "DELETE FROM " . getNotesTableName() . " WHERE note_id in (";

   $num_ids = count($note_id_list);

   $id_list_str = "";
   for ($i=0; $i<$num_ids; $i++)
   {
      $id = $note_id_list[$i];
      $id_list_str = $id_list_str . $id . ",";
   }
   // remove the last character
   $id_list_str = substr($id_list_str, 0, -1);

   $del_query = $del_query . $id_list_str . ")";

   fetchQueryResults($del_query);
}

function getMaxColumns()
{
   $query = "SELECT max(col) FROM " . getSeatingTableName();

   $result = fetchQueryResults($query);

   while ( $res = pg_fetch_row($result) )
   {
      $num_cols = $res[0];

      return $num_cols;
   }
}

function getActiveWarningsRewards($class, $cmt_type)
{
   $id_warn_map = array();

   $get_teacher_name_query = 'SELECT user_name FROM ' . getUsersTableName() .
         " WHERE role = 'teacher' AND schema_name = " .
         '(SELECT schema_name FROM ' . getUsersTableName() .
         " WHERE user_name = '" . $_SESSION['user_name'] . "')";

   $query = 'SELECT c.student_id, count(c.student_id) AS count FROM ' .
            getStudentTableName() . ' s, ' . getCommentsTableName() .
            " c WHERE s.student_id = c.student_id AND s.class = '" . $class . "' " .
            "AND is_active = 't' AND c.cmt_type = '" . $cmt_type . "' " .
            "AND c.teacher_name = (" . $get_teacher_name_query . ") GROUP BY c.student_id";

   printDebug($query);

   $result = fetchQueryResults($query);

   if ($result == false)
   {
      die("Failed to get active warnings for students. <br/>");
   }
   else
   {
      while ( $res = pg_fetch_row($result) )
      {
         $stud_id  = $res[0];
         $num_warn = $res[1];

         $id_warn_map[$stud_id] = $num_warn;
      }
   }

   return $id_warn_map;
}

function displayStudentNamesFromDB($class)
{
   $active_warnings_map = getActiveWarningsRewards($class, 'warning');
   $active_rewards_map  = getActiveWarningsRewards($class, 'reward');

   $NUM_COLUMNS = getMaxColumns();
   printDebug("NUM_COLUMNS = $NUM_COLUMNS <br/>");

   $query = "SELECT s.student_id, s.fname, s.lname, t.row, t.col FROM " .
            getStudentTableName() . " s, " .
            getSeatingTableName() . " t " .
            "WHERE s.class = '$class' AND s.student_id = t.student_id " .
            "ORDER BY t.row, t.col";

   $students = fetchQueryResults($query);

   echo "\n<table border='1' class='studentNamesTable'>\n";

   $html_string_array = array();

   $tr_idx = 0;
   $tc_idx = 1;
   $tr_data = "";
   $loop_counter = 1;
   while ( $student = pg_fetch_row($students) )
   {
      ++$loop_counter;

      $row_fully_closed = false;
      $id = $student[0];

      // $name = $student[1] . "<br/>" . $student[2];
      $name = $student[1] . " " . substr($student[2], 0, 1) . ".";
      // $name = $student[1];

      $db_row = $student[3];
      $db_col = $student[4];

      printDebug("id: $id, name: '$name', row: '$db_row', col: '$db_col'");

      // open a new table row
      if ($tr_idx != $db_row)
      {
         if ($tr_data != "")
         {
            $tr_data = $tr_data . "</tr>\n";
            array_push($html_string_array, $tr_data);
         }

         $tr_data = "<tr>\n"; // new table row data

         $tr_idx = $db_row;
         $tc_idx = 1;
      }

      // empty seat, fill in a blank cell
      while ($tc_idx < $db_col)
      {
         $tr_data = $tr_data . "<td/>\n";
         $tc_idx += 1;
      }

      $html_input_prefix = "<input type='radio' name='student_id' ";
      $html_input_id = getStudentNameChkboxHtmlId($id);

      $num_warn = 0;
      $num_rewards = 0;
      if (array_key_exists( $id, $active_warnings_map))
      {
         $num_warn = $active_warnings_map[$id];
      }
      if (array_key_exists( $id, $active_rewards_map))
      {
         $num_rewards = $active_rewards_map[$id];
      }
      $tr_data = $tr_data . "<td id='td_label_" . $id . "' style='padding-bottom: 0px; padding-right: 5px;'>\n";
      $tr_data = $tr_data . "$html_input_prefix id='$html_input_id' value='$id' onchange='studentNameSelected(this)' />\n";
      $tr_data = $tr_data . '<strong><span style="color:white;background-color:red;font-size:1.5em;float:right">' . $num_warn . '</strong></span>';
      $tr_data = $tr_data . "<label style='font-size: 1.5em' for='$html_input_id'><br/>$name</label>\n";
      $tr_data = $tr_data . '<br/><strong><span style="color:white;background-color:purple;font-size:1.5em;float:right">' . $num_rewards . '</span></strong>';
      $tr_data = $tr_data . "</td>\n";

      $tc_idx += 1;

      // last column of the row, close the table row
      if ( $db_col == $NUM_COLUMNS )
      {
         $tr_data = $tr_data . "</tr>\n";

         array_push($html_string_array, $tr_data);

         $tr_data = "";
         $tr_idx = $db_row;
         $tc_idx = 1;
      }

      if ($loop_counter > 500)
      {
         die("exceeding maximum loop count, something must have gone wrong");
      }
   }

   // if last row doesn't have enough columns, close the tr tag
   if ($tr_data != "")
   {
      $tr_data = $tr_data . "</tr>\n";

      array_push($html_string_array, $tr_data);
   }

   $reversed_array = array_reverse($html_string_array);

   for ($i=0; $i<count($reversed_array); ++$i)
   {
      echo $reversed_array[$i];
   }

   echo "</table>\n";
}

function getEnumArray($db_enum_name)
{
   $query = 'SELECT unnest(enum_range(NULL::' . $db_enum_name . '))';

   $enum_array = array();

   $enum_values = fetchQueryResults($query);

   while ( $enum_val = pg_fetch_row($enum_values) )
   {
      $value = $enum_val[0];
      array_push($enum_array, $value);
   }

   return $enum_array;
}

function displayBreakTypes()
{
   $query = "SELECT unnest(enum_range(NULL::" . getBreakTypeEnumName() . "))";

   $break_types = fetchQueryResults($query);

   echo "<table class='breakTypesTable'>\n";
   echo "<tr>\n";

   while ( $break_type = pg_fetch_row($break_types) )
   {
      $value = $break_type[0];
      $html_input_prefix = "<input type='radio' name='break_type' ";
      $html_input_id = 'break_type_' . $value;

      echo "<td style='padding-bottom: 3%'>\n";
      echo "$html_input_prefix id='$html_input_id' value='$value' />\n";
      echo "<label style='font-size: 2.0em; margin-right: 30px;' for='$html_input_id'>$value</label>\n";
      echo "</td>\n";
   }

   echo "</tr>\n";
   echo "</table>\n";
}

function displayPassTypes()
{
   $query = "SELECT unnest(enum_range(NULL::" . getPassTypeEnumName() . "))";

   $pass_types = fetchQueryResults($query);

   echo "<table class='passTypesTable'>\n";
   echo "<tr>\n";

   while ( $pass_type = pg_fetch_row($pass_types) )
   {
      $value = $pass_type[0];
      $html_input_prefix = "<input type='radio' name='pass_type' ";
      $html_input_id = 'pass_type_' . $value;
      $html_label_id = 'pass_type_label_' . $value;

      echo "<td style='padding-bottom: 3%'>\n";
      echo "$html_input_prefix id='$html_input_id' value='$value' />\n";
      echo "<label id='$html_label_id' style='font-size: 2.0em; margin-right: 30px;' for='$html_input_id'>$value</label>\n";
      echo "</td>\n";
   }

   echo "</tr>\n";
   echo "</table>\n";
}

// duration's in HH:MM:SS format.
function hhmmssToSeconds( $durationHms )
{
   $hms_array = explode(":", $durationHms);
   $hh = $hms_array[0];
   $mm = $hms_array[1];
   $ss = $hms_array[2];

   return $hh * 3600 + $mm * 60 + $ss;
}

// if hour is "00", only show "MM:SS"
function getHmsForDisplay( $durationHms )
{
   $hms_array = explode(":", $durationHms);
   $hh = $hms_array[0];
   $mm = $hms_array[1];
   $ss = $hms_array[2];

   if ($hh == "00")
   {
      return $mm . ":" . $ss;
   }

   return $durationHms;
}

// duration's in HH:MM:SS format.
//   color coding:
//     *  0-15 mins -> no color
//     * 15-20 mins -> yellow
//     * longer than 20 mins -> red
function getDurationHtmlStyleBgcolor( $durationHms )
{
   $duration_in_secs = hhmmssToSeconds($durationHms);

   if ($duration_in_secs >= 20 * 60)
   {
      $html_style = "style='text-align: center; background-color: red'";
   }
   else if ($duration_in_secs >= 15 * 60)
   {
      $html_style = "style='text-align: center; background-color: yellow'";
   }
   else // under 15 minutes
   {
      $html_style = "style='text-align: center'";
   }

   return $html_style;
}

// NOTE: This function assumes the student table name alias is "s"
function getFilteringClause()
{
   $filter_clause = "";

   if (isset($_SESSION[getClassFilterSessionKey()]) &&
         $_SESSION[getClassFilterSessionKey()] != "All")
   {
      $filter_clause = $filter_clause .
         " AND s.class = '" . $_SESSION[getClassFilterSessionKey()] . "'";
   }

   if (isset($_SESSION[getBreakTypeFilterSessionKey()]) &&
         $_SESSION[getBreakTypeFilterSessionKey()] != "All")
   {
      $filter_clause = $filter_clause .
         " AND b.break_type = '" . $_SESSION[getBreakTypeFilterSessionKey()] . "'";
   }

   if (isset($_SESSION[getFNameFilterSessionKey()]) &&
         $_SESSION[getFNameFilterSessionKey()] != '')
   {
      $filter_clause = $filter_clause .
         " AND s.fname ILIKE '%" . $_SESSION[getFNameFilterSessionKey()] . "%'";
   }

   if (isset($_SESSION[getLNameFilterSessionKey()]) &&
         $_SESSION[getLNameFilterSessionKey()] != '')
   {
      $filter_clause = $filter_clause .
         " AND s.lname ILIKE '%" . $_SESSION[getLNameFilterSessionKey()] . "%'";
   }

   if (isset($_SESSION[getDurationFilterSessionKey()]) &&
         $_SESSION[getDurationFilterSessionKey()] != '')
   {
      $filter_clause = $filter_clause .
         " AND age(b.time_in, b.time_out) >= '" .
         $_SESSION[getDurationFilterSessionKey()] . "minutes'::interval";
   }

   return $filter_clause;
}

function displayBreakHistory($class)
{
   $tz = 'America/New_York';

   $is_teacher_account = ($_SESSION['user_role'] == 'teacher');

   $COLUMNS = "b.break_id, b.student_id, s.fname, s.lname, b.break_type, b.pass_type, " .
              "TO_CHAR(timezone('$tz', b.time_out), 'HH12:MI:SS AM'), " .
              "TO_CHAR(timezone('$tz', b.time_in),  'HH12:MI:SS AM'), " .
              "TO_CHAR(age(b.time_in, b.time_out), 'HH24:MI:SS') AS duration";

   if ($is_teacher_account)
   {
      $COLUMNS = $COLUMNS .
                 ", TO_CHAR(timezone('$tz', b.time_in),  'mm/DD/YYYY')" .
                 ", TO_CHAR(timezone('$tz', b.time_in),  'Dy')" .
                 ", s.class";
   }

   $HISTORY_QUERY = "SELECT $COLUMNS FROM " . getBreaksTableName() . " b, " .
                    getStudentTableName() . " s WHERE " . " b.student_id = s.student_id ";

   if ($is_teacher_account)
   {
      $start_date_str = $_SESSION[getStartDateSessionKey()];
      $stop_date_str  = $_SESSION[getStopDateSessionKey()];

      $HISTORY_QUERY = $HISTORY_QUERY .
         " AND DATE(b.time_out AT TIME ZONE '$tz')::date >= '$start_date_str' " .
         " AND DATE(b.time_out AT TIME ZONE '$tz')::date <= '$stop_date_str' " .
         getFilteringClause();
   }
   else
   {
      // class filter
      $HISTORY_QUERY = $HISTORY_QUERY . " AND s.class = '" . $_SESSION['class_id'] . "' ";

      // show TODAY filter
      $HISTORY_QUERY = $HISTORY_QUERY . "AND DATE(b.time_out AT TIME ZONE '$tz') = DATE(now() AT TIME ZONE '$tz')";
   }

   $HISTORY_QUERY = $HISTORY_QUERY . ' ORDER BY b.time_out';

   $entries = fetchQueryResults($HISTORY_QUERY);

   echo "<form action='/index.php' method='POST' enctype='multipart/form-data'>\n";
   echo "<table border=1>\n";

   if ($is_teacher_account)
   {
      echo "<th></th>\n"; // for checkbox
      echo "<th>Class</th>\n";
   }
   echo "<th>Name</th>\n";
   echo "<th>Break Type</th>\n";
   echo "<th>Pass</th>\n";
   if ($is_teacher_account)
   {
      echo "<th>Date</th>\n";
      echo "<th>Day</th>\n";
   }
   echo "<th>Time Out</th>\n";
   echo "<th>Time In</th>\n";
   echo "<th>Duration</th>\n";

   $hidden_html_ids = "0"; // prefix with an invalid ID

   $row_number = 1;
   while ( $entry = pg_fetch_row($entries) )
   {
      $break_id   = $entry[0];
      $id         = $entry[1];
      $fname      = $entry[2];
      $lname      = $entry[3];
      $break_type = $entry[4];
      $pass_type  = $entry[5];
      $time_out   = $entry[6];
      $time_in    = $entry[7];
      $durationHms= $entry[8];
      $date       = $entry[9];
      $day        = $entry[10];
      $class_id   = $entry[11];

      $uniq_id = $break_id . '@' . $id;

      if ($time_out == $time_in)
      {
         $hidden_html_ids = $hidden_html_ids . "_" . $uniq_id;

         $break_id_session_key = getBreakIdSessionKey($id);
         $_SESSION[$break_id_session_key] = $break_id;

         $time_in = "NA";
      }

      // show special color for people who left without permission
      if ($break_type == 'L w/o P')
      {
         echo "\t<tr style='background: lawngreen'>\n";
      }
      else
      {
         echo "\t<tr>\n";
      }

      if ($is_teacher_account)
      {
         echo "\t\t<td align='center'>\n" .
              $row_number .
              "\t\t\t<input  style='width: 30px; height: 30px' type='checkbox' " .
              "name='break_checkbox[]' value='" .  $break_id . "'>\n" .
              "\t\t</td>\n";

         echo "\t\t<td>$class_id</td>\n";

         $row_number = $row_number + 1;
      }
      echo "\t\t<td>$fname $lname</td>\n";
      echo "\t\t<td id='break_type_" . $id . "'>$break_type</td>\n";
      echo "\t\t<td style='text-align: center' id='pass_type_"  . $break_id . "'>$pass_type</td>\n";
      if ($is_teacher_account)
      {
         echo "\t\t<td>" . $date . "</td>\n";
         echo "\t\t<td>" . $day . "</td>\n";
      }
      echo "\t\t<td id='time_out_"   . $id . "'>$time_out</td>\n";
      echo "\t\t<td id='time_in_"    . $id . "'>$time_in</td>\n";
      echo "\t\t<td " . getDurationHtmlStyleBgcolor($durationHms) .
           " id='duration_" . $break_id . "'>" . getHmsForDisplay($durationHms) . "</td>\n";

      echo "\t</tr>\n";
   }

   // show delete button
   if ($is_teacher_account)
   {
      echo "<br/><br/>\n";
      echo "\t<tr>\n" .
         "\t\t<td column-span='2' rowspan='2'>\n" .
         "<br/>" .
         "\t\t\t" . '<input type="submit" style="font-size: 1.5em" name="submit" Value="Delete Selected"/>' . "\n" .
         "\t\t</td>\n" .
         "\t</tr>\n";
   }

   echo '<input type="hidden" id="' . getHiddenFieldId() . '" name="checkedout_ids" value="' . $hidden_html_ids . '">';

   echo "</table>\n";
   echo "</form>\n";
} // end of displayBreakHistory

// this function's only displayed for teachers
function showNotesTable($start_date_str, $stop_date_str)
{
   if ($_SESSION['user_role'] == "student")
   {
      echo '<h1 align="center">\n';
      echo '    You are not allowed to view this page\n';
      echo '</h1>\n';
      return ;
   }

   $tz = 'America/New_York';
   $query = 'SELECT note_id, class, ' .
            "TO_CHAR(timezone('$tz', ts), 'Dy'), " .
            "TO_CHAR(timezone('$tz', ts), 'mm/DD/YYYY HH12:MI:SS AM'), " .
            'note_body FROM ' . getNotesTableName() .
            " WHERE DATE(ts AT TIME ZONE '$tz')::date >= '$start_date_str' " .
            " AND   DATE(ts AT TIME ZONE '$tz')::date <= '$stop_date_str'";

   if (isset($_SESSION[getNotesClassFilterSessionKey()]) &&
         $_SESSION[getNotesClassFilterSessionKey()] != 'All')
   {
      $query = $query . " AND class = '" . $_SESSION[getNotesClassFilterSessionKey()] . "'";
   }

   printDebug($query, false);

   $notes = fetchQueryResults($query);

   echo '<div align="center">';
   echo "<form action='/notes.php' method='POST' enctype='multipart/form-data'>\n";
   echo "<table border=1>\n";

   echo "<th></th>\n";
   echo "<th style='width: 60px'>class</th>\n";
   echo "<th style='width: 60px'>Day of Week</th>\n";
   echo "<th style='width: 200px'>Time</th>\n";
   echo "<th style='width: 600px'>Note</th>\n";

   $row_number = 1;
   while ( $entry = pg_fetch_row($notes) )
   {
      $note_id   = $entry[0];
      $class     = $entry[1];
      $dow       = $entry[2];
      $time      = $entry[3];
      $note_body = $entry[4];

      echo "\t<tr>\n";

      echo "\t\t<td align='center'>\n" .
           $row_number .
           "\t\t\t<input  style='width: 20px; height: 20px' type='checkbox' " .
           "name='note_checkbox[]' value='" .  $note_id . "'>\n" .
           "\t\t</td>\n";

      echo "\t\t<td style='text-align: center'>$class</td>\n";
      echo "\t\t<td style='text-align: center'>" . $dow . "</td>\n";
      echo "\t\t<td style='text-align: center'>$time</td>\n";
      echo "\t\t<td>$note_body</td>\n";

      echo "\t</tr>\n";

      $row_number = $row_number + 1;
   }

   // show delete button
   echo "<br/><br/>\n";
   echo "\t<tr>\n" .
      "\t\t<td column-span='2' rowspan='2'>\n" .
      "<br/>" .
      "\t\t\t" . '<input type="submit" style="font-size: 1.5em" name="submit" Value="Delete Selected"/>' . "\n" .
      "\t\t</td>\n" .
      "\t</tr>\n";

   echo "</table>\n";
   echo "</form>\n";
   echo "</div>\n";
} // end of showNotesTable

function showEnumDropDown($db_enum_name, $label, $html_name, $html_id, $show_all = true)
{
   echo "<label for='$html_id'>$label</label>\n";
   echo "<select name='" . $html_name . "' id='" . $html_id . "'>\n";

   if ($show_all)
   {
      echo "\t<option value='All'>All</option>\n";
   }

   $enum_array = getEnumArray($db_enum_name);

   $num_enums = count($enum_array);
   for ($i=0; $i<$num_enums; ++$i)
   {
      echo "\t<option value='" . $enum_array[$i] . "'>" . $enum_array[$i] . "</option>\n";
   }

   echo "</select>\n";
}

// NOTE: It's OK to have empty string in the fname or lname variable
function searchStudents($fname, $lname)
{
   $students = array();

   $query = 'SELECT student_id, fname, lname, class FROM ' . getStudentTableName() .
            " WHERE fname ILIKE '%" . $fname . "%' AND lname ILIKE '%" . $lname . "%'";

   $result = fetchQueryResults($query);

   if ($result == false)
   {
      die("Failed to write to database <br/>");
   }
   else
   {
      while ( $res = pg_fetch_row($result) )
      {
         $student = new tcStudent();

         $student->student_id = $res[0];
         $student->fname      = $res[1];
         $student->lname      = $res[2];
         $student->class      = $res[3];

         printDebug("searchStudents: found student id: " . $student->student_id .
                    ', (' . $student->fname . ' ' . $student->lname .
                    ') from class ' . $student->class );

         array_push($students, $student);
      }
   }

   return $students;
}

function insertRewardWarning($comment_type, $stud_id, $comment_body)
{
   $username = $_SESSION['user_name'];

   $insert_query = "INSERT INTO " . getCommentsTableName() .
      " (student_id, teacher_name, cmt_type, comment) " .
      "VALUES ('$stud_id', '$username', '$comment_type', '$comment_body')";

   printDebug( $insert_query);

   $result = fetchQueryResults($insert_query);

   if ($result == false)
   {
      die("Failed to add reward/warning to database <br/>");
   }
}

// this function's only displayed for teachers
// function showCommentsTable($start_date_str, $stop_date_str)
// {
//    if ($_SESSION['user_role'] == "student")
//    {
//       echo '<h1 align="center">\n';
//       echo '    You are not allowed to view this page\n';
//       echo '</h1>\n';
//       return ;
//    }
//
//    $tz = 'America/New_York';
//    $query = 'SELECT note_id, class, ' .
//             "TO_CHAR(timezone('$tz', ts), 'mm/DD/YYYY HH12:MI:SS AM'), " .
//             'note_body FROM ' . getNotesTableName() .
//             " WHERE DATE(ts AT TIME ZONE '$tz')::date >= '$start_date_str' " .
//             " AND   DATE(ts AT TIME ZONE '$tz')::date <= '$stop_date_str'";
//
//    if (isset($_SESSION[getNotesClassFilterSessionKey()]) &&
//          $_SESSION[getNotesClassFilterSessionKey()] != 'All')
//    {
//       $query = $query . " AND class = '" . $_SESSION[getNotesClassFilterSessionKey()] . "'";
//    }
//
//    $notes = fetchQueryResults($query);
//
//    echo '<div align="center">';
//    echo "<form action='/notes.php' method='POST' enctype='multipart/form-data'>\n";
//    echo "<table border=1>\n";
//
//    echo "<th></th>\n";
//    echo "<th style='width: 60px'>class</th>\n";
//    echo "<th style='width: 200px'>Time</th>\n";
//    echo "<th style='width: 600px'>Note</th>\n";
//
//    $row_number = 1;
//    while ( $entry = pg_fetch_row($notes) )
//    {
//       $note_id   = $entry[0];
//       $class     = $entry[1];
//       $time      = $entry[2];
//       $note_body = $entry[3];
//
//       echo "\t<tr>\n";
//
//       echo "\t\t<td align='center'>\n" .
//            $row_number .
//            "\t\t\t<input  style='width: 20px; height: 20px' type='checkbox' " .
//            "name='note_checkbox[]' value='" .  $note_id . "'>\n" .
//            "\t\t</td>\n";
//
//       echo "\t\t<td style='text-align: center'>$class</td>\n";
//       echo "\t\t<td style='text-align: center'>$time</td>\n";
//       echo "\t\t<td>$note_body</td>\n";
//
//       echo "\t</tr>\n";
//
//       $row_number = $row_number + 1;
//    }
//
//    // show delete button
//    echo "<br/><br/>\n";
//    echo "\t<tr>\n" .
//       "\t\t<td column-span='2' rowspan='2'>\n" .
//       "<br/>" .
//       "\t\t\t" . '<input type="submit" style="font-size: 1.5em" name="submit" Value="Delete Selected"/>' . "\n" .
//       "\t\t</td>\n" .
//       "\t</tr>\n";
//
//    echo "</table>\n";
//    echo "</form>\n";
//    echo "</div>\n";
// } // end of showNotesTable

?>
