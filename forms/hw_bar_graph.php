<style>

body, table, input, select, textarea {
}

.graph {
   margin-bottom:1em;
  font:normal 100%/150% arial,helvetica,sans-serif;
}

.graph caption {
   font:bold 150%/120% arial,helvetica,sans-serif;
   padding-bottom:0.33em;
}

.graph tbody th {
   text-align:right;
}

@supports (display:grid) {
   @media (min-width:32em) {
      .graph {
         display:block;
         width:400px;
         height:200px;
      }

      .graph caption {
         display:block;
      }

      .graph thead {
         display:none;
      }

      .graph tbody {
         position:relative;
         display:grid;
         grid-template-columns:repeat(auto-fit, minmax(2em, 1fr));
         column-gap:2.5%;
         align-items:end;
         height:100%;
         margin:3em 0 1em 2.8em;
         padding:0 1em;
         border-bottom:2px solid rgba(0,0,0,0.5);
         background:repeating-linear-gradient(
            180deg,
            rgba(170,170,170,0.7) 0,
            rgba(170,170,170,0.7) 1px,
            transparent 1px,
            transparent 20%
         );
      }

      .graph tbody:before,
      .graph tbody:after {
         position:absolute;
         left:-3.2em;
         width:2.8em;
         text-align:right;
         font:bold 80%/120% arial,helvetica,sans-serif;
      }

      .graph tbody:before {
         content:"100%";
         top:-0.6em;
      }

      .graph tbody:after {
         content:"0%";
         bottom:-0.6em;
      }

      .graph tr {
         position:relative;
         display:block;
      }

      .graph tr:hover {
         z-index:999;
      }

      .graph th,
      .graph td {
         display:block;
         text-align:center;
      }

      .graph tbody th {
         position:absolute;
         top:-3em;
         left:0;
         width:80%;
         font-weight:normal;
         text-align:center;
         white-space:nowrap;
         text-indent:0;
         transform:rotate(-45deg);
      }

      .graph tbody th:after {
         content:"";
      }

      .graph td {
         width:80%;
         height:100%;
         border-radius:0.5em 0.5em 0 0;
         transition:background 0.5s;
      }

      .graph tr:hover td {
         opacity:0.7;
      }

      .graph td span {
         overflow:hidden;
         position:absolute;
         left:50%;
         top:50%;
         width:0;
         padding:0.5em 0;
         margin:-1em 0 0;
         font:normal 85%/120% arial,helvetica,sans-serif;
/*          background:white; */
/*          box-shadow:0 0 0.25em rgba(0,0,0,0.6); */
         font-weight:bold;
         opacity:0;
         transition:opacity 0.5s;
         color:white;
      }

      .toggleGraph:checked + table td span,
      .graph tr:hover td span {
         width:4em;
         margin-left:-2em; /* 1/2 the declared width */
         opacity:1;
      }
   } /* min-width:32em */
} /* grid only */

</style>

<?php
   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   if (!isset($_SESSION['LOGGED_IN']))
   {
      header("location: /login.php");
   }

   $class_id = $_SESSION['class_id'];
   $stud_array = getStudentsForHWTracker($class_id);

   $total_students = count($stud_array);
   $num_incomplete = 0;
   $num_semicomplete = 6;
   $num_completed = 0;

   for ($i = 0; $i<$total_students; ++$i)
   {
      if ($stud_array[$i]->today_hw_status == 'completed')
      {
         $num_completed += 1;
      }
      else if ($stud_array[$i]->today_hw_status == 'semi-complete')
      {
         $num_semicomplete += 1;
      }
      else if ($stud_array[$i]->today_hw_status == 'incomplete')
      {
         $num_incomplete += 1;
      }
      else
      {
         die("unexpected hw_status: (" . $stud_array[$i]->today_hw_status . ')');
      }
   }

   $percent_incomplete   = $num_incomplete   / $total_students;
   $percent_semicomplete = $num_semicomplete / $total_students;
   $percent_completed    = $num_completed    / $total_students;
?>

<table class="graph">
   <caption>HW submissions</caption>
   <tbody>
      <tr <?php echo 'style="height:' . ($percent_incomplete * 100) . '%"'; ?> >
         <th scope="row">incomplete</th>
         <td style='background: red'><span> <?php echo $num_incomplete; ?> </span></td>
      </tr>
      <tr <?php echo 'style="height:' . ($percent_semicomplete * 100) . '%"'; ?> >
         <th scope="row">semi-complete</th>
         <td style='background: orange'><span> <?php echo $num_semicomplete; ?> </span></td>
      </tr>
      <tr <?php echo 'style="height:' . ($percent_completed * 100) . '%"'; ?> >
         <th scope="row">completed</th>
         <td style='background: green'><span> <?php echo $num_completed; ?> </span></td>
      </tr>
   </tbody>
</table>
