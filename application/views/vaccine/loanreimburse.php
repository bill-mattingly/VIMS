<div class='row'>
	<div class='col-md-12'>

	<h1>Outstanding Loans</h1>

	<!-- Used to provide feedback to user -->
	<p id='userMsg'></p>

	<div id="filterLoans">
		<h2>Loan Sort Options</h2>
		<label for='filterAllLoans'>All:</label>
		<input id='filterAllLoans' class='filterLoanOptions' type='radio' name='loanFilter' value='all' checked>
		
		<label for='filterVacName'>By Vaccine Name:</label>
		<input id='filterVacName' class='filterLoanOptions' type='radio' name='loanFilter' value='vacName'>
		
		<label for='filterBorrower'>By Borrower:</label>
		<input id='filterBorrower' class='filterLoanOptions' type='radio' name='loanFilter' value='borrower'>
		
		<label for='filterSigner'>By Signer:</label>
		<input id='filterSigner' class='filterLoanOptions' type='radio' name='loanFilter' value='signer'>
		
		<label for='filterLoanDate'>By Loan Date:</label>
		<input id='filterLoanDate' class='filterLoanOptions' type='radio' name='loanFilter' value='loanDate'>
		
		<label for='filterLotNum'>By Lot Number:</label>
		<input id='filterLotNum' class='filterLoanOptions' type='radio' name='loanFilter' value='lotNum'>
		
		<label for='filterExpireDate'>By Expiration Date:</label>
		<input id='filterExpireDate' class='filterLoanOptions' type='radio' name='loanFilter' value='expireDate'>
		
		<label for='filterLoanedDoses'>By Loaned Dose Quantity:</label>
		<input id='filterLoanedDoses' class='filterLoanOptions' type='radio' name='loanFilter' value='doses'>
		<br/>

		<label id='filterOptionsLbl' for='filterCategoryOptions'>Filter Options:</label>
		<select id='filterCategoryOptions'>
			<option value='all' selected>Select Filter</option>
		</select> <!--Populated based on the selected radio button-->

	</div> <!-- /End #filterLoans -->

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
					<input id='rdoReimburseCash' type='radio' name='loanReimburseType' value='cash' checked>
					<label for='rdoReimburseCash'>Cash Payment:</label>
					
					<input id="rdoReimburseVials" type='radio' name='loanReimburseType' value='doses'>
					<label for='rdoReimburseVials'>Vial Reimbursement:</label><br/>

					<input id="partialPayment" type='checkbox' name='partialPayment'>
					<label for="partialPayment">Is Partial Repayment?</label><br/>
					</div> <!-- /End .col-md-12 -->
				</div> <!-- /End .row -->

				<div class="row">
					<div class='col-md-6'>
						<h2>Loan Information</h2>
						<label>Vaccine:</label>
						<input id='loanVacName' type='text' disabled><br/>

						<label>Lot#:</label>
						<input id='loanLotNum' type='text' disabled><br/>

						<label>Expiration:</label>
						<input id='loanExpireDate' type='text' disabled><br/>

						<label>Quantity:</label>
						<input id='loanQty' type='text' disabled><br/>

						<label>Loan Date:</label>
						<input id='loanDate' type='text' disabled><br/>

						<label>Borrower:</label>
						<input id='loanBorrowerName' type='text' disabled><br/>

						<label>Signer:</label>
						<input id='loanSigner' type='text' disabled><br/>
					</div> <!-- /End col-md-6 -->

					<div class='col-md-6'>
						<h2>Reimbursement Form</h2>
						<label for='reimburseSigner'>Payer Name:</label>
						<input id='reimburseSigner' type='text' name='reimburseSigner' placeholder='Person Returning Money'><br/>

<!--
						<label id="lblReimburseQty" for="reimbursement">Reimbursement Amount:</label>
						<input id="reimburseQty" type="text" name='reimbursement' placeholder="Enter Monetary Value"><br/>
-->

						<div id='reimburseCashFields'>
							<label id="lblReimburseAmount" for="reimburseAmount">Value:</label>
							<input id="reimburseAmount" type="text" name='reimburseAmount' placeholder="Enter Monetary Value"><br/>
						</div> <!-- /End #reimburseCash -->

						<div id='reimburseDosesFields'>
							<label id="lblLotNum" for="reimburseLotNum">Lot Number:</label>
							<input id="reimburseLotNum" type="text" name='reimburseLotNum' placeholder="Enter Monetary Value"><br/>

							<!--See jQuery datepicker widget: https://jqueryui.com/datepicker/ -->
							<label id="lblExpireDate" for="reimburseExpireDate">Expire Date:</label>
							<input id="reimburseExpireDate" type="text" name='reimburseExpireDate' placeholder="Expiration Date"><br/>

							<label id="lblReimburseDoseQty" for="reimburseDoseQty">Dose Qty:</label>
							<input id="reimburseDoseQty" type="text" name='reimburseDoseQty' placeholder="Dose Quantity"><br/>

						</div> <!-- /End #reimburseDoses -->

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

</div> <!-- /End col-md-12 -->
</div> <!-- /End row -->

<script>

//Used to trigger jQuery datepicker widget: https://jqueryui.com/datepicker/
$(function(){
	$("#reimburseExpireDate").datepicker({
		minDate: 0 //Specifies that the "earliest" date available in the widget is today (zero days from today)
	});
}); //End annonymous function

//Creates a table of outstanding vaccine loans. The "sortCriteria" variable comes from the value of the "Loan Filter Options" radio buttons
function DisplayOutstandingLoans(sortCriteria, filterCriteria){
	/*Options for sortCriteria: (this just orders the loans in the table - it doesn't filter them)
		'allLoans', 'vacName', 'borrower', 'signer', 'loanDate', 'lotNum', 'expireDate', 'doses'
	*/

	/*Options for filterCriteria: (this filters, but doesn't order, the loans in the table)
		Option is based on whatever is available & selected from the <select> element	
	*/

	//$("#filterCategoryOptions").val() == 'all') //Default option has been selected


	$.ajax({
		url: "<?php echo site_url('Inventory/GetOutstandingLoans'); ?>",
		method: "POST",
		data: {"SortCriteria": sortCriteria, 'FilterCriteria': filterCriteria},
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
			header += "<th>Select a Loan</th></tr>";

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
					var drugID = null; //assigned within for loop
					var currentLoan = element; //Gets the loan currently being iterated over by the forEach loop
					
					var objKeysArray = Object.keys(element); //Array to be iterated over in "for" loop
					var key = null; //Stores the current key from the objectKeysArray
					var rowSize = objKeysArray.length; //Loop control variable (upper bound) 

					var counter; //Loop control variable

					for(counter = 0; counter < rowSize; counter++)
					{
						key = objKeysArray[counter];

						switch(key)
						{
							case "Loan ID":
								loanID = currentLoan[key];
								break;
							case "Borrower ID":
								borrowerID = currentLoan[key];
								break;
							case "Drug ID":
								drugID = currentLoan[key];
								break;
							default:
								tableData += "<td>" + currentLoan[key] + "</td>";
								break;
						} //End switch
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
					tableData += "<td><input id='checkBoxLoanID" + loanID + "' type='checkbox' value='" + loanID + "' data-loanID='" + loanID + "' data-borrowerID='" + borrowerID + "' data-drugID='" + drugID + "' data-toggle='modal' data-target='#loanModal'></td>";

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
	$("#loanVacName").val(loanDataArray[0]);
	$("#loanLotNum").val(loanDataArray[4]);
	$("#loanExpireDate").val(loanDataArray[5]);

	$("#loanQty").val(loanDataArray[6]);
	$("#loanDate").val(loanDataArray[3]);
	$("#loanBorrowerName").val(loanDataArray[1]);
	$("#loanSigner").val(loanDataArray[2]);

	//Clear the values in the Reimbursement Form
	$('#reimburseSigner').val('');

	//Cash Form
	$('#reimburseAmount').val('');

	//Doses Form
	$('#reimburseLotNum').val('');
	$('#reimburseExpireDate').val('');
	$('#reimburseDoseQty').val('');

	//Reset the selected radio button so that it defaults to "cash" option
	$('#rdoReimburseCash').prop('checked', true);

	//Hide the "doses" form on the modal & show the "cash" form on the modal
	$("#reimburseCashFields").css('display', 'block');
	$("#reimburseDosesFields").css('display', 'none');

	//Uncheck 'Is Partial Repayment' checkbox
	$('#partialPayment').prop('checked', false);

}); //End #loanModal.on('show.bs.modal')


//Unchecks the selected loan when the modal dialog box is closed
$('#loanModal').on('hide.bs.modal', function (event){
	$("input[type='checkbox']:checked").prop('checked', false);
}); //End #loanModal.on('hide.bs.modal')


//Allows user to filter loans
$("input[type='radio'][name='loanFilter']").click(function(){
	var selected = $(this).val();

	if(selected == 'all')
	{
		//Hide <select> element which provides contextual sort options
		$("#filterCategoryOptions").css('display', 'none');
		$("#filterOptionsLbl").css('display', 'none');
	
		//Display all outstanding loans'
		DisplayOutstandingLoans('all', 'all');

	} //End if
	else
	{
		//Display <select> element which provides contextual sort options
		$("#filterCategoryOptions").css('display', 'inline-block');
		$("#filterOptionsLbl").css('display', 'inline');

		//Sort loans by the selected criteria
		var sortCategory = $(this).val();
		DisplayOutstandingLoans(sortCategory, 'all');

		//Populate <select> element with sort options based on the radio option value
		$.ajax({
			url: "<?php echo site_url('Inventory/GetLoanFilterOptions'); ?>",
			method: "POST",
			data: {'FilterCategory': sortCategory},
			dataType: 'JSON',
			success: function(possibleOptions){

			}, //End success function
			error: function(errorResult){

			} //End error function
		}); //End $.ajax()

	} //End else

}); //End (input[type='radio'][name='loanFilter']).click()


//Controls events for the radio buttons on the modal dialog box
$("input[type='radio'][name='loanReimburseType']").click(function(){
	//console.log("Radio button clicked!");

	var selectedRdo = $(this).val();

	if(selectedRdo == 'cash') //If "cash" is selected, change "signer" textbox, hide the "dosesFields" div content, show the "cashFields" div content
	{
		$("#reimburseSigner").attr('placeholder', 'Person Returning Money');	
		$("#reimburseDosesFields").css('display', 'none');
		$("#reimburseCashFields").css('display', 'block');
	} //End if
	else //If "cash" is not selected, change placeholder text in "signer" textbox, hide the "cashFields" div content, and show the "dosesFields" div content
	{
		$("#reimburseSigner").attr('placeholder', 'Person Returning Vials');
		$('#reimburseCashFields').css('display', 'none');
		$("#reimburseDosesFields").css('display', 'block');
	} //End else

}); //End (input[type='radio'][name='loanReimburseType']).click()

//Controls the "Submit" button on the modal dialog box
$("#btnReimburse").click(function(){
	//console.log("Submit btn clicked");

	//Get information needed for loans (data not coming from the modal form)
	var loanID = $("input[type='checkbox']:checked").data('loanid');
//	var borrowerID = $("input[type='checkbox']:checked").data('borrowerid');
	var drugID = $("input[type='checkbox']:checked").data('drugid');

	var reimburseType = $("input[type='radio'][name='loanReimburseType']:checked").val();
	var isPartialReimbursement = $("#partialPayment").is(":checked");

	var reimburseSigner = $("#reimburseSigner").val();

	//Declare/initialize variables for modal form
	//Cash modal form
	var reimburseAmount = null;

	//Doses modal form
	var lotNum = null;
	var expireDate = null;
	var doseQty = null;



	//Get modal form data
	switch(reimburseType)
	{
		case "cash":
			reimburseAmount = $("#reimburseAmount").val();
			break;
		case "doses":
			lotNum = $("#reimburseLotNum").val();
			expireDate = $("#reimburseExpireDate").val();
			doseQty = $("#reimburseDoseQty").val();
			break;
		default:
			console.log("An unavailable option was selected");
			break;
	} //End switch


	$.ajax({
		url: "<?php echo site_url('Inventory/LoanReimbursement'); ?>",
		method: "POST",
		data: {'ReimburseType': reimburseType, 
			   'IsPartialReimbursement': isPartialReimbursement, 
			   'LoanID': loanID, 
/*			   'BorrowerID': borrowerID, */
			   'DrugID': drugID,
			   'ReimburseAmount': reimburseAmount,
			   'ReimburseSigner': reimburseSigner,
			   'LotNum': lotNum,
			   'ExpireDate': expireDate,
			   'DoseQty': doseQty
			},
		dataType: "JSON",
		success: function(result){
			console.log(result + ". Reimburse success");

			//Provide user feedback
			$("#userMsg").html("Loan Reimbursement Successful");
			DisplaySuccessMsg();

			//Refresh list of loans in the table
			var sortCategory = $("input[type='radio'][name='filterLoanOptions']:checked").val();
			var filterCategory = $("#filterCategoryOptions").val(); //#filterCategoryOptions is a <select> box
			DisplayOutstandingLoans(sortCategory, filterCategory);

		}, //End success function
		error: function(errorResult){
			console.log('Reimbursement error occurred');

			//User feedback
			$("#userMsg").html("Loan Reimbursement Failed");
			DisplayErrorMsg();


		} //End error function
	}); //End $.ajax()

}); //End #btnReimburse.click()

// //Controls the "Cancel" button on the modal dialog box
// $("#btnClose").click(function(){

// }); //End #btnClose.click()


//Controls display of #userMsg element if AJAX requests are successful or user input is valid
function DisplayErrorMsg()
{
	//Display userMsg element
	$("#userMsg").css('display', 'block');
	$("#userMsg").css('margin-left', '341.5px');
	$("#userMsg").css('margin-right', '341.5px');

	//Change background color & text color
	$("#userMsg").css('background-color', '#f76e6e');
	$("#userMsg").css('color', 'black');
} //End DisplaySuccessMsg()

//Controls display of #userMsg element in AJAX requests are unsuccessful or user input is invalid
function DisplaySuccessMsg()
{
	//Display userMsg element
	$("#userMsg").css('display', 'block');
	$("#userMsg").css('margin-left', '341.5px');
	$("#userMsg").css('margin-right', '341.5px');

	//Change background color & text color
	$("#userMsg").css('background-color', '#99ccff');
	$("#userMsg").css('color', 'black');
} //End DisplayErrorMsg()

</script>