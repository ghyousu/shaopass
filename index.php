<html>
  <head>
     <title>Directory Listing</title>
     <script type="text/javascript">
      function selectAllClicked(e)
      {
         var checkboxes = document.getElementsByName( "check_list[]" );
         var numElems = checkboxes.length;
         for ( i=0; i<numElems; i++ )
         {
            if (checkboxes[i].value.split('.').pop() != "php")
            {
              checkboxes[i].checked = true;
            }
         }
         e.preventDefault(); // don't actually submit
      }

      function unselectAllClicked(e)
      {
         var checkboxes = document.getElementsByName( "check_list[]" );
         var numElems = checkboxes.length;
         for ( i=0; i<numElems; i++ )
         {
            checkboxes[i].checked = false;
         }
         e.preventDefault(); // don't actually submit
      }

      function rename_selected(e)
      {
         var from_str = document.getElementById("from_re").value;
         var to_str   = document.getElementById("to_re").value;

         if (from_str == "")
         {
            alert("You need to specify the 'From' text box");
            e.preventDefault(); // don't actually submit
            return ;
         }

         if (to_str == "")
         {
            alert("You need to specify the 'To' text box");
            e.preventDefault(); // don't actually submit
            return ;
         }

         if (from_str == to_str)
         {
            alert("'From' and 'To' are the same, do nothing");
            e.preventDefault(); // don't actually submit
            document.getElementById("to_re").value = "CHANGE_ME";
            return ;
         }

         // verify there's at least one file checked
         var checkboxes = document.getElementsByName( "check_list[]" );
         var numElems = checkboxes.length;
         var has_file_selected = false;
         for ( i=0; i<numElems; i++ )
         {
            if (checkboxes[i].checked)
            {
                has_file_selected = true;
                break;
            }
         }

         if (!has_file_selected)
         {
            alert("No files are selected. Select some files to be renamed");
            e.preventDefault(); // don't actually submit
         }

         console.log("Renaming files from '" + from_str + "' to '" + to_str + "'");
      }

     </script>

     <?php
         setlocale(LC_ALL,'C.UTF-8');

         session_start();

         if ($_SERVER['PHP_SELF'] == '/mason/index.php' ||
             $_SERVER['PHP_SELF'] == '/kidsMusic/index.php' ||
             $_SERVER['PHP_SELF'] == '/jplayer-2.9.2/index.php')
         {
            $require_login = true;
         }

         if ($require_login && (!isset($_SESSION['LOGGED_IN'])))
         {
            header("location: /login.php");
         }

         function myExec($cmd, $is_debug)
         {
             $output = "NA_OUTPUT";
             $ret_err_code = "-12345";

             $lastLineOutput = exec($cmd, $output, $ret_err_code);

             if ($is_debug)
             {
                echo "Cmd: $cmd<br/>";
                echo "Output: $outpu<br/>";
                echo "ret_err_code: $ret_err_code<br/>";
                echo "lastLineOutput: $lastLineOutput<br/>";
             }
         }

         // if it's triggered by the "Delete Selected" button, perform
         // file deletion and return to the same page
         // echo print_r($_POST) . "<br/>";

         if ($_SERVER['REQUEST_METHOD'] === 'POST')
         {
            $action = "DELETE";
            if (isset($_POST['rename_selected_files']))
            {
               $action = "RENAME";
            }

            if ( isset($_POST['check_list']) )
            {
               $selectedArray = $_POST['check_list'];
               $numSelected   = count( $selectedArray );

               // for rename function start
               $from_str = $_POST['from_re'];
               $to_str   = $_POST['to_re'];
               $is_dry_run = false;

               if (isset($_POST['rename_dry_run']))
               {
                  $is_dry_run = (count($_POST['rename_dry_run']) > 0);
               }

               $dry_run_opt = "";
               if ($is_dry_run)
               {
                  $dry_run_opt = "-n";
               }
               // for rename function end

               for ($i=0; $i<$numSelected; $i++)
               {
                  $tbdFile = $selectedArray[$i];

                  if ($action == "DELETE")
                  {
                     $cmd = "rm -fv \"$tbdFile\" ";
                     // echo "debug: cmd = '" . $cmd . "'";
                     myExec( $cmd, false);
                  }
                  else if ($action == "RENAME")
                  {
                     $file_dir = dirname($tbdFile);

                     $to_be_renamed = basename($tbdFile);

                     myExec( "cd $file_dir && rename -v 's/$from_str/$to_str/' \"$to_be_renamed\" $dry_run_opt ", $is_dry_run );
                  }
               }

               // if (isset($_SERVER["HTTP_REFERER"]))
               // {
               //    header("Location: " . $_SERVER["HTTP_REFERER"]);
               // }
            }
         }
     ?>
  </head>

  <body>
     <?php
        // var_dump( $_SERVER ); // debug
        // echo "Debug: " . print_r( $_SERVER ) . "<br/>";

        $documentRoot    = $_SERVER["DOCUMENT_ROOT"];

        $thisScriptLinux = $_SERVER["SCRIPT_FILENAME"];
        $thisScriptBase  = basename($_SERVER["SCRIPT_FILENAME"]);
        $thisDirLinux    = dirname($thisScriptLinux);

        $thisScriptWeb   = $_SERVER["SCRIPT_NAME"];
        $thisDirWeb      = dirname($thisScriptWeb);
        $parentDirWeb    = dirname($thisDirWeb);

        // open pointer to directory and read list of files
        $d = @dir($thisDirLinux) or die("Failed opening directory $thisDirLinux for reading");

        $files = [];
        while (false !== ($entry = $d->read()))
        {
           array_push( $files, $entry );
        }
        sort($files);
        $d->close();

        foreach ($files as $file)
        {
           // skip hidden files
           if ($file[0] == ".") continue;

           if ($file == $thisScriptBase) continue;

           // These are files used for heroku. don't display them
           if ($file == "readme") continue;
           if ($file == "vendor") continue;
           if ($file == "Procfile") continue;
           if ($file == "login.php") continue;
           if ($file == "web_shell.php") continue;
           if ($file == "mason") continue;
           if ($file == "youtube-dl") continue;
           if ($file == "composer.json") continue;
           if ($file == "jplayer-2.9.2") continue;
           if ($file == "4c9184f37cff01bcdc32dc486ec36961") continue;
           if ($file == "5c29c2e513aadfe372fd0af7553b5a6c") continue;
           if ($file == "updateYTD.bash") continue;

           if ($thisScriptWeb == '/index.php')
           {
             if ($file == "youtube_dl.php") continue;
             if ($file == "m4aPlaylist.php") continue;
             if ($file == "mp3Playlist.php") continue;
             if ($file == "videoPlaylist.php") continue;
             if ($file == "uploadFile.php") continue;
           }

           $htmlRefFilename = "$thisDirWeb/$file";

           if ( $thisDirWeb == "/" )
           {
              $htmlRefFilename = "/$file";
           }

           if (is_dir("$thisDirLinux/$file"))
           {
              $retval[] = array(
                    "name" => $htmlRefFilename,
                    "type" => filetype("$thisDirLinux/$file"),
                    "size" => 0,
                    "lastmod" => filemtime("$thisDirLinux/$file")
                    );
           }
           elseif (is_readable("$thisDirLinux/$file"))
           {
              $retval[] = array(
                    "name" => $htmlRefFilename,
                    "type" => mime_content_type("$thisDirLinux/$file"),
                    "size" => filesize("$thisDirLinux/$file"),
                    "lastmod" => filemtime("$thisDirLinux/$file")
                    );
           }
        }

        echo "<h1>Index of $thisDirWeb</h1>";
     ?>

      <table>
         <tr>
            <th valign="top"><img src="/icons/blank.gif" alt="[ICO]"></th>
            <th><a href="?C=D;O=A">Delete File</a></th>
            <th><a href="?C=S;O=A">Size</a></th>
            <th><a href="?C=N;O=A">Name</a></th>
            <th><a href="?C=M;O=A">Last modified</a></th>
         </tr>

         <tr><td colspan="10"><hr></td></tr>

         <tr>
            <td valign="top"><img src="/icons/back.gif" alt="[PARENTDIR]"></td>
            <td><a href="<?php echo $parentDirWeb ?>">Parent Directory</a></td>
            <td>&nbsp;</td>
            <td align="right">  - </td>
            <td>&nbsp;</td>
         </tr>

         <form action="<?php echo $_SERVER["REQUEST_URI"] ?>" method="POST">
         <?php
            // copied from http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
            function human_filesize($bytes, $decimals = 2)
            {
               $size = array('','K','M','G','T','P','E','Z','Y');
               // echo "size = strlen($bytes)<br/>";
               $factor = floor((strlen($bytes) - 1) / 3);
               return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
            }

            $TOTAL_SIZE = 0;

            for ( $i = 0; $i < count($retval); ++$i )
            {
               $filename = $retval[$i]["name"];

               // drupal.org/project/drupal/issues/278425
               // "basename" function is no locale safe
               $fileBasename = basename($filename);

               $TOTAL_SIZE += $retval[$i]["size"];

               echo "<tr>\n";

               echo ' <td align="right">' . "\n";
               echo '   <input type="checkbox" name="check_list[]" value="';
               echo        "$documentRoot/$filename" . '">' . "\n";
               echo " </td>\n";

               // download button for files
               echo " <td align='center'>\n";
               if ($retval[$i]["type"] != "dir")
               {
                  echo "   <a href='$filename' download>Download</a>\n";
               }
               echo " </td>\n";

               echo ' <td align="right">' . human_filesize($retval[$i]["size"]) . "</td>\n";

               echo " <td>\n";
               echo '   <a href="' . $filename . '">' . $fileBasename . "</a>\n";
               echo " </td>\n";

               echo ' <td align="right">' . date('Y-m-d h:i', $retval[$i]["lastmod"]) . "</td>\n";

               echo "</tr>\n";
            }
         ?>

         <tr><td colspan="10"><hr></td></tr>

         <tr>
            <td align="left" colspan="2">
                  <input type="submit" name="submit" Value="Delete Selected"/>
            </td>
            <td align="left">
                  <input type="submit" name="selectAll" Value="Select All"
                     onclick='selectAllClicked(event)'/>
            </td>
            <td align="left">
                  <input type="submit" name="unselectAll" Value="UnSelect All"
                     onclick='unselectAllClicked(event)'/>
            </td>
            <td align="right">
                  Total:
            </td>
            <td align="right">
                  <?php echo human_filesize($TOTAL_SIZE) ?>
            </td>
         </tr>

         <tr>
            <td>From: </td>
            <td align="right">
                <input type="text" id="from_re" name="from_re" />
            </td>

            <td align="right">To: </td>
            <td align="left">
                <input type="text" id="to_re" name="to_re" />
            </td>

            <td align="left">
                  <input type="submit" name="rename_selected_files" Value="Rename"
                     onclick='rename_selected(event)'/>
            </td>

            <td align="left">
                 <input type="checkbox" name="rename_dry_run[]" value="Dry Run" />
            </td>
         </tr>
         </form>

         <tr><td colspan="10"><hr></td></tr>
      </table>

      <address>Mason's custom directory listing</address>

  </body>

</html>
