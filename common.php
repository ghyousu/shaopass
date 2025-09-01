<style>
.studNameCell {
   width: 150px;
   height: 100px;
}

.studNameSelRadioBtn {
   width: 1.5em;
   height: 1.5em;
}

.breakTypeRadioBtn {
   width: 1.5em;
   height: 1.5em;
}

.passTypeRadioBtn {
   width: 1.5em;
   height: 1.5em;
}

input[type=radio]{
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-clip: content-box;
  border: 2px solid rgba(255,252,229,1);
  background-color: rgba(255,252,229,1);
}

input[type="radio"]:checked {
  appearance: none;
  background-color: green;
  padding: 4px;
  border: 2px solid #000;
}

</style>

<?php

function printDebug($str, $debug = 0)
{
   if ($debug == 1)
   {
      echo "Debug: $str <br/>";
   }
}

function printInfo($str, $enabled = 0)
{
   if ($enabled == 1)
   {
      echo "Info: $str <br/>";
   }
}

function printError($str)
{
   echo "Error: $str <br/>";
}

// use public schema for now, can be renamed to other names if needed
function getCommonSchemaName() { return 'common'; }

// common schema tables
function getUsersTableName() { return getCommonSchemaName() . ".users"; }
function getStudentTableName() { return getCommonSchemaName() . "." . "student"; }

// enum types in common schema
function getClassEnumName() { return getCommonSchemaName() . ".youclassname"; }
function getCommentTypeEnumName() { return getcommonschemaname() . ".commentType"; }
function getHWStatusEnumName() { return getIndividualSchemaName() . ".hwSubmissionStatus"; }
function getToggleHWStatusProcName() { return getIndividualSchemaName() . ".toggleHomeworkStatus"; }

function getIndividualSchemaName() { return $_SESSION['schema_name']; }

// enum types in individual schemas
function getBreakTypeEnumName() { return getIndividualSchemaName() . ".youbreaktype"; }
function getPassTypeEnumName() { return getIndividualSchemaName() . ".youpasstype"; }

// table names in individual schemas
function getNotesTableName() { return getIndividualSchemaName() . "." . "notes"; }
function getBreaksTableName() { return getIndividualSchemaName() . "." . "breaks"; }
function getSeatingTableName() { return getIndividualSchemaName() . "." . "seating"; }
function getCommentTemplateTableName() { return getIndividualSchemaName() . "." . "comment_template"; }
function getCommentsTableName() { return getIndividualSchemaName() . "." . "teacherComment"; }
function getHWSubmissionTableName() { return getIndividualSchemaName() . "." . "hw_submissions"; }

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
function getCmtFNameFilterSessionKey()  { return 'cmt_filter_fname'; }
function getCmtLNameFilterSessionKey()  { return 'cmt_filter_lname'; }
function getDurationFilterSessionKey()  { return 'filter_duration'; }
function getAdminPageClassSessionKey()  { return 'filter_admin_class_id'; }

class tcComment
{
   public $cmt_id = 0;
   public $cmt_type = "";
   public $cmt_text = "";
   public $is_active = false;

   // for convinience, do time conversion from database and store string in memory
   public $cmt_dow = "";
   public $full_ts = "";
}

class tcStudent
{
   public $student_id = 0;
   public $fname = "";
   public $lname = "";
   public $class = "";
   public $today_hw_status = '';
   public $seating_row = null;
   public $seating_col = null;
   public $display_color = null;
   public $comments = array();
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
      die('Failed to execute query. query str: "' . $query . '". <br/>' .
          'Error str: "' . pg_last_error($conn) . '"');

      pg_close($conn);
   }

   pg_close($conn);
   return $result;
}

function updateStudentDisplayColor($stud_ids, $display_color)
{
   $conn = getDBConnection();

   $num_ids = count($stud_ids);
   for ($i=0; $i<$num_ids; $i++)
   {
      $stud_id = $stud_ids[$i];

      $query = "UPDATE " . getStudentTableName() . " SET display_color = '" . $display_color .
               "' WHERE student_id = " . $stud_id;

      $result = pg_query($conn, $query);

      if ($result == false)
      {
         die('Failed to execute query. query str: "' . $query . '". <br/>' .
               'Error str: "' . pg_last_error($conn) . '"');

         pg_close($conn);
      }
   }

   pg_close($conn);
}

function authenticateUser($username, $pw)
{
   $query = "SELECT role,auth_class,schema_name from " . getUsersTableName() .
            " WHERE user_name = '$username' AND pw = '" . sha1($pw) . "'";

   printDebug("query: '$query'");

   $result = fetchQueryResults($query);

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

function checkoutStudent($student_id, $break_type, $pass_type)
{
   $insert_query = "INSERT INTO " . getBreaksTableName() . " (student_id, break_type, pass_type) " .
      "VALUES ('$student_id', '$break_type', '$pass_type') RETURNING break_id";

   $result = fetchQueryResults($insert_query);

   $break_id = pg_fetch_row($result)[0];

   printDebug("successfully inserted break id: $break_id <br/>");

   return $break_id;
}

function checkinStudent($student_id, $break_id)
{
   $update_query = "UPDATE " . getBreaksTableName() . " SET time_in = NOW() " .
                   "WHERE break_id = " . $break_id;

   printDebug($update_query);

   fetchQueryResults($update_query);
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

   fetchQueryResults($insert_query);
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

   while ( $res = pg_fetch_row($result) )
   {
      $stud_id  = $res[0];
      $num_warn = $res[1];

      $id_warn_map[$stud_id] = $num_warn;
   }

   return $id_warn_map;
}

function insertRewardWarning($comment_type, $stud_id, $comment_body)
{
   $username = $_SESSION['user_name'];

   $insert_query = "INSERT INTO " . getCommentsTableName() .
      " (student_id, teacher_name, cmt_type, comment) " .
      "VALUES ('$stud_id', '$username', '$comment_type', '$comment_body')";

   printDebug($insert_query);

   $result = fetchQueryResults($insert_query);

   if ($result == false)
   {
      die("Failed to add reward/warning to database <br/>");
   }
}

function displayStudentNamesFromDB($class)
{
   $active_warnings_map = getActiveWarningsRewards($class, 'warning');
//   $active_rewards_map  = getActiveWarningsRewards($class, 'reward');

   $NUM_COLUMNS = getMaxColumns();
   printDebug("NUM_COLUMNS = $NUM_COLUMNS <br/>");

   $query = "SELECT s.student_id, s.fname, s.lname, t.row, t.col, s.display_color FROM " .
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
      $db_color = $student[5];

      // myou: only enable this for Salim's classes
      if ( substr($_SESSION['user_name'], 0, 3) != "sci" )
      {
         $db_color = "unset";
      }

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

      $html_input_prefix = "<input class='studNameSelRadioBtn' type='radio' name='student_id' ";
      $html_input_id = getStudentNameChkboxHtmlId($id);

      $num_warn = 0;
//      $num_rewards = 0;
      if (array_key_exists( $id, $active_warnings_map))
      {
         $num_warn = $active_warnings_map[$id];
      }
//      if (array_key_exists( $id, $active_rewards_map))
//      {
//         $num_rewards = $active_rewards_map[$id];
//      }
      $tr_data = $tr_data . "<td style='background-color: " . $db_color . "' id='td_label_" . $id . "' class='studNameCell'>\n";
      $tr_data = $tr_data . "$html_input_prefix id='$html_input_id' value='$id' onchange='studentNameSelected(this)' />\n";
      if ($num_warn > 0)
      {
         $tr_data = $tr_data . '<strong><span style="color:white;background-color:red;font-size:1.5em;float:right">' . $num_warn . '</strong></span>';
      }
      $tr_data = $tr_data . "<label style='font-size: 1.5em' for='$html_input_id'><br/>$name</label>\n";
//      $tr_data = $tr_data . '<br/><strong><span style="color:white;background-color:purple;font-size:1.5em;float:right">' . $num_rewards . '</span></strong>';
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

/* 12/24/2024 disable this for now     if ($value == "Late")
      {
         continue; // temporarily disable this enum until further evaluation
      } */

      $html_input_prefix = "<input class='breakTypeRadioBtn' type='radio' name='break_type' ";
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

      // updated on 12/24/2024:
      // * hide S3, L1, L2, L3 enums since psql doesn't support removing enums
      if ($value == "S3" || $value == "L1" || $value == "L2" || $value == "L3")
      {
         continue;
      }

/* 12/24/2024 disable this for now      if ($value == "Late")
      {
         continue; // temporarily disable this enum until further evaluation
      } */

      $html_input_prefix = "<input class='passTypeRadioBtn' type='radio' name='pass_type' ";
      $html_input_id = 'pass_type_' . $value;
      $html_label_id = 'pass_type_label_' . $value;

/* 12/24/2024 disable this for now      if ($value == "Late")
      {
         echo "<td style='display: none'>\n";
         // echo "<td style='padding-bottom: 3%'>\n";
      }
      else
      { */
         echo "<td style='padding-bottom: 3%'>\n";
//      }
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

   if ($duration_in_secs >= 10 * 60)
   {
      $html_style = "style='text-align: center; background-color: red'";
   }
   /*  2022-09-11; no longer applicable for 2022-2023 school year
   else if ($duration_in_secs >= 15 * 60)
   {
      $html_style = "style='text-align: center; background-color: yellow'";
   }
   */
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
      else if ($break_type == 'Acer w/o P')
      {
         echo "\t<tr style='background: deepskyblue'>\n";
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

function showEnumDropDown($db_enum_name, $label, $html_name, $html_id, $show_all = true, $on_change = "")
{
   echo "<label for='$html_id'>$label</label>\n";
   echo "<select name='" . $html_name . "' id='" . $html_id . "' ";

   if ($on_change != "")
   {
      echo $on_change;
   }

   echo ">\n";

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
function searchCommentsFromDB($fname, $lname)
{
   $students = array();

   $tz = 'America/New_York';

   $query = 'SELECT s.student_id, s.fname, s.lname, s.class, c.comment_id, c.cmt_type, c.comment, ' .
            "c.is_active, TO_CHAR(timezone('$tz', c.time), 'Dy'), " .
            "TO_CHAR(timezone('$tz', c.time), 'mm/DD/YYYY HH12:MI:SS AM') " .
            'FROM ' . getStudentTableName() . ' s, ' .
            getCommentsTableName() . ' c ' . 'WHERE s.student_id=c.student_id ' .
            "AND s.fname ILIKE '%" . $fname . "%' AND s.lname ILIKE '%" . $lname . "%' " .
            'ORDER BY c.is_active, c.time';

   printDebug($query, 0);

   $result = fetchQueryResults($query);

   while ( $res = pg_fetch_row($result) )
   {
      $id = $res[0];

      if (!array_key_exists($id, $students))
      {
         $student = new tcStudent();
         $student->student_id = $res[0];
         $student->fname      = $res[1];
         $student->lname      = $res[2];
         $student->class      = $res[3];

         $students[$id] = $student;

         printDebug("searchCommentsFromDB: found student id: " . $student->student_id .
               ', (' . $student->fname . ' ' . $student->lname .
                 ') from class ' . $student->class);
      }

      $student = $students[$id];

      $comment = new tcComment();
      $comment->cmt_id    = $res[4];
      $comment->cmt_type  = $res[5];
      $comment->cmt_text  = $res[6];
      $comment->is_active = ($res[7] == 't');
      $comment->cmt_dow   = $res[8];
      $comment->full_ts   = $res[9];

      printDebug("searchCommentsFromDB: adding cmt_type " . $comment->cmt_type .
            ", is_active = " . $comment->is_active . ", dow: " . $comment->cmt_dow .
            ", ts: " . $comment->full_ts . ", comment: '" . $comment->cmt_type . "'");

      array_push($student->comments, $comment);
   }

   return $students;
} // end of searchCommentsFromDB

function insertNewStudent($fname, $lname, $class)
{
   $insert_query = "INSERT INTO " . getStudentTableName() .
      " (fname, lname, class) " .
      "VALUES ('$fname', '$lname', '$class')";

   printDebug($insert_query, 0);

   return fetchQueryResults($insert_query);
}

function getAllStudents()
{
   $query = "SELECT student_id, fname, lname FROM " .  getStudentTableName();

   $students = fetchQueryResults($query);

   $stud_array = array();

   while ( $row = pg_fetch_row($students) )
   {
      $student = new tcStudent();
      $student->student_id = $row[0];
      $student->fname      = $row[1];
      $student->lname      = $row[2];

      array_push($stud_array, $student);
   }

   return $stud_array;
}

function getStudentNamesPerClass($class)
{
   $query = "SELECT student_id, fname, lname FROM " .
            getStudentTableName() . " WHERE class = '$class'";

   $students = fetchQueryResults($query);

   $stud_array = array();

   while ( $row = pg_fetch_row($students) )
   {
      $student = new tcStudent();
      $student->student_id = $row[0];
      $student->fname      = $row[1];
      $student->lname      = $row[2];

      array_push($stud_array, $student);
   }

   return $stud_array;
}

function deleteStudentPerId($stud_id)
{
   $query = "DELETE FROM " . getStudentTableName() . " WHERE student_id = $stud_id";

   fetchQueryResults($query);
}

function renameStudent($stud_id, $new_fname, $new_lname)
{
   $query = "UPDATE " . getStudentTableName() .
            " SET fname = '$new_fname', lname = '$new_lname'" .
            " WHERE student_id = $stud_id";

   fetchQueryResults($query);
}

function getStudentsWithSeatAssignment($class)
{
   $query = "SELECT s.student_id, s.fname, s.lname, t.row, t.col FROM " .
            getStudentTableName() . " s, " .
            getSeatingTableName() . " t " .
            "WHERE s.class = '$class' AND s.student_id = t.student_id " .
            "ORDER BY t.row, t.col";

   $students = fetchQueryResults($query);

   $stud_array = array();

   while ( $row = pg_fetch_row($students) )
   {
      $student = new tcStudent();
      $student->student_id  = $row[0];
      $student->fname       = $row[1];
      $student->lname       = $row[2];
      $student->seating_row = $row[3];
      $student->seating_col = $row[4];

      $array_index = $student->seating_row * 10 + $student->seating_col;
      $stud_array[$array_index] = $student;
   }

   // var_dump($stud_array);

   return $stud_array;
}

function getStudentsWithoutSeatAssignment($class)
{
   $query = "SELECT s.student_id, s.fname, s.lname FROM " .
            getStudentTableName() . " s LEFT JOIN " .
            getSeatingTableName() . " t " .
            "ON s.student_id = t.student_id WHERE s.class = '$class' AND " .
            "t.row IS NULL";

   $students = fetchQueryResults($query);

   $stud_array = array();

   while ( $row = pg_fetch_row($students) )
   {
      $student = new tcStudent();
      $student->student_id  = $row[0];
      $student->fname       = $row[1];
      $student->lname       = $row[2];

      array_push($stud_array, $student);
   }

   return $stud_array;
}

function getStudentNamesForRainbowPage($class)
{
   $query = "SELECT s.student_id, s.fname, s.lname, s.display_color, t.row, t.col FROM " .
            getStudentTableName() . " s, " .
            getSeatingTableName() . " t " .
            "WHERE s.student_id = t.student_id AND s.class = '$class' " .
            " ORDER BY t.row DESC, t.col";

   $students = fetchQueryResults($query);

   $stud_array = array();

   while ( $row = pg_fetch_row($students) )
   {
      $student = new tcStudent();
      $student->student_id    = $row[0];
      $student->fname         = $row[1];
      $student->lname         = $row[2];
      $student->display_color = $row[3];
      $student->seating_row   = $row[4];
      $student->seating_col   = $row[5];

      array_push($stud_array, $student);
   }

   return $stud_array;
}

function getStudentsForHWTracker($class)
{
   $ugly_sub_query = '(SELECT COALESCE(' .
          '(SELECT hw_status FROM ' . getHWSubmissionTableName() . ' h ' .
            "WHERE h.student_id = s.student_id AND h.hw_date = CURRENT_DATE), 'incomplete')" .
          ') AS hw_status ';

   $query = 'SELECT s.student_id, s.fname, s.lname, t.row, t.col, ' . $ugly_sub_query .
            ' FROM ' . getStudentTableName() . " s, " .  getSeatingTableName() . " t " .
            "WHERE s.student_id = t.student_id AND s.class = '$class' " .
            " ORDER BY t.row DESC, t.col";

   $students = fetchQueryResults($query);

   $stud_array = array();

   while ( $row = pg_fetch_row($students) )
   {
      $student = new tcStudent();
      $student->student_id      = $row[0];
      $student->fname           = $row[1];
      $student->lname           = $row[2];
      $student->seating_row     = $row[3];
      $student->seating_col     = $row[4];
      $student->today_hw_status = $row[5];

      array_push($stud_array, $student);
   }

   return $stud_array;
}

function insertUpdateSeatAssignment($stud_id, $row, $col, $is_insert)
{
   $insertQuery = "INSERT INTO " . getSeatingTableName() .
            " (student_id, row, col) VALUES ($stud_id, $row, $col)";

   $updateQuery = "UPDATE " . getSeatingTableName() .
            " SET row=$row, col=$col WHERE student_id = $stud_id";

   if ($is_insert)
   {
      fetchQueryResults($insertQuery);
   }
   else
   {
      fetchQueryResults($updateQuery);
   }
}

function moveStudent($stud_id, $new_class)
{
   $updateQuery = "UPDATE " . getStudentTableName() .
            " SET class = '$new_class' WHERE student_id = $stud_id";

   $removeSeatingQuery = "DELETE FROM " . getSeatingTableName() .
            " WHERE student_id = $stud_id";

   fetchQueryResults($updateQuery) && fetchQueryResults($removeSeatingQuery);
}

function getStudentsPerSeat($row, $col)
{
   $query = "SELECT s.fname, s.lname, s.class, t.row, t.col, s.student_id FROM " .
            getStudentTableName() . " s, " .
            getSeatingTableName() . " t " .
            "WHERE s.student_id = t.student_id AND t.row = $row AND t.col = $col " .
            "ORDER BY s.class";

   $students = fetchQueryResults($query);

   $stud_array = array();

   while ( $row = pg_fetch_row($students) )
   {
      $student = new tcStudent();
      $student->fname       = $row[0];
      $student->lname       = $row[1];
      $student->class       = $row[2];
      $student->seating_row = $row[3];
      $student->seating_col = $row[4];
      $student->student_id  = $row[5];

      $stud_array[$student->class] = $student;
   }

   // var_dump($stud_array);

   return $stud_array;
}

function markCommentsInactive($cmt_id)
{
   $query = "UPDATE " . getCommentsTableName() .
            " SET is_active = 'f', redeem_time = 'NOW()' " .
            "WHERE comment_id = $cmt_id";

   printDebug($query, 0);

   fetchQueryResults($query);
}

function getCommentTemplates()
{
   $cmt_templates = array();

   $query = "SELECT comment FROM " . getCommentTemplateTableName() .
            " WHERE cmt_type = 'warning' ORDER BY comment_id";

   $result = fetchQueryResults($query);

   while ( $res = pg_fetch_row($result) )
   {
      array_push($cmt_templates, $res[0]);
   }

   return $cmt_templates;
}

function updateStudentHomeworkTracker($student_id)
{
   $query = 'CALL ' . getToggleHWStatusProcName() . '(' .  $student_id . ')';

   fetchQueryResults($query);
}

?>
