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

// return a working connection, caller is responsible to close
// connection when done
function getDBConnection()
{
   // $conn = pg_connect(getenv("DATABASE_URL"));
   $conn = pg_connect("postgres://offebckiwtuszs:10a387b0a19ce9fccc2b232127a8a2eb039d60d96243c6031d3f38c43d8a79fe@ec2-3-218-158-102.compute-1.amazonaws.com:5432/devqb8osdvb4qn");

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

function updateStudent($student_id, $break_type, $pass_type)
{
   $check_time_out_query = "SELECT * FROM " . getBreaksTableName() . " WHERE " .
                           "student_id = '$student_id' AND " .
                           "time_in = time_out AND " .
                           "DATE(time_out) = CURRENT_DATE " .
                           "ORDER BY break_id DESC LIMIT 1";

   $has_time_out = pg_num_rows(fetchQueryResults($check_time_out_query));

   if ($has_time_out == 1)
   {
      // update "time_in" column
      printDebug("to be implemented<br/>");
   }
   else
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

      printDebug("id: $id, name: '$name'");

      if ( $loopCount == 1 )
      {
         echo "<tr>";
      }

      $html_input_prefix = "<input type='radio' name='student_id' ";
      $html_input_id = getStudentNameChkboxHtmlId($id);
      $html_label_id = 'label_name_' . $id;

      echo "<td id='td_label_" . $id . "' style='padding-bottom: 3%'>\n";
      echo "$html_input_prefix id='$html_input_id' value='$id' onchange='studentNameSelected(this)' />\n";
      echo "<label id='$html_label_id' for='$html_input_id'>$name</label>\n";
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
      echo "<label for='$html_input_id'>$value</label>\n";
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
      echo "<label for='$html_input_id'>$value</label>\n";
      echo "</td>\n";
   }

   echo "</tr>\n";
   echo "</table>\n";
}

function displayTodaysHistory($class)
{
   // TODO:
   //    * add in "class" variable
   //    * display student name instead of id
   $COLUMNS = "student_id, break_type, pass_type, " .
              "TO_CHAR(timezone('America/New_York', time_out), 'HH24:MI:SS'), " .
              "TO_CHAR(timezone('America/New_York', time_in),  'HH24:MI:SS')";
   $HISTORY_QUERY = "SELECT $COLUMNS FROM " . getBreaksTableName() . " WHERE " .
                    "DATE(time_out) = CURRENT_DATE ORDER BY time_out";

   $entries = fetchQueryResults($HISTORY_QUERY);

   echo "<table border=1>\n";

   echo "<th>Name</th>\n";
   echo "<th>Break Type</th>\n";
   echo "<th>Pass Type</th>\n";
   echo "<th>Time Out</th>\n";
   echo "<th>Time In</th>\n";

   $hidden_html_ids = "0"; // prefix with an invalid ID

   while ( $entry = pg_fetch_row($entries) )
   {
      $id          =  $entry[0];
      $break_type  =  $entry[1];
      $pass_type   =  $entry[2];
      $time_out    =  $entry[3];
      $time_in     =  $entry[4];

      if ($time_out == $time_in)
      {
         $hidden_html_ids = $hidden_html_ids . "_" . $id;

         $time_in = "NA";
      }

      echo "\t<tr>\n";

      echo "\t\t<td id='id_to_name_" . $id . "'>$id</td>\n";
      echo "\t\t<td id='break_type_" . $id . "'>$break_type</td>\n";
      echo "\t\t<td id='pass_type_"  . $id . "'>$pass_type</td>\n";
      echo "\t\t<td id='time_out_"   . $id . "'>$time_out</td>\n";
      echo "\t\t<td id='time_in_"    . $id . "'>$time_in</td>\n";

      echo "\t</tr>\n";
   }

   echo '<input type="hidden" id="' . getHiddenFieldId() . '" name="checkedout_ids" value="' . $hidden_html_ids . '">';

   echo "</table>\n";
}

?>
