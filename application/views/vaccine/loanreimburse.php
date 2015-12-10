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

				<div class='row'>
					<div class='col-md-12'>
					<input id='reimburseCash' type='radio' name='loanReimburseType' value='cash' checked>
					<label for='reimburseCash'>Cash Payment:</label>
					
					<input id="reimburseVials" type='radio' name='loanReimburseType' value='vials'>
					<label for='reimburseVials'>Vial Reimbursement:</label><br/>

					<input id="partialPayment" type='checkbox' name='partialPayment'>
					<label for="partialPayment">Partial Repayment?</label><br/>
					</div> <!-- /End .col-md-12 -->
				</div> <!-- /End .row -->

				<div class="row">
					<div class='col-md-6'>
						<h2>Loan Information</h2>
						<label>Vaccine:</label>
						<input id='vacName' type='text' disabled><br/>

						<label>Lot#:</label>
						<input id='lotNum' type='text' disabled><br/>

						<label>Expiration:</label>
						<input id='expireDate' type='text' disabled><br/>

						<label>Quantity:</label>
						<input id='loanQty' type='text' disabled><br/>

						<label>Loan Date:</label>
						<input id='loanDate' type='text' disabled><br/>

						<label>Borrower:</label>
						<input id='borrowerName' type='text' disabled><br/>

						<label>Signer:</label>
						<input id='loanSigner' type='text' disabled><br/>
					</div> <!-- /End col-md-6 -->

					<div class='col-md-6'>
						<h2>Reimbursement Form</h2>
						<label for='reimburseSigner'>Reimbursing Person:</label>
						<input id='reimburseSigner' type='text' name='reimburseSigner' placeholder='Person Returning Money'><br/>

						<label id="lblReimburseQty" for="reimbursement">Reimbursement Amount:</label>
						<input id="reimburseQty" type="text" name='reimbursement' placeholder="Enter Monetary Value"><br/>
					</div> <!-- /End col-md-6 -->
				</div> <!-- /End .row -->

			</div> <!-- /End .modal-body -->
			<div class='modal-footer'>
				<div class='row col-md-12'>
					<button id="btnReimburse" type="button" class="btn btn-default" data-dismiss="modal">Submit</button>
					<button id="btnClose" type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div> <!-- /End row col-md-12 -->
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
					
					var loanID = null; //assigned within for loop
					var borrowerID = null; //assigned within for loop
					var currentLoan = element; //Gets the loan currently being iterated over by the forEach loop
					
					var objKeysArray = Object.keys(element); //Array to be iterated over in "for" loop
					var key = null; //Stores the current key from the objectKeysArray
					var rowSize = objKeysArray.length; //Loop control variable (upper bound) 

					var counter; //Loop control variable

					for(counter = 0; counter < rowSize; counter++)
					{
						key = objKeysArray[counter];

						if(key != 'Loan ID' && key != 'Borrower ID')
						{
							tableData += "<td>" + currentLoan[key] + "</td>";
						}
						else if(key == 'Loan ID')
						{
							loanID = currentLoan[key];
						} //End else if
						else if(key == 'Borrower ID')
						{
							borrowerID = currentLoan[key];
						} //End else if
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
					tableData += "<td><input id='checkBoxLoanID" + loanID + "' type='checkbox' value='" + loanID + "' data-loanID='" + loanID + "' data-borrowerID='" + borrowerID + "' data-toggle='modal' data-target='#loanModal'></td>";

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

// // This script parses several attributes to be used by the modal dialog box with the id photo-modal in team.php
// $('#loanModal').on('show.bs.modal', function (event) {
  
// 	//Get loan data to display in the dialog box


// 	// console.log(event);
// 	var element = event.relatedTarget;
// 	var loanID = $(element).data('loanid');




// //Begin original code

// //  var thumb = $(event.relatedTarget);		// Thumbnail that triggered the modal
// //  var photoSrc = thumb.attr("src");			// Extract image's src attribute
// //  var pieces = photoSrc.split("/");			// Break photo source URL into pieces for parsing
// //  var photoFile = pieces[pieces.length-1];	// Select last index in pieces array for filename
// //  var photoDir = "photos/large/";			// Directory that large versions of photos are stored in
// //  var name = thumb.attr("alt");				// Grab employee's name from image's alt text attribute

// //  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
// //  var modal = $(this);
// //  modal.find('#modal-employeeName').text(name);					//Place employee name in title portion of modal
// //  modal.find('#modal-photo').attr("src", photoDir + photoFile);	//Set image source to large version of employee photo

// //End original code


// }); //End #loanModal.on('show.bs.modal')

// //Get selected row to store all loan data in session variables (for populating the modal dialog with loan details)
// $("input[type='checkbox']").on('mousedown', function(){
	
// 	//Get selected row 
// 	var selectedRow = $(this).closest('tr');


// 	//Get the selected row's loan information
// 	var loanid = $("input[type='checkbox']:checked").data('loanid');
// 	var borrowerid = $("input[type='checkbox']:checked").data('borrowerid');

// 	var vacName = selectedRow[0].innerHtml;
// 	var borrowerName = selectedRow[1].innerHtml;
// 	var loanSigner = selectedRow[2].innerHtml;
// 	var loanDate = selectedRow[3].innerHtml;

// 	var lotNum = selectedRow[4].innerHtml;
// 	var expireDate = selectedRow[5].innerHtml;
// 	var loanQty = selectedRow[6].innerHtml;

// 	//Store loan information in a session variable (to be accessed by the modal dialog)
// 	sessionStorage.setItem('loanID', loanID);
// 	sessionStorage.setItem('borrowerID', borrowerID);
// 	sessionStorage.setItem('vacName', vacName);
// 	sessionStorage.setItem('borrowerName', borrowerName);

// 	sessionStorage.setItem('loanSigner', loanSigner);
// 	sessionStorage.setItem('loanDate', loanDate);
// 	sessionStorage.setItem('lotNum', lotNum);
// 	sessionStorage.setItem('expireDate', expireDate);
// 	sessionsStorage.setItem('loanQty', loanQty);

// }); //End $(#outstandingLoansTbl tr).click()

//Populates modal dialog with loan information from sessionStorage variable
$('#loanModal').on('show.bs.modal', function(event){
	
	var checkBox = event.relatedTarget;

	var count = 0;

	var selectedRow = $(checkBox).closest('tr');

	var loanDataArray = [];

	$(selectedRow).find('td').each(function(){
		var cell = $(this).html();
		console.log(cell);
		loanDataArray.push(cell);
	}); //End .each()

	//Populate loan information modal text boxes
	$("#vacName").val(loanDataArray[0]);
	$("#lotNum").val(loanDataArray[4]);
	$("#expireDate").val(loanDataArray[5]);

	$("#loanQty").val(loanDataArray[6]);
	$("#loanDate").val(loanDataArray[3]);
	$("#borrowerName").val(loanDataArray[1]);
	$("#loanSigner").val(loanDataArray[2]);


}); //End #loanModal.on('show.bs.modal')

//Unchecks the selected loan when the modal dialog box is closed
$('#loanModal').on('hide.bs.modal', function (event){
	$("input[type='checkbox']:checked").prop('checked', false);
}); //End #loanModal.on('hide.bs.modal')

//Controls events for the radio buttons on the modal dialog box
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

//Controls the "Submit" button on the modal dialog box
$("#btnReimburse").click(function(){
	//console.log("Submit btn clicked");
}); //End #btnReimburse.click()

//Controls the "Cancel" button on the modal dialog box
$("#btnClose").click(function(){

}); //End #btnClose.click()

</script>