function atLeastOneChecked(radio_grp_name)
{
   var radios = document.getElementsByName(radio_grp_name);

   for (var i = 0, len = radios.length; i < len; i++) {
      if (radios[i].checked) {
         return true;
      }
   }

   return false;
}

function selectAllClicked()
{
   debugger;

   var btn_elem = document.getElementById('select_all_btn');
   var btn_text = btn_elem.value;

   var chk_boxes_elem = document.getElementsByName('<?php echo getStudentNameChkboxName() . "[]"; ?>');

   if (btn_text == 'Select All')
   {
      btn_elem.value = "De-Select All";

      for (var i in chk_boxes_elem)
      {
         chk_boxes_elem[i].checked = true;
      }
   }
   else
   {
      btn_elem.value = "Select All";

      for (var i in chk_boxes_elem)
      {
         chk_boxes_elem[i].checked = false;
      }
   }
}

function submitClicked(event)
{
   debugger;
   var alert_text_elem = document.getElementById('alert_text');
   var show_alert = false;

   // var chk_boxes_elem = document.getElementsByName('<?php echo getStudentNameChkboxName() . "[]"; ?>');

   if (false == atLeastOneChecked('<?php echo getStudentNameChkboxName() . "[]"; ?>'))
   {
      alert_text_elem.innerText = "Select at least one student name";
      show_alert = true;
   }

   if (!show_alert && false == atLeastOneChecked('<?php echo getColorSelRadioBtnGrpName(); ?>'))
   {
      alert_text_elem.innerText = "You need to make a color selection";
      show_alert = true;
   }

   if (show_alert)
   {
      // debugger;
      var alertDivElem = document.getElementById('my_custom_alert_id');
      alertDivElem.style.display = "block";

      event.preventDefault();
      return ;
   }
}

function closeBtnClicked()
{
   document.getElementById('my_custom_alert_id').style.display = "none";

   setBackgroundColorByClassName('breakTypesTable', 'none');
   setBackgroundColorByClassName('passTypesTable', 'none');

   event.preventDefault();
   return ;
}
