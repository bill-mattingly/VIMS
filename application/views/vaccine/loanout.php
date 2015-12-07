<!-- Body opened in header file-->

<div class="row">

	<h1>Vaccine Loan Out Form</h1>
	<p>Add Information For Vaccine Loan Outs Below:</p>

	<?php
		echo validation_errors();

		$attributes = array('id' => 'loanout-form');
		echo form_open("Inventory/LoanOut", $attributes);

		echo "<div class='form-group'>";
			echo "<label for='ndc10' class='actionform'>NDC 10:</label>";
			echo "<input id='ndc10' type='text' value='".$ndc10."' readonly>";
		echo "</div>";

		// echo "<div class='form-group'>";
		// 	echo "<label for='ndc11' class='actionform'>HIPAA NDC 11:</label>";
		// 	echo "<input id='ndc11' type='text' name='ndc11' value='".$ndc11."' readonly>";
		// echo "</div>";
	
		echo "<div class='form-group'>";
			echo "<label for='lotNumList' class='actionform'>Lot Number:</label>";
			// echo "<input id='lotNum' type='text' name='lotNum' value='".$lotNum."' placeholder>";

			echo "<select id='lotNumList' name='lotNumList'>"; //need a 'name' attribute in order for validation callback function to work correctly
				echo "<option value='-1' data-expireDate='' selected>Select Lot Number</option>";

				foreach($vaccineArray as $lotArray)
				{
					//echo $lotArray->{'Proprietary Name'};
					echo "<option value='".$lotArray->{'Lot Number'}."' data-expireDate='".$lotArray->{'Expire Date'}."' data-maxDoses='".$lotArray->{'Net Doses'}."'>".$lotArray->{'Lot Number'}."</option>";
				};

			echo "</select>";
		echo "</div>";
	?>

		<div class='form-group'>
			<label for='expireDate' class='actionform'>Expiration Date:</label>
			<input id='expireDate' type='text' name='expireDate' placeholder='Expiration Date' readonly>
		</div>


		<div class='form-group'>
			<label for='clinicCost' class='actionform'>Reimbursement Price (Per Dose):</label>
			<input id='clinicCost' type='text' placeholder='Reimbursement Price' value="<?php echo $clinicCost; ?>">
		</div>


		<div class="form-group">
			<label id='borrowerUserMsg'></label>
			<label for="borrowerID" class='actionform'>Borrowing Department:</label>
			<select id="borrowerID" name="borrowerID">
				<option value='-1' selected>Select a Department</option>
				
				<?php
					foreach($listOfBorrowers as $aBorrower)
					{
						echo "<option value ='".$aBorrower->BorrowerID."'>";
						echo $aBorrower->EntityName;
						echo "</option>";
					}
				?>

			</select> <button id='btnAccessBorrowerControls' type='button'>Add Borrower</button><br/>
			
			<div class='editBorrowerControls'>
				<br/>
				<label for='rdoAddBorrower' checked>Add</label><input id='rdoAddBorrower' type='radio' value='add' name='rdoBorrower'>
				<label for='rdoEditBorrower'>Edit</label><input id='rdoEditBorrower' type='radio' value='edit' name='rdoBorrower' disabled>
				<label for='rdoDeleteBorrower'>Delete</label><input id='rdoDeleteBorrower' type='radio' value='delete' name='rdoBorrower' disabled><br/>

				<label for='borrowerName'>Borrowing Unit Name:</label><input id='borrowerName' type='text' placeholder='Unit&apos;s Name'><br/>

				<label for='contactName'>Contact Person&apos;s Name:</label><input id='contactName' type='text' placeholder='(First &amp; Last) (optional)'><br/>
				<label for='contactPhone'>Contact Phone:</label><input id='contactPhone' type='text' placeholder='10 Digit #(optional)'><br/>
				<label for='contactEmail'>Contact Email:</label><input id='contactEmail' type='text' placeholder='Email (optional)'><br/>

				<button id='btnEditBorrower' type='button'>Add</button>
				<button id='btnCancelEdit' type='button'>Cancel Add</button><br/>
			</div> <!-- /.editBorrowerControls -->

			<div class='form-group'>
				<br/>
				<label>Loan Signer:</label><br/>
					<label for='loanSigner'>Received By:</label><input id='loanSigner' type='text' name='loanSigner' placeholder='(First &amp; Last Name)'><br/>
			</div> <!-- /.form-group -->

		</div> <!-- /End .form-group -->

<!-- 		<div class="form-group">
			<label for="packageQty" class='actionform'>Number of Packages:</label>
			<input id="packageQty" type="number" name="packageQty" min='1'>
		</div> -->

		<div class="form-group">
			<label id="dosesLoanableErrorMsg"></label> <!--Label that tells the user if they have entered an incorrect number of doses (i.e. below the min or above the max)-->
			<label for="totalDosesLoaned" class='actionform'>Total Doses Loaned:</label> <!--Doses Per Package:-->
			<input id='totalDosesLoaned' type='number' placeholder='Total Doses Loaned' name='dosesPerPackage' min='1' max='' value=''>
			<label id="maxDosesLoanable"></label> <!--Label that displays the maximum number of doses which can be loaned out for a given lot number to the user-->
		</div>

		<div class="form-group">
			<input type="submit" name="Add">
		</div>
		<div id="AJAXPreloader"></div>
	</form>

	<!--Takes users back to the home page-->
	<button type='button'><?php echo anchor('Inventory/Index', 'Cancel', array('id' => 'btnCancel')); ?></button>

</div> <!-- /End .row -->

<script>

	$(document).ready(function(){
		//Disabled clinic cost, borrower select box, total doses loaned, & submit button by default
		$("input[type='submit']").attr("disabled", "disabled");
		$("#clinicCost").attr("disabled", "disabled");
		$("#borrowerID").attr("disabled", "disabled");
		$("#totalDosesLoaned").attr("disabled", "disabled");
		$("#btnAccessBorrowerControls").attr("disabled", "disabled");

		//Governs what occurs when a borrowing department is selected from the borrowing department list box
		$("#borrowerID").change(function(){
			//Clear value in the #borrowerUserMsg label & hide the label
			$("#borrowerUserMsg").css('display', 'none');
			$("#borrowerUserMsg").html('');

			if($(this).val() == -1) //If selected item is the default option, make button caption "Add Borrower"
			{
				$("#btnAccessBorrowerControls").html('Add Borrower');

				//If the borrower controls are displayed (and no borrower is selected), then do the following 
				if($(".editBorrowerControls").css('display') == 'block')
				{
					//Disable "Edit" & "Delete" radio buttons
					$("#rdoEditBorrower").prop('disabled', 'disabled');
					$("#rdoDeleteBorrower").prop('disabled', 'disabled');

					//Check the "Add" radio button
					$("#rdoAddBorrower").prop('checked', true);

					//Clear the borrower control textboxes
					$("#borrowerName").val('');
					$("#contactName").val('');
					$("#contactPhone").val('');
					$("#contactEmail").val('');

					//Change text on btnEditBorrower button to "Add"
					$("#btnEditBorrower").html("Add");

					//Change text on btnCancel edit to "Cancel Add"
					$("#btnCancelEdit").html("Cancel Add");
				}

			}
			else //If selected item isn't the default, make button caption "Edit Borrower"
			{
				$("#btnAccessBorrowerControls").html('Edit Borrower');

				//If a borrower has been selected & the borrower controls are displayed then this means the "Edit Borrower" button has been clicked.
				//Thus, if a borrower has been selected & the edit radio button is disabled, then enable both the edit and delete radio button options
				if(($(".editBorrowerControls").css('display') == 'block')) //&& ($("#rdoEditBorrower").prop('disabled') == true)) 
				{
					if(($("#rdoEditBorrower").prop('disabled') == true))
					{
						//Enable "Edit" & "Delete" radio buttons
						$("#rdoEditBorrower").prop('disabled', false);
						$("#rdoDeleteBorrower").prop('disabled', false);
					}

					//Select "Edit" radio button
					$("#rdoEditBorrower").prop('checked', true);

					//Change text in btnEditBorrower
					$("#btnEditBorrower").html('Edit');

					//Change text in btnCancel edit
					$("#btnCancelEdit").html("Cancel Edit");

					//Fetch borrower's info, display AJAX preloader image, & populate borrower controls with information
					//Display AJAX preloader image
					$("#AJAXPreloader").css('display', 'block');
					var borrowerID = $(this).val();
					
					//AJAX request
					$.ajax({
						url: "<?php echo site_url('Inventory/GetBorrower'); ?>",
						method: "POST",
						data: {"BorrowerID": borrowerID},
						dataType: "JSON",
						success: function(theBorrower){

							console.log(theBorrower);

							//Display borrower information in borrower controls
							$("#borrowerName").val(theBorrower[0].Name);
							$("#contactName").val(theBorrower[0].Contact);
							$("#contactPhone").val(theBorrower[0].Phone); //Process phone data for display
							$("#contactEmail").val(theBorrower[0].Email);

							//Hide preloader image
							$("#AJAXPreloader").css('display', 'none');

						}, //End success
						error: function(errorResult){
							console.log("An error occurred");

							//Hide preloader image
							$("#AJAXPreloader").css('display', 'none');

						} //End error
					}); //End .ajax()
				} //End if

			} //End else

		}); //End #borrowerID.change()

		//Controls what happens when the #btnAccessBorrowerControls button is clicked (this is the button which)
		$("#btnAccessBorrowerControls").click(function(){
			//Clear value in the #borrowerUserMsg label & hide the label
			$("#borrowerUserMsg").css('display', 'none');
			$("#borrowerUserMsg").html('');

			//Display textbox & button to allower user to add a borrower
			$(".editBorrowerControls").css('display', 'block');


			//Get borrower id from the #borrowerID select element
			var borrowerID = $("#borrowerID").val();

			//Check which option is currently selected in #borrowerID list box (the list box displaying borrowing departments)
			//If default option (-1) is selected, disable "Edit" & "Delete" radio options. If any option other than default is selected, enable the "Edit" & "Delete" radio options.
				//var borrowerID = $("#borrowerID").val();
			if(borrowerID == -1)
			{
				//Disable the "Edit" & "Delete" radio buttons (b/c no borrower has been selected)
				$("#rdoEditBorrower").prop('disabled', 'disabled');
				$("#rdoDeleteBorrower").prop('disabled', 'disabled');

				//Check the "Add" radio button
				$("#rdoAddBorrower").prop('checked', true);

				
			} //End if
			else
			{
				//Display AJAX Preloader image
				$("#AJAXPreloader").css('display', 'block');

				//Enable "Edit" & "Delete" radio buttons (b/c user has selected a borrower)
				$("#rdoEditBorrower").prop('disabled', false);
				$("#rdoDeleteBorrower").prop('disabled', false);

				//Check the "Edit" radio button
				$("#rdoEditBorrower").prop('checked', true);

				//Change btnEditBorrower text to "Edit"
				$("#btnEditBorrower").html('Edit');

				//Change btnCancelEdit text
				$("#btnCancelEdit").html("Cancel Edit");

				//Populate the borrower controls with the borrower's information
				$.ajax({
					url: "<?php echo site_url('Inventory/GetBorrower'); ?>",
					method: "POST",
					data: {"BorrowerID": borrowerID},
					dataType: "JSON",
					success: function(theBorrower){

						console.log(theBorrower);

						//Display borrower's information in borrower controls
						$("#borrowerName").val(theBorrower[0].Name);
						$("#contactName").val(theBorrower[0].Contact);
						$("#contactPhone").val(theBorrower[0].Phone);
						$("#contactEmail").val(theBorrower[0].Email);

						//Hide AJAX Preloader image
						$('#AJAXPreloader').css('display', 'none');

					}, //End success
					error: function(errorResult){
						console.log("An error occurred");

						//Hide AJAX Preloader image
						$("#AJAXPreloader").css('display', 'none');

					} //End error

				}); //End .ajax()


			} //End else


		}); //End #btnAccessBorrowerControls.click()

		$("input[type='radio']").click(function(){
			//Change value on the btnEditBorrower button
			switch($(this).val())
			{
				case 'add':
					//Change text of btnEditBorrower to 'Add'
					$("#btnEditBorrower").html("Add");
					//document.getElementById('btnEditBorrower').innerHTML = 'Add';

					//Change btnCancelEdit text
					$("#btnCancelEdit").html("Cancel Add");

					//Change select element's selected option to the default ("Select a Department")
					$("#borrowerID").val(-1);

					//Change text of btnAccessBorrowerControls to "Add Borrower"
					$("#btnAccessBorrowerControls").html("Add Borrower");

					//Disable the "Edit" & "Delete" radio options
					$("#rdoEditBorrower").prop("disabled", "disabled");
					$("#rdoDeleteBorrower").prop("disabled", "disabled");

					//Clear information in borrower textboxes
					$("#borrowerName").val("");
					$("#contactName").val("");
					$("#contactPhone").val("");
					$("#contactEmail").val("");


					break;
				case 'edit':
					//Change text of btnEditBorrower to 'Edit'
					$("#btnEditBorrower").html("Edit");
				//	document.getElementById('btnEditBorrower').innerHTML = 'Edit';
					
					//Change btnCancelEdit text
					$("#btnCancelEdit").html("Cancel Edit");

					break;
				case 'delete':
					//Change text of btnEditBorrower to 'Delete'
					$("#btnEditBorrower").html("Delete");
					//document.getElementById('btnEditBorrower').innerHTML = 'Delete';

					//Change btnCancelEdit text
					$("#btnCancelEdit").html("Cancel Delete");
					break;
				default:
					console.log('An undefined option was selected');
					break;
			} //End switch
		}); //End input[type='radio'].click()

		$("#btnEditBorrower").click(function(){
			//Make change to borrower based on the value of the radio buttons

			//Get selected radio button
			var selectedRdo = $("input[type='radio']:checked").val();
			console.log(selectedRdo);

			//Object containing all the form data needed by the AJAX call
			var dataObject = {
				'action': selectedRdo,
				'id': $("#borrowerID").val(),
				'name': $("#borrowerName").val(),
				'contact': $("#contactName").val(),
				'phone': $("#contactPhone").val(),
				'email': $("#contactEmail").val()
			};


			// dataArray['action'] = selectedRdo;
			// dataArray['id'] = $("#borrowerID").val();
			// dataArray['name'] = $("#borrowerName").val();
			// dataArray['contact'] = $("#contactName").val();
			// dataArray['phone'] = $("#contactPhone").val();
			// dataArray['email']	= $("#contactEmail").val();


			//If 'add', then add the borrower


			//If 'edit', then change the name of the borrower
			//If 'delete', then delete the borrower




			//AJAX request
			$.ajax({
				url: "<?php echo site_url('Inventory/EditBorrowers'); ?>",
				method: "POST",
				data: {'DataObject': dataObject},
				dataType: "JSON",
				success: function(theNewBorrowers){
					
					//Clear all values from borrower select element
					$("#borrowerID").empty(); 
					
					//Add default value to select list
					$("#borrowerID").append("<option value='" + -1 + "'>Select a Department</option>");

					//Repopulate the #borrowerID select element
					var newBorrowersList = theNewBorrowers[1];
					$.each(newBorrowersList, function(key, value){
						$("#borrowerID").append("<option value='" + value.Id + "'>" + value.Name + "</option>");
					});

					//Display message to user
					var borrowerMsg = "";
					switch($("input[type='radio']:checked").val())
					{
						case 'add':
							borrowerMsg = "Borrower Added";

							//If added or edited, select that user in the select box
							//Change btnAccessBorrowerControls button to "Edit Borrower"
							var selectedBorrower = theNewBorrowers[0];
							$("#borrowerID").val(selectedBorrower);
							$("#btnAccessBorrowerControls").html("Edit Borrower");

							break;
						case 'edit':
							borrowerMsg = "Edit Successful";

							//If added or edited, select that user in the select box
							//Change btnAccessBorrowerControls button to "Edit Borrower"
							var selectedBorrower = theNewBorrowers[0];
							$("#borrowerID").val(selectedBorrower);
							$("#btnAccessBorrowerControls").html("Edit Borrower");

							break;
						case 'delete':
							borrowerMsg = "Borrower Deleted";

							//If deleted, select the default option in the select box
							//Change btnAccessBorrowerControls button to "Add Borrower"
							$("#borrowerID").val(-1); //Select the default select box option
							$("#btnAccessBorrowerControls").html("Add Borrower");

							break;
						default:
							break;
					}
					$("#borrowerUserMsg").html(borrowerMsg);
					$("#borrowerUserMsg").css('display', 'block');
					$("#borrowerUserMsg").css('background-color', '#99ccff'); //#99FFCC


					//Clear the editBorrowerControls
					$("#borrowerName").val('');
					$("#contactName").val('');
					$("#contactPhone").val('');
					$("#contactEmail").val('');

					//Hide the editBorrowerControls
					$(".editBorrowerControls").css('display', 'none');


					console.log('success');
				}, //End AJAX success function
				error: function(errorResult){
					console.log('Borrower Edit Failed!');
				} //End AJAX error function
			}); //End .ajax()



		}); //End #btnEditBorrower.click()

		//Controls what happens when user clicks the #btnCancelEdit button
		//(this button is only available to the user if he/she has clicked the #btnAccessBorrowerControls button)
		$("#btnCancelEdit").click(function(){
			//Hide all controls in the #editBorrowerControls div
			$(".editBorrowerControls").css('display', 'none');

		}); //End #btnCancelEdit.click()


		// $("")
		// {
		// 	//AJAX function to create new borrower

		// 	//Hide #addBorrowerControls div
		// }

		$("#lotNumList").change(function()
		{
			
			if($(this).val() != -1)
			{
				//Activate controls based on a valid lot number being selected
				$("#clinicCost").attr("disabled", false);
				$("#borrowerID").attr("disabled", false);
				$("#totalDosesLoaned").attr("disabled", false);
				
				$("#btnAccessBorrowerControls").attr('disabled', false);
				//$("input[type='submit']").attr("disabled", false);

				//Set the max value & the label value for the totalDosesLoaned & maxDosesLoanable controls
				//var maxQty = $("#lotNumList").find(":selected").data("maxDoses");//.data("maxDoses");
				//var selectedOption = $("#lotNumList").find(":selected"); //$("#lotNumList :selected"); //.data("maxDoses");
				var lotNumList = document.getElementById("lotNumList"); //.item() selectedIndex();
				var selectedOption = lotNumList.item(lotNumList.selectedIndex);
				var maxQty = selectedOption.getAttribute("data-maxDoses");
				// var minQty = $("#totalDosesLoaned").attr("min");


				$("#totalDosesLoaned").attr("max", maxQty);
				$("#maxDosesLoanable").html("Max Quantity: " + maxQty);

				//Get value of #totalDosesLoaned
				var theDoses = $("#totalDosesLoaned").val();

				if(theDoses != '')
				{
					if(theDoses > maxQty)
					{
						$("#totalDosesLoaned").val(maxQty);
					}
					//Don't need to take care of values less than minQty b/c validation function handles this
					// else if(theDoses < minQty)
					// {
					// 	$("#totalDosesLoaned").val(minQty);
					// }
				}

			}
			else //(If the user selects "Select Lot Number" option)
			{
				//Deactivate controls
				$("#clinicCost").attr("disabled", "disabled");
				$("#borrowerID").attr("disabled", "disabled");
				$("#totalDosesLoaned").attr("disabled", "disabled");
				$("input[type='submit']").attr('disabled', true);

				$("#btnAccessBorrowerControls").attr('disabled', true);
				$("#btnAccessBorrowerControls").html('Edit Borrowers');

				//Clear max value for totalDosesLoaned text box & for maxDosesLoaned label element
				$("#totalDosesLoaned").val('');
				$("#totalDosesLoaned").attr("max", "");
				$("#maxDosesLoanable").html('');
				//document.getElementById("maxDosesLoaned").innerHTML = '';
			}

			
						
		}); //End #lotNumList.change() function


		


		//Validation for totalDosesLoaned field
		$("#totalDosesLoaned").keyup(function(){
			//By default, hide error message label & clear its text whenever value in textbox is changed
			$("#dosesLoanableErrorMsg").css("display", "hidden");
			$("#dosesLoanableErrorMsg").html("");

			//Get textbox values
			//var doses = undefined;
			var min = Number($(this).attr('min'));
			var max = Number($(this).attr('max'));

			try{ //Try to conver
				var doses = $(this).val();

				if(isNaN(doses) || doses == "")
				{
					throw new TextError(doses);
				}
				else if(Number(doses) < min)
				{
					throw new MinError(doses);
				}
				else if(Number(doses) > max)
				{
					throw new MaxError(doses);
				}

				//Activate submit button (Keep submit button deactivated until valid number of doses is entered)
				$("input[type='submit']").attr("disabled", false);

			} //End try
			catch(errorObject)
			{
				//Print error to console
				console.log("Error Name: " + errorObject.name + "\n" + "Error Message: " + errorObject.message);

				switch(errorObject.name)
				{
					case "TextError":
						console.log(errorObject.name);

						$(this).val("");
						$("input[type='submit']").attr('disabled', true);

						//Error Message
						$("#dosesLoanableErrorMsg").css("display", "block");
						$("#dosesLoanableErrorMsg").html("Enter a Number");

						break;
					case "MaxError":
						console.log(errorObject.name);

						$(this).val(max);
						$("input[type='submit']").attr('disabled', false);

						$("#dosesLoanableErrorMsg").css("display", "block");
						$("#dosesLoanableErrorMsg").html("Can't Loan Out More than Inventory Quantity");

						break;
					case "MinError":
						console.log(errorObject.name);

						$(this).val(min);
						$("input[type='submit']").attr('disabled', false);

						$("#dosesLoanableErrorMsg").css("display", "block");
						$("#dosesLoanableErrorMsg").html("Can't Loan Out Less than 1 Dose");

						break;
					default:
						console.log("A " + errorObject.name + " error occurred");

						$("input[type='submit']").attr('disabled', true);

						break;
				} //End switch statement

			} //End catch



		}); //End .change() event


		function MaxError(doseQty) //Creates error object "MaxError"
		{
			this.name = "MaxError";
			this.message = "Tried to Enter More Doses than Available.";
		} 

		function MinError(doseQty) //Creates error object "MinError"
		{
			this.name = "MinError";
			this.message = "Tried to Enter Less than 1 Dose";
		}

		function TextError(doseQty)
		{
			this.name = "TextError";
			this.message = "Tried to Enter Text Rather than a Number";
		}

	}); //End .ready() function




	// $(document).ready(function(){

	// 	//Set the Expiration Date field (in the Administer & LoanOut pages) based on the selected Lot Number
	// 	$("#lotNumList").change(function(){

	// 		var selectList = document.getElementById('lotNumList');
	// 		var option = selectList.options[selectList.selectedIndex];
	// 		var date = option.getAttribute('data-expireDate');

	// 		$("#expireDate").val(date);

	// 	});

	// });

</script>


<!--Body closed in footer file