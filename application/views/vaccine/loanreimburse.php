<div class='row'>
	<div class='col-md-12'>

	<h1>Outstanding Loans</h1>

	<!-- Used to provide feedback to user -->
	<p id='userMsgMain'></p>

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
			<option value='all' selected>Select Option</option>
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

						<label>Cost/Dose:</label>
						<input id='perDoseLoanCost' type='text' disabled><br/>

						<label>Loan Date:</label>
						<input id='loanDate' type='text' disabled><br/>

						<label>Borrower:</label>
						<input id='loanBorrowerName' type='text' disabled><br/>

						<label>Signer:</label>
						<input id='loanSigner' type='text' disabled><br/>
					</div> <!-- /End col-md-6 -->

					<div class='col-md-6'>
						<h2>Reimbursement Form</h2>

						<!--For displaying error messages to user-->
						<p id='userMsgModal'></p>

						<label for='reimburseSigner'>Payer Name:</label>
						<input id='reimburseSigner' type='text' name='reimburseSigner' placeholder='Person Returning Money'><br/>

<!--
						<label id="lblReimburseQty" for="reimbursement">Reimbursement Amount:</label>
						<input id="reimburseQty" type="text" name='reimbursement' placeholder="Enter Monetary Value"><br/>
-->

						<div id='reimburseCashFields'>
							<p id='infoRemainingValue'></p>
							<label id="lblReimburseAmount" for="reimburseAmount">Value:</label>
							<input id="reimburseAmount" type="number" min='0' name='reimburseAmount' placeholder="Enter Monetary Value"><br/>
						</div> <!-- /End #reimburseCash -->

						<div id='reimburseDosesFields'>
							<label id="lblLotNum" for="reimburseLotNum">Lot Number:</label>
							<input id="reimburseLotNum" type="text" name='reimburseLotNum' placeholder="Enter Monetary Value"><br/>

							<!--See jQuery datepicker widget: https://jqueryui.com/datepicker/ -->
							<label id="lblExpireDate" for="reimburseExpireDate">Expire Date:</label>
							<input id="reimburseExpireDate" type="text" name='reimburseExpireDate' placeholder="Expiration Date"><br/>

							<p id='infoRemainingDoses'></p>
							<label id="lblReimburseDoseQty" for="reimburseDoseQty">Dose Qty:</label>
							<input id="reimburseDoseQty" type="number" min='0' name='reimburseDoseQty' placeholder="Dose Quantity"><br/>


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

			//Clear any current records in the table
			$("#outstandingLoansTbl").empty();


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
					var remainingDoses = null; //assigned within for loop
					var remainingValue = null; //assigned within for loop
					var perDoseLoanCost = null; //assigned in for loop

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
							case "Per Dose Loan Cost":
								perDoseLoanCost = currentLoan[key];
								break;
							case "Remaining Doses":
								remainingDoses = currentLoan[key];
								tableData += "<td>" + currentLoan[key] + "</td>";
								break;
							case "Outstanding Loan Value":
								remainingValue = currentLoan[key];
								tableData += "<td>" + currentLoan[key] + "</td>";
								break;
							default:
								tableData += "<td>" + currentLoan[key] + "</td>";
								break;
						} //End switch
					} //End for loop


					//Add checkbox to final column of row
					tableData += "<td><input id='checkBoxLoanID" + loanID + "' type='checkbox' value='" + loanID + "' data-loanID='" + loanID + "' data-borrowerID='" + borrowerID + "' data-drugID='" + drugID + "' data-remainingdoses='"+ remainingDoses +"' data-remainingvalue='"+ remainingValue +"' data-perdoseloancost='"+ perDoseLoanCost +"' data-toggle='modal' data-target='#loanModal'></td>";

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

} //End DisplayOutstandingLoans()


//Populates modal dialog with loan information from sessionStorage variable
$('#loanModal').on('show.bs.modal', function(event){
	
	var checkBox = event.relatedTarget;

	//Check to see if loanid exists already in loan reimbursement table (i.e. if a row in the table has the same "loan id" in it)
	//If so, check (& then disabled) the #partialPayment checkbox (so user can't uncheck it)
	//If not, then uncheck the checkbox
	var loanID = $(checkbox).data('loanid');


	$.ajax({
		url: "<?php echo site_url('Inventory/CheckPartialLoanReturn'); ?>",
		method: 'POST',
		data: {'LoanID': loanid},
		dataType: 'JSON',
		success: function(loanReturnResult){

		},
		error: function(errorResult){
			console.log("An error checking on loan return transactions occurred");
		}
	}); //End $.ajax()



	var count = 0;

	var selectedRow = $(checkBox).closest('tr');

	var loanDataArray = [];

	$(selectedRow).find('td').each(function(){
		var cell = $(this).html();
//		console.log(cell);
		loanDataArray.push(cell);
	}); //End .each()

	//Enable controls that might be disabled
	$("#rdoReimburseVials").prop('disabled', false);
	$("#btnReimburse").prop('disabled', false);

	//Populate loan information modal text boxes
	$("#loanVacName").val(loanDataArray[0]);
	$("#loanLotNum").val(loanDataArray[4]);
	$("#loanExpireDate").val(loanDataArray[5]);

	$("#loanQty").val(loanDataArray[6]);

	var loanCostPerDose = parseFloat($("input[type='checkbox']:checked").data('perdoseloancost'));
	$("#perDoseLoanCost").val(loanCostPerDose);

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

	//Display to user the remaining loan value (since the "Cash Payment" option is the default)
	var remainingValue = parseFloat($(checkBox).data('remainingvalue'));
	$("#infoRemainingValue").html("Remaining Loan Value: " + remainingValue);

	//Set max attribute of #reimburseAmount textbox to the maximum loan value
	$("#reimburseAmount").attr('max', remainingValue);

	//Disable #rdoReimburseVials if loanCostPerDose > remainingValue (means a half dose was reimbursed (through a prior cash reimbursement) & thus the only valid option is to reimburse the remaining loan value with cash (rather than doses))
	if(loanCostPerDose > remainingValue)
	{
		$("#rdoReimburseVials").prop('disabled', true);	

		//Give user feedback
		DisplayErrorMsg("#userMsgModal");
		$("#userMsgModal").html("Cost Per Dose Greater Than Residual Loan Value. Must Reimburse Through Cash Transaction Only");

	} //End if


	//Hide the "doses" form on the modal & show the "cash" form on the modal
	$("#reimburseCashFields").css('display', 'block');
	$("#reimburseDosesFields").css('display', 'none');

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
			success: function(filterOptions){
				//Populate #filterCategoryOptions <select> element with results
				console.log(filterOptions);

				//Empty #filterCategoryOptions <select> element
				$("#filterCategoryOptions").empty();

				//Repopulate #filterCategoryOptions
				$("#filterCategoryOptions").append("<option value='all' selected>Select Option</option>");

				$.each(filterOptions, function(key, value){
					$("#filterCategoryOptions").append('<option value=\''+ value +'\'>' + value + '</option>');
				});


			}, //End success function
			error: function(errorResult){
				console.log("An error occurred");

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

		//Add user msg on modal form to tell user remaining value
		var remainingValue = $("input[type='checkbox']:checked").data('remainingvalue');
		$("#infoRemainingValue").html("Remaining Loan Value: " + remainingValue);

		//Set max value of #reimburseAmount textbox = remainingValue
		$("#reimburseAmount").attr('max', remainingValue);

	} //End if
	else //If "cash" is not selected, change placeholder text in "signer" textbox, hide the "cashFields" div content, and show the "dosesFields" div content
	{
		$("#reimburseSigner").attr('placeholder', 'Person Returning Vials');
		$('#reimburseCashFields').css('display', 'none');
		$("#reimburseDosesFields").css('display', 'block');

		//Add user msg on modal form to tell user remaining doses
		var remainingDoses = $("input[type='checkbox']:checked").data('remainingdoses');
		$("#infoRemainingDoses").html("Remaining Doses to be Returned: " + remainingDoses);

		//Set max value of #reimburseDoseQty textbox to remainingDoses
		$("#reimburseDoseQty").attr('max', remainingDoses);

	} //End else

}); //End (input[type='radio'][name='loanReimburseType']).click()


$("#filterCategoryOptions").change(function(){

	var sortCriteria = $("input[type='radio'][name='loanFilter']:checked").val();
	var filterCriteria = $(this).val();

	//Redraw loan table so it only contains the value from the <select> element
	DisplayOutstandingLoans(sortCriteria, filterCriteria);


}); //End #filterCategoryOptions.change()


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
			$("#userMsgMain").html("Loan Reimbursement Successful");
			DisplaySuccessMsg("#userMsgMain");

			//Refresh list of loans in the table
			var sortCategory = $("input[type='radio'][name='filterLoanOptions']:checked").val();
			var filterCategory = $("#filterCategoryOptions").val(); //#filterCategoryOptions is a <select> box
			DisplayOutstandingLoans(sortCategory, filterCategory);

		}, //End success function
		error: function(errorResult){
			console.log('Reimbursement error occurred');

			//User feedback
			$("#userMsgMain").html("Loan Reimbursement Failed");
			DisplayErrorMsg("#userMsgMain");


		} //End error function
	}); //End $.ajax()

}); //End #btnReimburse.click()

// //Controls the "Cancel" button on the modal dialog box
// $("#btnClose").click(function(){

// }); //End #btnClose.click()


//Controls validation of user input into #reimburseDoseQty field (checks entered doses against remaining doses)
$("#reimburseDoseQty").focusout(function(){
	//Check entered value against remaining doses
	var enteredDoses = parseFloat(($("#reimburseDoseQty").val()), 10);
	var remainingDoses = parseFloat($("input[type='checkbox']:checked").data('remainingdoses'));

	//Enable submit button (by default)
	$("#btnReimburse").prop('disabled', false);

	//Clear user message element
	$("#userMsgModal").attr('background-color', 'initial');
	$("#userMsgModal").html('');

	if(enteredDoses > remainingDoses || enteredDoses <= 0)
	{
		//Disable "submit" button
		$("#btnReimburse").attr('disabled', true);

		//Tell user to correct the entered doses
		DisplayErrorMsg("#userMsgModal");

		if(enteredDoses > remainingDoses)
		{
			$("#userMsgModal").html("Too Many Doses Entered");
		} //End if
		else
		{
			$("#userMsgModal").html("Invalid Number of Doses");
		} //End else

		//Clear the entered doses field & set focus to the field
		$("#reimburseDoseQty").val('');
		$("#reimburseDoseQty").focus();

	} //End if
	else if (enteredValue < remainingDoses) //Check the "Partial Repayment" Checkbox if Entered Value < Remaining Doses
	{
		//Check partial payment checkbox
		$("#partialPayment").prop('checked', true);

		//Check to make sure partial doses aren't being reimbursed
		//Checks to see if remainingDoses/enteredValue has a remainder value
		if(remainingDoses % enteredValue != 0)
		{
			//Set user message
			DisplayErrorMsg("#userMsgModal");
			$("#userMsgModal").html("Must Reimburse Doses in Whole Quantities or Reimburse with Cash");

			//Disable submit button
			$("#btnReimburse").prop('disabled', true);

			//Clear #reimburseDoseQty field
			$("#reimburseDoseQty").val('');

			//Set focus to #reimburseDoseQty field
			$("#reimburseDoseQty").focus();
		}


	} //End else if



}); //End #reimburseDoseQty.focusout()

//Controls validation of user input into #reimburseAmount field (checks entered doses against remaining value)
$("#reimburseAmount").focusout(function(){
	//Check entered value against remaining value
	var enteredValue = parseFloat($("#reimburseAmount").val());
	var remainingAmount = parseFloat($("input[type='checkbox']:checked").data('remainingvalue'));

	//Enable submit button (by default)
	$("#btnReimburse").prop('disabled', false);

	//Clear user message element
	$("#userMsgModal").attr('background-color', 'initial');
	$("#userMsgModal").html('');

	if(enteredValue > remainingAmount || enteredValue <= 0)
	{
		//Disable "submit" button
		$("#btnReimburse").attr('disabled', true);

		//Tell user to correct the entered doses
		DisplayErrorMsg("#userMsgModal");

		if(enteredValue > remainingAmount)
		{
			$("#userMsgModal").html("Returned Amount Higher Than Loan");
		} //End if
		else
		{
			$("#userMsgModal").html("Returned Amount is Invalid");
		} //End else

		//Clear the entered doses field & set focus to the field
		$("#reimburseAmount").val('');
		$("#reimburseAmount").focus();

	} //End if
	else if (enteredValue < remainingAmount) //Check the "Partial Repayment" Checkbox if Entered Value < Remaining Amount
	{
		$("#partialPayment").prop('checked', true);
	} //End else if


}); //End #reimburseAmount.focusout()


//Controls display of #userMsg element if AJAX requests are successful or user input is valid
function DisplayErrorMsg(jQueryID)
{
	switch(jQueryID)
	{
		case '#userMsgModal':
			//Display userMsg element
			$(jQueryID).css('display', 'block');
			break;
		case '#userMsgMain':
			//Display userMsg element
			$(jQueryID).css('display', 'block');
			$(jQueryID).css('margin-left', '341.5px');
			$(jQueryID).css('margin-right', '341.5px');
			break;
		default:
			break;
	}

	//Change background color & text color
	$(jQueryID).css('background-color', '#f76e6e');
	$(jQueryID).css('color', 'black');
} //End DisplaySuccessMsg()

//Controls display of #userMsg element in AJAX requests are unsuccessful or user input is invalid
function DisplaySuccessMsg(jQueryID)
{
	switch(jQueryID)
	{
		case '#userMsgModal':
			//Display userMsg element
			$(jQueryID).css('display', 'block');
			break;
		case '#userMsgMain':
			//Display userMsg element
			$(jQueryID).css('display', 'block');
			$(jQueryID).css('margin-left', '341.5px');
			$(jQueryID).css('margin-right', '341.5px');
			break;
		default:
			break;
	}

	//Change background color & text color
	$(jQueryID).css('background-color', '#99ccff');
	$(jQueryID).css('color', 'black');
} //End DisplayErrorMsg()

</script>