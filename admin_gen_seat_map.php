<?php

   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   $NUM_ROWS_PER_CLASS  = 6;
   $NUM_COLUMNS_PER_ROW = 6;

   function getSeatChkboxId()    { return 'seating_cell_chk_boxes'; }
   function getSeatChkboxName()  { return getSeatChkboxId(); }
?>

<script type="text/javascript">

</script>

<h2>
   Click "Generate" button to generate seating map(s)
</h2>

<br/>

<table border=0>
   <form target="_blank" action='/admin_show_printable_seating_map.php' method='POST'>

   <tr>
      <td colspan=<?php echo $NUM_COLUMNS_PER_ROW; ?> >
         <div align='center' style='font-size: 2em'><b>Back</b></div>
      </td>
   </tr>

   <?php for ($row=$NUM_ROWS_PER_CLASS; $row>0;--$row): ?>
      <tr>
      <?php for ($col=1; $col<=$NUM_COLUMNS_PER_ROW; ++$col): ?>
         <td width="200" height="50" align='center' style='padding-bottom: 20px; font-size: 1.5em'>
            <input style='width: 30px; height: 30px' type='checkbox'
              name='<?php echo getSeatChkboxName() . "[]"; ?>'
              value='<?php echo $row . "_" . $col; ?>'
            >
            <?php echo "Row $row, Col $col"; ?>
            </input>
         </td>
      <?php endfor; ?>
      </tr>
   <?php endfor; ?>

   <tr>
      <td colspan=<?php echo $NUM_COLUMNS_PER_ROW; ?> >
         <div align='center' style='font-size: 2em'><b>Front</b></div>
      </td>
   </tr>

   <!-- add the Generate button -->
   <tr>
      <td colspan='6' style="padding-top: 30px; padding-left: 75%">
         <input type='submit' name='gen_seating_map'
               value='Generated Seating Map' style='font-size: 1.5em' />
      </td>
   </tr>

   </form>
</table>

</div>

