<?php

function printDebug($str)
{
   $debug=0;

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

function getIndividualSchemaName() { return $_SESSION['schema_name']; }

// enum types in individual schemas
function getBreakTypeEnumName() { return getIndividualSchemaName() . ".youbreaktype"; }
function getPassTypeEnumName() { return getIndividualSchemaName() . ".youpasstype"; }

// table names in individual schemas
function getNotesTableName() { return getIndividualSchemaName() . "." . "notes"; }
function getBreaksTableName() { return getIndividualSchemaName() . "." . "breaks"; }
function getSeatingTableName() { return getIndividualSchemaName() . "." . "seating"; }

function getStudentNameChkboxHtmlId($id) { return 'student_id_' . $id; }

function getHiddenFieldId() { return 'checkedout_student_ids'; }

function getBreakIdSessionKey($student_id) { return "break_id_" . $student_id; }

function getDefaultNumberDaysToDisplay() { return 0; }
function getNotesStartDateSessionKey() { return 'notes_date_start'; }
function getNotesStopDateSessionKey() { return 'notes_date_stop'; }

function getBreakStartDateSessionKey() { return 'break_date_start'; }
function getBreakStopDateSessionKey() { return 'break_date_stop'; }
function getClassFilterSessionKey() { return 'filter_class_id'; }

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

function displayStudentNamesFromDB($class)
{
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
   while ( $student = pg_fetch_row($students) )
   {
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
      while ($tc_idx != $db_col)
      {
         $tr_data = $tr_data . "<td/>\n";
         $tc_idx += 1;
      }

      $html_input_prefix = "<input type='radio' name='student_id' ";
      $html_input_id = getStudentNameChkboxHtmlId($id);

      $tr_data = $tr_data . "<td id='td_label_" . $id . "' style='padding-bottom: 30px; padding-right: 30px;'>\n";
      $tr_data = $tr_data . "$html_input_prefix id='$html_input_id' value='$id' onchange='studentNameSelected(this)' />\n";
      $tr_data = $tr_data . "<label style='font-size: 1.5em' for='$html_input_id'><br/>$name</label>\n";
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

function getClassEnumArray()
{
   $query = 'SELECT unnest(enum_range(NULL::' . getClassEnumName() . '))';

   $class_names_array = array();

   $class_names = fetchQueryResults($query);

   while ( $class_id = pg_fetch_row($class_names) )
   {
      $value = $class_id[0];
      array_push($class_names_array, $value);
   }

   return $class_names_array;
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

function displayBreakHistory($class)
{
   $tz = 'America/New_York';

   $is_teacher_account = ($_SESSION['user_role'] == 'teacher');

   $COLUMNS = "b.break_id, b.student_id, s.fname, s.lname, b.break_type, b.pass_type, " .
              "TO_CHAR(timezone('$tz', b.time_out), 'HH12:MI:SS AM'), " .
              "TO_CHAR(timezone('$tz', b.time_in),  'HH12:MI:SS AM'), " .
              "TO_CHAR(age(b.time_in, b.time_out), 'HH24:MI:SS')";

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
      $start_date_str = $_SESSION[getBreakStartDateSessionKey()];
      $stop_date_str  = $_SESSION[getBreakStopDateSessionKey()];

      $HISTORY_QUERY = $HISTORY_QUERY .
         " AND DATE(b.time_out AT TIME ZONE '$tz')::date >= '$start_date_str' " .
         " AND DATE(b.time_out AT TIME ZONE '$tz')::date <= '$stop_date_str' ";

      if (isset($_SESSION[getClassFilterSessionKey()]) &&
            $_SESSION[getClassFilterSessionKey()] != "All")
      {
         $HISTORY_QUERY = $HISTORY_QUERY .
            " AND class = '" . $_SESSION[getClassFilterSessionKey()] . "'";
      }
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

      echo "\t<tr>\n";

      if ($is_teacher_account)
      {
         echo "\t\t<td align='center'>\n" .
              "\t\t\t<input  style='width: 30px; height: 30px' type='checkbox' " .
              "name='break_checkbox[]' value='" .  $break_id . "'>\n" .
              "\t\t</td>\n";

         echo "\t\t<td>$class_id</td>\n";
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

function showNotesTable($start_date_str, $stop_date_str)
{
   $tz = 'America/New_York';
   $query = 'SELECT note_id, class, ' .
            "TO_CHAR(timezone('$tz', ts), 'mm/DD/YYYY HH12:MI:SS AM'), " .
            'note_body FROM ' . getNotesTableName() .
            " WHERE DATE(ts AT TIME ZONE '$tz')::date >= '$start_date_str' " .
            " AND   DATE(ts AT TIME ZONE '$tz')::date <= '$stop_date_str'" .

   $show_check_box = false; // for student's account
   if (isset($_SESSION['class_id']))
   {
      $query = $query . " AND class = '" . $_SESSION['class_id'] . "'";
   }
   else
   {
      $show_check_box = true; // assume teacher's account
   }

   $notes = fetchQueryResults($query);

   echo '<div align="center">';
   echo "<form action='/notes.php' method='POST' enctype='multipart/form-data'>\n";
   echo "<table border=1>\n";

   if ($show_check_box)
   {
      echo "<th></th>\n";
   }
   echo "<th style='width: 60px'>class</th>\n";
   echo "<th style='width: 200px'>Time</th>\n";
   echo "<th style='width: 600px'>Note</th>\n";

   while ( $entry = pg_fetch_row($notes) )
   {
      $note_id   = $entry[0];
      $class     = $entry[1];
      $time      = $entry[2];
      $note_body = $entry[3];

      echo "\t<tr>\n";

      if ($show_check_box)
      {
         echo "\t\t<td align='center'>\n" .
              "\t\t\t<input  style='width: 20px; height: 20px' type='checkbox' " .
              "name='note_checkbox[]' value='" .  $note_id . "'>\n" .
              "\t\t</td>\n";
      }
      echo "\t\t<td style='text-align: center'>$class</td>\n";
      echo "\t\t<td style='text-align: center'>$time</td>\n";
      echo "\t\t<td>$note_body</td>\n";

      echo "\t</tr>\n";
   }

   // show delete button
   if ($show_check_box)
   {
      echo "<br/><br/>\n";
      echo "\t<tr>\n" .
         "\t\t<td column-span='2' rowspan='2'>\n" .
         "<br/>" .
         "\t\t\t" . '<input type="submit" style="font-size: 1.5em" name="submit" Value="Delete Selected"/>' . "\n" .
         "\t\t</td>\n" .
         "\t</tr>\n";
   }

   echo "</table>\n";
   echo "</form>\n";
   echo "</div>\n";
} // end of showNotesTable

function showClassNameDropDown($html_name, $html_id)
{
   echo "<label for='class_drop_down'>Select A class: </label>\n";
   echo "<select name='" . $html_name . "' id='" . $html_id . "'>\n";
   echo "\t<option value='All'>All</option>\n";

   $class_names = getClassEnumArray();

   $num_classes = count($class_names);
   for ($i=0; $i<$num_classes; ++$i)
   {
      echo "\t<option value='" . $class_names[$i] . "'>" . $class_names[$i] . "</option>\n";
   }

   echo "</select>\n";
}

?>
