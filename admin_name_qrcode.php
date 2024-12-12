<?php

   require_once("common.php");
   setlocale(LC_ALL,'C.UTF-8');
   session_start();

   $all_students = getAllStudents();
   $num_students = count($all_students);

   $NUM_ROWS            = 5;
   $NUM_COLUMNS_PER_ROW = 2;
?>

<style>

.page_breaker {
   break-before: page;
}

</style>

<table id='main_table' border=1>
   <?php for ($arr_index=0; $arr_index<$num_students; ++$arr_index): ?>

       <?php if ($arr_index % $NUM_COLUMNS_PER_ROW == 0) : ?>
          <?php if ($arr_index % $NUM_ROWS == 0) : ?>
             <tr class='page_breaker'>
          <?php else : ?>
             <tr>
          <?php endif; ?>
       <?php endif; ?>

          <td class='stud_name'>
             <?php echo $all_students[$arr_index]->fname . " " . substr($all_students[$arr_index]->lname, 0, 1) . ".";  ?>
          </td>
          <td class='qrcode'>
             <img src='/imgs/<?php echo $all_students[$arr_index]->student_id . ".png'"; ?>
          </td>

       <?php if (($arr_index+1) % $NUM_COLUMNS_PER_ROW == 0) : ?>
       </tr>
       <?php endif; ?>

   <?php endfor; ?>
</table>

</div>

