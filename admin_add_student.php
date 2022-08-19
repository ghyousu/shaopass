<?php

   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   // maximum number of students can be added per page
   $MAX_NUM_ADDTIONS = 10;

   function getFNameHtmlId($index) { return 'fname_' . $index; }
   function getLNameHtmlId($index) { return 'lname_' . $index; }
   function getHiddenClassId()     { return 'hidden_class_name'; }

   function getFNameHtmlName($index) { return getFNameHtmlId($index); }
   function getLNameHtmlName($index) { return getLNameHtmlId($index); }
   function getHiddenClassName()     { return getHiddenClassId(); }

   if ($_SERVER['REQUEST_METHOD'] === 'POST')
   {
      // var_dump($_POST);
      // sample $_POST
      // array(21) { ["fname_1"]=> string(5) "mason" ["lname_1"]=> string(3) "you" ["fname_2"]=> string(0) "" ["lname_2"]=> string(0) "" ["fname_3"]=> string(0) "" ["lname_3"]=> string(0) "" ["fname_4"]=> string(0) "" ["lname_4"]=> string(0) "" ["fname_5"]=> string(0) "" ["lname_5"]=> string(0) "" ["fname_6"]=> string(0) "" ["lname_6"]=> string(0) "" ["fname_7"]=> string(0) "" ["lname_7"]=> string(0) "" ["fname_8"]=> string(0) "" ["lname_8"]=> string(0) "" ["fname_9"]=> string(7) "shannan" ["lname_9"]=> string(3) "you" ["fname_10"]=> string(0) "" ["lname_10"]=> string(0) "" ["add_students"]=> string(6) "Submit" }

      $class = $_POST[getHiddenClassName()];
      for ($i=1; $i<=$MAX_NUM_ADDTIONS; ++$i)
      {
         $fname = trim($_POST[getFNameHtmlName($i)]);
         $lname = trim($_POST[getLNameHtmlName($i)]);

         // NOTE: depending on javascript for input validation
         if (strlen($fname) > 0 && strlen($lname) > 0)
         {
            if (insertNewStudent($fname, $lname, $class))
            {
               printInfo("Successfully added '$fname' '$lname' to class '$class'", 1);
            }
         }
      }
   }
?>

  <script type="text/javascript">
   function validate_input(event)
   {
      debugger;
      var num_rows = <?php echo $MAX_NUM_ADDTIONS; ?>;

      // validate all the rows to make sure no rows are missing fname or lname
      var error_string = "";
      for (i=1; i<=num_rows; ++i)
      {
         var fname_id = "fname_" + i;
         var lname_id = "lname_" + i;

         var fname = document.getElementById(fname_id).value.trim();
         var lname = document.getElementById(lname_id).value.trim();

         if (fname.length > 0 && lname.length > 0)
         {
            console.log("valid entry for row " + i + " for '" + fname + "' '" + lname + "'");
            continue;
         }
         else if (fname.length == 0 && lname.length == 0)
         {
            console.log("empty entry for row " + i);
            continue;
         }
         else
         {
            error_string += "row " + i + " missing First Name or Last Name\n";
         }
      }

      if (error_string.length > 0)
      {
         event.preventDefault();
         alert(error_string);
         return ;
      }
      else
      {
         // fill in the class name to the hidden field
         var class_id          = document.getElementById('<?php echo getClassDropDownId(); ?>');
         var hidden_class_elem = document.getElementById('<?php echo getHiddenClassId(); ?>');

         hidden_class_elem.value = class_id.value;
      }
   }

  </script>

<table border=0>
   <form action='/admin.php?action=add' method='POST'>
      <?php for ($i=1; $i<=$MAX_NUM_ADDTIONS; ++$i): ?>
      <tr>
         <td>
            <br/>
            <label style="font-size: 1.5em" for="<?php echo getFNameHtmlId($i) ?>">First Name:</label>
            <input style="width: 120px" type="text"
                id="<?php echo getFNameHtmlId( $i ); ?>"
                name="<?php echo getFNameHtmlName( $i ); ?>" >
         </td>

         <td>
            <br/>
            <label style="font-size: 1.5em" for="<?php echo getLNameHtmlId( $i ); ?>">Last Name:</label>
            <input style="width: 120px" type="text"
               id="<?php echo getLNameHtmlId( $i ); ?>"
               name="<?php echo getLNameHtmlName( $i ); ?>" >
          </td>
      </tr>
      <?php endfor; ?>

      <!-- add the submit button -->
      <tr>
         <td>
            <!-- NOTE: value is filled by javascript after input validation -->
            <input type="hidden" value='' name='<?php echo getHiddenClassName(); ?>' id='<?php echo getHiddenClassId(); ?>' />
         </td>

         <td style="padding-top: 20px; padding-left: 30%">
            <input type='submit' name='add_students' onclick="validate_input(event)" value='Submit' style='font-size: 1.5em' />
         </td>
      </tr>

   </form>
</table>
