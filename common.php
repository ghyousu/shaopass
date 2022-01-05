<?php

function printDebug($str)
{
   $debug=0;

   if ($debug == 1)
   {
      echo "Debug: $str <br/>";
   }
}

function getSchemaName() { return "ohs_shao"; }

function getUsersTableName() { return getSchemaName() . "." . "users"; }

function getStudentTableName() { return getSchemaName() . "." . "student"; }

function getNotesTableName() { return getSchemaName() . "." . "notes"; }

function getBreaksTableName() { return getSchemaName() . "." . "breaks"; }

function getStudentNameChkboxHtmlId($id) { return 'student_id_' . $id; }

function getHiddenFieldId() { return 'checkedout_student_ids'; }

function getBreakIdSessionKey($student_id) { return "break_id_" . $student_id; }

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

function getUserRoleFromDB($username, $pw)
{
   $query = "SELECT role,auth_class from " . getUsersTableName() .
            " WHERE user_name = '$username' AND pw = '" . sha1($pw) . "'";

   printDebug("query: '$query'");

   $result = fetchQueryResults($query);

   if ($result == false)
   {
      die("Failed to get login info from database <br/>");
   }
   else
   {
      $row  = pg_fetch_row($result);
      $role = $row[0];

      if ($role == 'student')
      {
         $auth_class = $row[1];
         $_SESSION['class_id'] = $auth_class;
      }

      return $role;
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

function displayStudentNamesFromDB($class)
{
   $NUM_COLUMNS = 8;

   $query = "SELECT student_id, fname, lname FROM ohs_shao.student WHERE class = '$class'";

   $students = fetchQueryResults($query);

   echo "<table class='studentNamesTable'>\n";

   $loopCount = 1;
   while ( $student = pg_fetch_row($students) )
   {
      $id = $student[0];
      $name = $student[1] . "<br/>" . $student[2];

      printDebug("id: $id, name: '$name'");

      if ( $loopCount == 1 )
      {
         echo "<tr>";
      }

      $html_input_prefix = "<input type='radio' name='student_id' ";
      $html_input_id = getStudentNameChkboxHtmlId($id);

      echo "<td id='td_label_" . $id . "' style='padding-bottom: 30px; padding-right: 30px;'>\n";
      echo "$html_input_prefix id='$html_input_id' value='$id' onchange='studentNameSelected(this)' />\n";
      echo "<label style='font-size: 1.5em' for='$html_input_id'><br/>$name</label>\n";
      echo "</td>\n";

      if ( $loopCount++ == $NUM_COLUMNS )
      {
         echo "</tr>\n";
         $loopCount = 1;
      }
   }

   echo "</table>\n";
}

function displayBreakTypes()
{
   $query = "SELECT unnest(enum_range(NULL::youBreakType))";

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
   $query = "SELECT unnest(enum_range(NULL::youPassType))";

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

function displayTodaysHistory($class)
{
   $tz = 'America/New_York';

   $COLUMNS = "b.break_id, b.student_id, s.fname, s.lname, b.break_type, b.pass_type, " .
              "TO_CHAR(timezone('$tz', b.time_out), 'HH12:MI:SS AM'), " .
              "TO_CHAR(timezone('$tz', b.time_in),  'HH12:MI:SS AM'), " .
              "TO_CHAR(age(b.time_in, b.time_out), 'HH24:MI:SS')";

   $HISTORY_QUERY = "SELECT $COLUMNS FROM " . getBreaksTableName() . " b, " .
                    getStudentTableName() . " s WHERE " .
                    " b.student_id = s.student_id AND s.class = '" . $_SESSION['class_id'] . "' " .
                    "AND DATE(b.time_out AT TIME ZONE '$tz') = DATE(now() AT TIME ZONE '$tz') ORDER BY b.time_out";

   $entries = fetchQueryResults($HISTORY_QUERY);

   echo "<table border=1>\n";

   echo "<th>Name</th>\n";
   echo "<th>Break Type</th>\n";
   echo "<th>Pass</th>\n";
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

      $uniq_id = $break_id . '@' . $id;

      if ($time_out == $time_in)
      {
         $hidden_html_ids = $hidden_html_ids . "_" . $uniq_id;

         $break_id_session_key = getBreakIdSessionKey($id);
         $_SESSION[$break_id_session_key] = $break_id;

         $time_in = "NA";
      }

      echo "\t<tr>\n";

      echo "\t\t<td>$fname $lname</td>\n";
      echo "\t\t<td id='break_type_" . $id . "'>$break_type</td>\n";
      echo "\t\t<td style='text-align: center' id='pass_type_"  . $break_id . "'>$pass_type</td>\n";
      echo "\t\t<td id='time_out_"   . $id . "'>$time_out</td>\n";
      echo "\t\t<td id='time_in_"    . $id . "'>$time_in</td>\n";
      echo "\t\t<td " . getDurationHtmlStyleBgcolor($durationHms) .
           " id='duration_" . $break_id . "'>" . getHmsForDisplay($durationHms) . "</td>\n";

      echo "\t</tr>\n";
   }

   echo '<input type="hidden" id="' . getHiddenFieldId() . '" name="checkedout_ids" value="' . $hidden_html_ids . '">';

   echo "</table>\n";
}

?>
