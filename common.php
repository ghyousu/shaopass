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

function getStudentTableName() { return getSchemaName() . "." . "student"; }

function getBreaksTableName() { return getSchemaName() . "." . "breaks"; }

function getStudentNameChkboxHtmlId($id) { return 'student_id_' . $id; }

function getHiddenFieldId() { return 'checkedout_student_ids'; }

function getNameSessionKey($student_id) { return "name_" . $student_id; }

function getBreakIdSessionKey($student_id) { return "break_id_" . $student_id; }

// return a working connection, caller is responsible to close
// connection when done
function getDBConnection()
{
   $db_url = getenv("DATABASE_URL");

   printDebug("db_url from env: '$db_url'");

   if ($db_url == "")
   {
      $local_file = "heroku_database_url.txt";

      printDebug("local file: $local_file");

      if (is_readable($local_file))
      {
         $myfile = fopen($local_file, "r") or die("Unable to open file '$local_file'!");
         $db_url = fread($myfile, filesize($local_file)-1); // account for EOL

         printDebug("file size: " . filesize($local_file));
         printDebug("db_url from $local_file: '$db_url'");

         fclose($myfile);
      }
   }

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

   // $students = pg_query_params($conn, 'SELECT * FROM ohs_shao.student WHERE class = $1', '901');
   // failing: $students = pg_query($conn, 'SELECT * FROM ohs_shao.student WHERE class = "901"');
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

function displayStudentNamesFromDB()
{
   $NUM_COLUMNS = 5;

   // $students = pg_query_params($conn, 'SELECT * FROM ohs_shao.student WHERE class = $1', '901');
   // failing: $students = pg_query($conn, 'SELECT * FROM ohs_shao.student WHERE class = "901"');
   $students = fetchQueryResults('SELECT student_id, fname, lname FROM ohs_shao.student');

   echo "<table class='studentNamesTable'>\n";

   $loopCount = 1;
   while ( $student = pg_fetch_row($students) )
   {
      $id = $student[0];
      $name = $student[1] . " " . $student[2];

      $_SESSION[getNameSessionKey($id)] = $name;

      printDebug("id: $id, name: '$name'");

      if ( $loopCount == 1 )
      {
         echo "<tr>";
      }

      $html_input_prefix = "<input type='radio' name='student_id' ";
      $html_input_id = getStudentNameChkboxHtmlId($id);

      echo "<td id='td_label_" . $id . "' style='padding-bottom: 3%'>\n";
      echo "$html_input_prefix id='$html_input_id' value='$id' onchange='studentNameSelected(this)' />\n";
      echo "<label style='font-size: 1.5em' for='$html_input_id'>$name</label>\n";
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
   $break_types = array("Bathroom", "Nurse", "Water", "Other");

   echo "<table class='breakTypesTable'>\n";
   echo "<tr>\n";

   foreach ($break_types as $index => $value)
   {
      printDebug("index = $index, value = $value");

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
   $pass_types = array("A", "B", "E");

   echo "<table class='passTypesTable'>\n";
   echo "<tr>\n";

   foreach ($pass_types as $index => $value)
   {
      printDebug("index = $index, value = $value");

      $html_input_prefix = "<input type='radio' name='pass_type' ";
      $html_input_id = 'pass_type_' . $value;

      echo "<td style='padding-bottom: 3%'>\n";
      echo "$html_input_prefix id='$html_input_id' value='$value' />\n";
      echo "<label style='font-size: 2.0em; margin-right: 30px;' for='$html_input_id'>$value</label>\n";
      echo "</td>\n";
   }

   echo "</tr>\n";
   echo "</table>\n";
}

// duration's in HH:SS format.
function mmssToSeconds( $duration )
{
   $mm_ss_array = explode(":", $duration);
   $mm = $mm_ss_array[0];
   $ss = $mm_ss_array[1];

   return $mm * 60 + $ss;
}


// duration's in HH:SS format.
//   color coding:
//     *  0-15 mins -> no color
//     * 15-20 mins -> yellow
//     * longer than 20 mins -> red
function getDurationHtmlStyleBgcolor( $duration )
{
   $duration_in_secs = mmssToSeconds($duration);

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
   $COLUMNS = "break_id, student_id, break_type, pass_type, " .
              "TO_CHAR(timezone('America/New_York', time_out), 'HH12:MI:SS AM'), " .
              "TO_CHAR(timezone('America/New_York', time_in),  'HH12:MI:SS AM'), " .
              "TO_CHAR(age(time_in, time_out), 'MI:SS')";
   $HISTORY_QUERY = "SELECT $COLUMNS FROM " . getBreaksTableName() . " WHERE " .
                    "DATE(time_out) = CURRENT_DATE ORDER BY time_out";

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
      $id         = $entry[1]; // student_id
      $break_type = $entry[2];
      $pass_type  = $entry[3];
      $time_out   = $entry[4];
      $time_in    = $entry[5];
      $duration   = $entry[6];

      if ($time_out == $time_in)
      {
         $hidden_html_ids = $hidden_html_ids . "_" . $id;

         $break_id_session_key = getBreakIdSessionKey($id);
         $_SESSION[$break_id_session_key] = $break_id;

         $time_in = "NA";
      }

      echo "\t<tr>\n";

      echo "\t\t<td>" . $_SESSION[getNameSessionKey($id)] . "</td>\n";
      echo "\t\t<td id='break_type_" . $id . "'>$break_type</td>\n";
      echo "\t\t<td style='text-align: center' id='pass_type_"  . $id . "'>$pass_type</td>\n";
      echo "\t\t<td id='time_out_"   . $id . "'>$time_out</td>\n";
      echo "\t\t<td id='time_in_"    . $id . "'>$time_in</td>\n";
      echo "\t\t<td " . getDurationHtmlStyleBgcolor($duration) . " id='duration_" . $break_id . "'>$duration</td>\n";

      echo "\t</tr>\n";
   }

   echo '<input type="hidden" id="' . getHiddenFieldId() . '" name="checkedout_ids" value="' . $hidden_html_ids . '">';

   echo "</table>\n";
}

?>
