<div class='row col-md-12'>

	<h1>Outstanding Loans</h1>

	<!-- Used to provide feedback to user -->
	<p id='userMsg'></p>

	<!-- Table listing loans -->
	<table id='outstandingLoansTbl' class='table table-bordered table-striped table-hover'>
	</table>

<!--
Display outstanding loans

Have check box next to each loan to "reimburse"

Specify cash/vaccine

If cash, specify amount

If vaccine, specify dose amount
-->

<div id="loanModal" class="modal fade">
	<div class="modal-dialog">
		<div class='modal-content'>
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h2 class="model-title">Loan Reimbursement</h2>
			</div> <!-- /End .modal-header -->
			<div class='modal-body'>
			<!--
				<input type="text" placeholder='hi'></br>
				<button>Submit</button>
			-->

				<label for='reimburseCash'>Cash Payment:</label>
				<input id='reimburseCash' type='radio' name='loanReimburseType' value='cash' checked>

				<label for='reimburseVials'>Vial Reimbursement:</label>
				<input id="reimburseVials" type='radio' name='loanReimburseType' value='vials'><br/>

				<label for='reimburseSigner'>Reimbursing Person:</label>
				<input id='reimburseSigner' type='text' name='reimburseSigner' placeholder='Person Returning Money'><br/>

				<label id="lblReimburseQty" for="reimbursement">Reimbursement Amount:</label>
				<input id="reimburseQty" type="text" name='reimbursement' placeholder="Enter Monetary Value"><br/>



			</div> <!-- /End .modal-body -->
			<div class='modal-footer'>
				<button id="btnReimburse" type="button" class="btn btn-default" data-dismiss="modal">Submit</button>
				<button id="btnClose" type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div> <!-- /End .modal-footer -->
		</div> <!-- /End .modal-content -->
	</div> <!-- /End .modal-dialog -->
</div> <!-- /End #loanModal -->

<!-- Full screen image to display for AJAX requests -->
<div id="AJAXPreloader"></div>

</div> <!-- /End row col-md-12 -->

<script>

//Creates a table of outstanding vaccine loans
function DisplayOutstandingLoans(sortCriteria){

	$.ajax({
		url: "<?php echo site_url('Inventory/GetOutstandingLoans'); ?>",
		method: "POST",
		data: {"SortCriteria": sortCriteria},
		dataType: "JSON",
		success: function(loanResult){
			//The "loanResult" return value is a JSON object
			//It has 3 properties: headerRow, tableData, and numLoans


			//Create table header row & add to the table
			var header = "<tr>"; //Opening tr tag
			var tableData = ""; //Declare and initialize variable
			var colCount = loanResult.headerRow.length + 1; //The "+1" accounts for the extra column (which is manually added) to allow the user to select which loan to review

			//For the javascript forEach() function, you must provide a callback function as the argument
			//See MDN link: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/forEach
			loanResult.headerRow.forEach(function(element, index, array)
			{
				header += "<th>" + element + "</th>";
			}); //End forEach

			//Add a heading for a "checkbox" column at the end of the header row to allow users to select loans for review
			header += "<th>Select Loan&lpar;s&rpar;</th></tr>";

			//Add header row to Outstanding Loans Table
			$("#outstandingLoansTbl").append(header);


			//Check loanCount (if > 0 then display loan data in table)
			if(loanResult.numLoans > 0)
			{
				//loanResult.tableData is an array of objects
				//Thus it has "2" arrays (the "outer" array accesses each row object & the "inner" array accesses the attributes of each object)
				loanResult.tableData.forEach(function(element, index, array){

					//Open row tag for a row object
					tableData += "<tr>"; 
					
					var loanID = null; //assigned within the inner forEach
					var currentLoan = element; //Gets the loan currently being iterated over by the forEach loop
					
					var objKeysArray = Object.keys(element); //Array to be iterated over in "for" loop
					var key = null; //Stores the current key from the objectKeysArray
					var rowSize = objKeysArray.length; //Loop control variable (upper bound) 

					var counter; //Loop control variable

					for(counter = 0; counter < rowSize; counter++)
					{
						key = objKeysArray[counter];

						if(key != 'Loan ID')
						{
							tableData += "<td>" + currentLoan[key] + "</td>";
						}
						else
						{
							loanID = currentLoan[key];
						}
					} //End for loop

					//Loop through the attributes of that row object
//					loanResult.tableData[index].forEach(function(element, index, array)
//					{
//						if(index != 0)
//						{
//							tableData += "<td>" + element + "</td>"
//						} //End if
//						else
//						{
//							loanID = element;
//						} //End else
//					}); //End "inner" forEach (accessing a specific row result object's attributes)

					//Add checkbox to final column of row
					tableData += "<td><input id='checkBoxLoanID" + loanID + "' type='checkbox' value='" + loanID + "' data-loanID='" + loanID + "' data-toggle='modal' data-target='#loanModal'></td>";

					//Close row tag
					tableData += "</tr>";

				}); //End "outer" forEach looping through the array of row result objects

 			} //End if
			else
			{
				tableData = "<tr><td colspan=" + colCount + ">No Current Outstanding Loans</td></tr>";
			} //End else

			//Add whatever rows are in "tableData" to the Outstanding Loans table
			$("#outstandingLoansTbl").append(tableData);


		}, //End success function
		error: function(errorResult){
			console.log("An error retrieving outstanding loans occurred");
		} //End error function

	}); //End .ajax()

} //End GetOutstandingLoans()

// function Callback_TableRow(element, index, array) //foreach(loanResult.headerRow as colName)
// {
// 	console.log("element[" + index + "] = " + element);
// }



// Modified from "team_photo.js" (happistudy.net), originally by Matt Grassman
// team_photo.js was adapted from code example: http://getbootstrap.com/javascript/#modals-related-target
// 12/9/2015

// This script parses several attributes to be used by the modal dialog box with the id photo-modal in team.php
$('#loanModal').on('show.bs.modal', function (event) {
  
 // console.log(event);
  var element = event.relatedTarget;
  var loanID = $(element).data('loanid');


//  var thumb = $(event.relatedTarget);		// Thumbnail that triggered the modal
//  var photoSrc = thumb.attr("src");			// Extract image's src attribute
//  var pieces = photoSrc.split("/");			// Break photo source URL into pieces for parsing
//  var photoFile = pieces[pieces.length-1];	// Select last index in pieces array for filename
//  var photoDir = "photos/large/";			// Directory that large versions of photos are stored in
//  var name = thumb.attr("alt");				// Grab employee's name from image's alt text attribute

//  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
//  var modal = $(this);
//  modal.find('#modal-employeeName').text(name);					//Place employee name in title portion of modal
//  modal.find('#modal-photo').attr("src", photoDir + photoFile);	//Set image source to large version of employee photo
}); //End #loanModal.on('show.bs.modal')

$('#loanModal').on('hide.bs.modal', function (event){
	$("input[type='checkbox']:checked").prop('checked', false);

}); //End #loanModal.on('hide.bs.modal')


$("input[type='radio']").click(function(){
	console.log("Radio button clicked!");

	var selectedRdo = $(this).val();

	if(selectedRdo == 'cash')
	{
		$("#reimburseQty").attr("placeholder", 'Enter Monetary Value');
		$("#reimburseSigner").attr('placeholder', 'Person Returning Money');	
	} //End if
	else
	{
		$("#reimburseQty").attr('placeholder', 'Enter Vial Quantity');
		$("#reimburseSigner").attr('placeholder', 'Person Returning Vials');

	} //End else

}); //End input[type='radio'].click()

$("#btnReimburse").click(function(){
	//console.log("Submit btn clicked");
}); //End #btnReimburse.click()

$("#btnClose").click(function(){

}); //End #btnClose.click()

</script>