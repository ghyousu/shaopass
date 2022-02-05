<html>
  <head>
     <title>File Uploader</title>

     <?php
         if ( isset($_FILES["filesToUpload"]["name"]) )
         {
            $numFiles = count($_FILES["filesToUpload"]["name"]);

            // die(var_dump($_FILES));

            for ($i=0; $i<$numFiles; ++$i)
            {
               $input_basename = basename($_FILES["filesToUpload"]["name"][$i]);

               if ($input_basename == "")
               {
                  continue;
               }

               $target_file = $input_basename;

               // Check if file already exists
               if (file_exists($target_file)) 
               {
                  echo "ERROR: file $target_file already exists.<br/>";
               }

               else if (move_uploaded_file($_FILES["filesToUpload"]["tmp_name"][$i], $target_file)) 
               {
                  echo $input_basename . " uploaded successfully.<br/>";
               } 
               else 
               {
                  echo "ERROR: failed to upload '" . $input_basename . "'<br/>";
               }

               flush();
               ob_flush();
            }
         }
     ?>
  </head>

  <body>
     <br/>

     <table align="center">
        <form action="<?php echo $_SERVER["REQUEST_URI"] ?>" method="post" 
                 enctype="multipart/form-data">
           <?php
              for ($i=0; $i<10; ++$i)
              {
                 echo '<tr><td>';
                 echo '<input style="font-size: 2em" type="file" name="filesToUpload[]">';
                 echo '</td></tr>';
                 echo '<tr><td></td></tr>';
              }
           ?>
           <tr>
              <td>
                   <input style="font-size: 2em" type="submit" value="Upload File" name="submit">
              </td>
              <td>
                   <a href="<?php echo dirname($_SERVER['SCRIPT_NAME']) ?>"
                      style="font-size : 2em;">
              One level Up</a>
              </td>
           </tr>
        </form>
     </table>

  </body>

</html>
