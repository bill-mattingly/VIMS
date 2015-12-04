

$(function() {
	$("#datepicker").datepicker(
	{
		//beforeShowDay: $.datepicker.noWeekends
		dateFormat: "mm-dd-yy",
		changeYear: true,
		minDate: 0 //"0" means that days before whatever date "today" is are not selectable in the calendar
	});
});

			

function SelectPatientPrice()
{
	$('.patientType:checked').one("change", function(eventObject){  //.click(function(eventObject){
		
		$("#customerChrg").val($(this).data('price'));
		
		//document.getElementById('customerChrg').value = $(this).data('price');
		//$(".patientType").off();//("change");
	});
}

// function SelectBarcodeAction()
// {
// 	$('.navlink').click(function(){
		
// 	});
// }
