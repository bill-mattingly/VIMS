<!--Body opened in header-->

<div class="row">

<!--
Template:

List of Transactions

options to sort transactions:
all, invoice, administer, loan out, loan return

[transactions in table form]

[column in table with "edit" and "delete" links]
-->

<h1>List of Transactions</h1>

<div class='form-group'>
	<h2>Transaction Filter</h2>

	<label for="transTypeAll">All</label>
	<input id="transTypeAll" type="radio" name="transtype" value="all" checked>
	
	<label for="transTypeInvoice">Invoice</label>
	<input id="transTypeInvoice" type="radio" name="transtype" value="invoice">
	
	<label for="transTypeAdminister">Administered Vaccine</label>
	<input id="transTypeAdminister" type="radio" name="transtype" value="administer">
	
	<label for="transTypeLoanOut">Loans</label>
	<input id="transTypeLoanOut" type="radio" name="transtype" value="loanout">

	<label for="transTypeOutstandingLoan">Outstanding Loans</label>
	<input id="transTypeOutstandingLoan" type="radio" name="transtype" value="outstandingloan">

	<label for="transTypeLoanReturn">Loan Returns</label>
	<input id="transTypeLoanReturn" type="radio" name="transtype" value="loanreturn">
</div> <!-- /End .form-group -->

<div>

		<?php
			//var_dump($transResults);

			//Get keys for array to build table header & also to access the columns in each row
			//$result = json_decode($transResults);
			//var_dump($transResults[0]);
			
			// $h = "BREAK";
			// echo $h;
			//var_dump(json_decode($transResults));
					// var_dump($transResults);
					// $decodedResult = json_decode($transResults);
					// //var_dump($result);


					// $propertiesArray = get_object_vars($decodedResult[0]); //($transResults[0]); //just arbitrarily picked the first row (position 0) to get the object property values
					// $objectKeys = array_keys($propertiesArray);
					// //var_dump($objectKeys);
					// //var_dump($transResults);

					// $columnCount = count($objectKeys);
		?>

	<table class="table table-bordered table-striped table-hover">
		<thead>
			<tr>
				<?php
					// foreach($objectKeys as $heading)
					// {
					// 	echo "<th>".$heading."</th>";
					// }
				?>
				<!--Begin search columns-->
				<th>Transaction ID</th>
				<th>Transaction Date</th>
				<th>Proprietary Name</th>
				<th>Non-Proprietary Name</th>
				<th>Lot Number</th>
				<th>Expire Date</th>
				<th>Description</th>
				<th>Transaction Doses</th>
				<th>Transaction Type</th>
				<!--End search columns-->

				<th>Options</th>
			</tr>
		</thead>

		<tbody id="transList">
			<?php
				// if ((count($transResults) == 1) && ($decodedResult[0]->{'Transaction ID'} == 'Transaction ID')) //If the result array (decodedResult) only has 1 record & the 1 record == the table heading (this is tested by checking if the row's first property ("Transaction ID") == the heading name (i.e. 'Transaction ID'))  
				// {
				// 	$columnCount += 1; //Add an extra column to $columnCount to account for the "Options" column (which is manually added & is not part of the columns in a query result)
				// 	echo "<tr><td colspan='$columnCount'>No Transactions</td><tr>";
				// }

				// else
				// {
				// 	$rowIndex = 0;

				// 	foreach($decodedResult as $row)
				// 	{
				// 		$transID = $row->{$objectKeys[0]}; //Index position 0 contains the Transaction ID according to the SQL query

				// 		echo "<tr id='transRow".$rowIndex."'>"; //$row->{$objectKeys[0]}."'>";
				// 			for($count = 0; $count < $columnCount; $count++)
				// 			{
				// 				$cellData = "<td>".$row->{$objectKeys[$count]}; //."</td>";

				// 				if($objectKeys[$count] == 'Transaction Doses') //Add a label to the Transaction Doses cell (to be used for error messages)
				// 				{
				// 					//$transID = $row->{$objectKeys[$count]};
				// 					$cellData .= "<label id='transDosesTransID".$transID."'></label>";
				// 				}

				// 				$cellData .= "</td>";

				// 				echo $cellData;
				// 			} //End for loop

				// 			//Add Edit & Delete Button to Row
				// 			echo "<td>".anchor("/Inventory/EditTransactions/#", "Edit", array('id' => 'edit'.$rowIndex, 'class' => 'modifyTransBtn', 'data-modifyType' => 'edit', 'data-transID' => $transID))." ".anchor("Inventory/EditTransactions/#", "Delete", array('id'=>'delete'.$rowIndex, 'class' => 'modifyTransBtn', 'data-modifyType' => 'delete', 'data-transID' => $transID))." ".anchor("Inventory/EditTransactions/#", "Update", array('id'=> 'update'.$rowIndex, 'class' => 'modifyTransBtn modifyTransUpdate', 'data-modifyType' => 'update' , 'data-transID' => $transID, 'data-rowID' => $rowIndex))."</td>";
				// 			$rowIndex++;

				// 		echo "</tr>";
				// 	} //End foreach
				// } //End Else
			?>
		</tbody>

	</table>

</div>

</div> <!-- /End .row -->


<script>
	// $(document).ready()
	// {
	// 	FillTransTable("all");
	// }

	function FillTransTable(aTransType)
	{
		var transType = aTransType;

		$.ajax({
			url: "<?php echo site_url('Inventory/FilterTransactions'); ?>",
			method: "POST",
			data: {'transType': transType, 'dataReturnType': "JSON"},
			dataType: "JSON",
			success: function(filteredResult){
				//Feedback to console
				console.log("success");
				//console.log(filteredResult);
				
				//Clear the rows in the tbody element
				$("#transList").empty();

				//Build new table with the results received back
				var tblRows = null;

				if(filteredResult.length == 0) //If no results are returned, tell user there are no transactions
				{
					tblRows = "<tr><td colspan='10' style='text-align: center'>No Transactions</td></tr>";
				} //End if
				else
				{
					//Iterate through each row object
					$.each(filteredResult, function(rowIndex, rowObject){
						//console.log("Key: " + rowIndex + "Value: " + rowObject + "\n");
						
						tblRows += "<tr id='transRow" + rowIndex + "'>"; //Open new row
						var transID = null; //Stores transID value

							//Iterate through each column property in a given row
							$.each(rowObject, function(rowColName, rowColValue){
								
								if(rowColName == "Transaction ID")
								{
									transID = rowColValue;
									console.log(transID);
								}
								
								if(rowColName == "Transaction Doses") //Adds a label to the transaction doses cell so an error message can be displayed if the user enters an invalid value
								{
									tblRows += "<td>" + rowColValue + "<label id='transDosesTransID" + transID + "'></label></td>";
									console.log("transdoses");
								}
								else
								{
									//console.log("Key: " + rowColName + "Value: " + rowColValue + "\n");
									tblRows += "<td>" + rowColValue + "</td>";
								}

							});
						
							//Add "Edit"/"Delete" options to each row
							tblRows += "<td><a id='edit" + rowIndex + "' href='#' class='modifyTransBtn' data-modifyType='edit' data-transID='" + transID + "'>Edit</a> <a id='delete" + rowIndex + "' href='#' class='modifyTransBtn' data-modifyType='delete' data-transID='" + transID + "'>Delete</a> <a id='update" + rowIndex + "' href='#' class='modifyTransBtn modifyTransUpdate' data-modifyType='update' data-transID='" + transID + "'>Update</a></td>";
						//	tblRows += "<?php echo anchor('', 'Edit').anchor('', 'Delete'); ?>";
						//	tblRows += "</td>";

						tblRows += "</tr>"; //Close new row

						//tblRows += "<tr><td>" + value + "</td></tr>";
					});

				} //End else	

				//Add rows to table
				$("#transList").append(tblRows);

				

			},
			error: function(errorMsg, txtStatus, thrownError){
				//Feedback to console
				console.log("An error occurred: " + errorMsg.responseText);
			}
		});
	} //End FillTransTable()

	//AJAX function to filter the transactions by type
	$("input[type='radio']").change(function(){
		
		var theTransType = $(this).val();

		FillTransTable(theTransType);

		// if(transactionType == undefined)
		// {
		// 	transactionType = 'all';
		// }

		// $.ajax({
		// 	url: "<?php echo site_url('Inventory/FilterTransactions'); ?>",
		// 	method: "POST",
		// 	data: {'transType': transactionType},
		// 	dataType: "JSON",
		// 	success: function(filteredResult){
		// 		//Feedback to console
		// 		console.log("success");
				
		// 		//Clear the rows in the tbody element
		// 		$("#transList").empty();

		// 		//Build new table with the results received back
		// 		var tblRows = null;

		// 		//Iterate through each row object
		// 		$.each(filteredResult, function(rowIndex, rowObject){
		// 			//console.log("Key: " + rowIndex + "Value: " + rowObject + "\n");
					
		// 			tblRows += "<tr id='transRow" + rowIndex + "'>"; //Open new row
		// 			var transID = null; //Stores transID value

		// 				//Iterate through each column property in a given row
		// 				$.each(rowObject, function(rowColName, rowColValue){
							
		// 					if(rowColName == "Transaction ID")
		// 					{
		// 						transID = rowColValue;
		// 						console.log(transID);
		// 					}
							
		// 					if(rowColName == "Transaction Doses") //Adds a label to the transaction doses cell so an error message can be displayed if the user enters an invalid value
		// 					{
		// 						tblRows += "<td>" + rowColValue + "<label id='transDosesTransID" + transID + "'></label></td>";
		// 						console.log("transdoses");
		// 					}
		// 					else
		// 					{
		// 						//console.log("Key: " + rowColName + "Value: " + rowColValue + "\n");
		// 						tblRows += "<td>" + rowColValue + "</td>";
		// 					}

		// 				});
					
		// 				//Add "Edit"/"Delete" options to each row
		// 				tblRows += "<td><a id='edit" + rowIndex + "' href='#' class='modifyTransBtn' data-modifyType='edit' data-transID='" + transID + "'>Edit</a> <a id='delete" + rowIndex + "' href='#' class='modifyTransBtn' data-modifyType='delete' data-transID='" + transID + "'>Delete</a> <a id='update" + rowIndex + "' href='#' class='modifyTransBtn modifyTransUpdate' data-modifyType='update' data-transID='" + transID + "'>Update</a></td>";
		// 			//	tblRows += "<?php echo anchor('', 'Edit').anchor('', 'Delete'); ?>";
		// 			//	tblRows += "</td>";

		// 			tblRows += "</tr>"; //Close new row

		// 			//tblRows += "<tr><td>" + value + "</td></tr>";
		// 		});

		// 		//console.log(tblRows);

		// 		//Add rows to table
		// 		$("#transList").append(tblRows);


		// 	},
		// 	error: function(errorMsg, txtStatus, thrownError){
		// 		//Feedback to console
		// 		console.log("An error occurred: " + errorMsg.responseText);
		// 	}
		// });


	}); //End $(input[type='radio']).change()

	//AJAX function to edit or delete a transaction
	$(document).on("click", "a.modifyTransBtn",  function(eventObject){
		
		//Check the type of button which was clicked (the edit or delete button)
		//btnType = $(this);

		// var btnType = $(this).data('modifyType');
		// console.log(event.target);
		// console.log(typeof(event.target));		

		var clickedBtn = eventObject.target;
		var btnType = $(clickedBtn).attr('data-modifyType');
		var transID = $(clickedBtn).attr('data-transID');
		var rowID = $(clickedBtn).attr("id");
		console.log("Here is the row id: " + rowID);

		// console.log(btnType);
		switch(btnType)
		{
			case "edit":
			/* Will be done in Iteration 2	//Take user to form where they can edit the selected transaction */

				//Get row id
				rowID = rowID.substr(4, rowID.length);
				console.log("Here is rowid without edit: " + rowID);

				//Cause Update link to be displayed
				var updateID = "#update" + rowID;
				$(updateID).css('display', 'inline');

				//Store table row object (http://www.w3schools.com/jsref/dom_obj_tablerow.asp)
				var row = document.getElementById("transRow" + rowID);
				transID = row.cells[0].innerHTML; //Get transaction id
				//console.log(row);

				//Change background-color off lot, expire, & transaction doses cells so user knows 
				// var cellRange = 4, 5, 7 (index positions)
				row.cells[4].style.backgroundColor = '#DEE7FF'; //#DEE7FF //#ADC2FF /* #ADC2FF #B5C8FF #BDCEFF #C6D4FF #CEDAFF #D6E0FF #DEE7FF */
				row.cells[5].style.backgroundColor = '#DEE7FF';
				row.cells[7].style.backgroundColor = '#DEE7FF';

				//Set text color of editable cells
				row.cells[4].style.color = "#666666";
				row.cells[5].style.color = "#666666";
				row.cells[7].style.color = "#666666";

			//	row.cells[4].removeAttribute('backgroundColor');

				//If transtype (cell 8 in a given row) == 'Invoice', then display the package qty & the dose/package in 2 subcells to "Transaction Doses"
				//Do an ajax request to get the Package & Doses/Package Information
				if(row.cells[8].innerHTML == "Invoice")
				{
					//var transID = row.cells[0].innerHTML;
					var packages = null;
					var dosesPerPackage = null;

					//AJAX request to get "Doses Per Package" & "Total Packages"
					$.ajax({
						url: "<?php echo site_url('Inventory/GetPackageAndDoses'); ?>",
						method: 'POST',
						data: {'TransID': transID},
						dataType: 'JSON',
						success: function(data) //"data" is a JSON object - in this case, the results are an array of object. So you reference the data by first referencing the array index you want from "data", then the object properties you want from the object in that index (using "dot" notation)
						{
							console.log("Success");
							packages = data[0].PACKAGEQTY; 
							dosesPerPackage = data[0].DOSES_PER_PACKAGE;

							//Create table within the "Transaction Doses" column
							row.cells[7].innerHTML = "<table id='innerTable" + rowID + "' class='invoiceInnerTable'>" +
														"<tr>" +
															"<th>Packages</th>" +
															"<th>Doses/Package</th>" +
														"</tr>" +
														"<tr id='rowID" + rowID + "innerRow1'>" +
															"<td contenteditable='true'>" + packages + "</td>" +
															"<td contenteditable='true'>" + dosesPerPackage + "</td>" +
														"</tr>" +
													  "</table>" + 
													  "<label id='transDosesTransID" + transID + "'></label>";

						},
						error: function(errorObject)
						{
							console.log(errorObject);
						}
					});
					
				} //End If(row.cells[8].innerHTML == 'Invoice')
				else //Transaction Type isn't "Invoice" & so it is fine for the user to "wipe out" anything in cell 7
				{
					row.cells[7].setAttribute('contenteditable', 'true');

					//If current cell value is different than the database value,
					//then display the database value
					var storedDoses = 0;

					var doseString = row.cells[7].innerHTML;
					var charIndex = doseString.search("<"); //Finds the index value for the "<" (opening html tag of the label element) in the string within the 7th column within the selected row. The 7th column contains the transaction doses number but also a label element (need to extract the quantity from the label element)

					var userDoses = parseInt(doseString.slice(0, charIndex));

					$.ajax({
						url: "<?php echo site_url('Inventory/GetDoses'); ?>",
						method: "POST",
						data: {'TransID': transID, 'TransType': row.cells[8].innerHTML},
						dataType: "JSON",
						success: function(data){
							//Compare stored doses to the doses in the cell (if different, change to the stored does value)
							storedDoses = (data[0]['Doses Given']) * -1; //See this link for json keys with spaces: http://stackoverflow.com/questions/10311361/accessing-json-object-keys-having-spaces
							//console.log(data);

							if(storedDoses != userDoses)
							{
								//Change value in cell
								row.cells[7].innerHTML = storedDoses + "<label id='transDosesTransID" + transID + "'></label>";
							}

							////Clear any error message in the cell's error message label element (if not clear)
							// var labelID = "#transDosesTransID" + transID;
							// console.log("TransID: " + labelID);
							// $(labelID).html("");

						},
						error: function(errorObject){
							console.log("An error occurred");
							console.log(errorObject);
						}
					}); //End .ajax

				} //End else

				//Add "editable content" html attribute to Lot Number, ExpireDate, Transaction Doses
				row.cells[4].setAttribute('contenteditable', 'true');
				row.cells[5].setAttribute('contenteditable', 'true');

				break;
			case "update":
				rowID = rowID.substr(6, rowID.length);
				staticRowID = rowID;
				//console.log("Here's rowid without update: " + rowID);
				
				var row = document.getElementById('transRow' + rowID);
				//var updateID = "#update" + rowID;

				//Hide update button
				$(updateID).css('display', 'none');

				//Turn background color back to the inherited value:
				row.cells[4].style.backgroundColor = 'inherit';
				row.cells[5].style.backgroundColor = 'inherit';
				row.cells[7].style.backgroundColor = 'inherit';

				//Set "contenteditable" to false
				row.cells[4].setAttribute("contenteditable", 'false');
				row.cells[5].setAttribute('contenteditable', 'false');
				row.cells[7].setAttribute('contenteditable', 'false');

				//Submit data update request
				//Get values in transID, lot num, expire date, transaction qty
				var transID = row.cells[0].innerHTML;
				var lot = row.cells[4].innerHTML;
				var expiration = row.cells[5].innerHTML;
				var transType = row.cells[8].innerHTML;

				var transQty = null;
				var packageQty = null;
				var dosesPerPackage = null;

				if(transType == "Invoice")
				{
					rowID = "rowID" + rowID + "innerRow1";
					rowArray = document.getElementById(rowID); //Gets a row in the table created in the "Transaction Doses" cell (this table is created only after the "Edit" button is clicked for "Invoice" transactions)

					packageQty = rowArray.cells[0].innerHTML; //Cell 1 in the inner table in the "Transaction Doses" cell
					dosesPerPackage = rowArray.cells[1].innerHTML; //Cell 2 in the inner table in the "Transaction Doses" cell
					transQty = packageQty * dosesPerPackage;
				}
				else //If transaction isn't of type "Invoice", then get the transQty from the "Transaction Doses" cell directly (there won't be any "inner table" in this cell for non-Invoice transactions)
				{
					transQty = parseInt(row.cells[7].innerHTML);
					console.log(transQty);
					
					// if (transQty < 0)
					// {
					// 	transQty = Math.abs(transQty); //Turn negative value positive (for entry into database)
					// }
				}

				//console.log(transID + " " + lot + " " + expiration + " " + transQty);

				//If transaction doses are negative & transaction type is Invoice or Loan Return, make user reenter a positive value
				if(transQty <= 0 && (transType == 'Invoice' || transType == 'Loan Return')) // && transType == 'Invoice') || (transQty == 0 && transType != 'Invoice'))
				{
					//Message user about negative values
					var labelID = "transDosesTransID" + transID;
					// console.log(labelID);
					var label = document.getElementById(labelID);
					label.innerHTML = "(Doses Cannot Be Less than 1)";

					//Highlight transaction doses by changing color to red 
					row.cells[7].style.backgroundColor = "#B00000";
					row.cells[7].style.color = "white";

					//Change border color of inner table to white
					elements = "#innerTable" + staticRowID + " th, #innerTable" + staticRowID + " td";
 					$(elements).css("border-color", "#C9C9C9");
				}
				else if(transQty == 0 && (transType == 'Administer' || transType == 'Loan Out'))
				{
					//Message user about negative values
					var labelID = "transDosesTransID" + transID;
					// console.log(labelID);
					var label = document.getElementById(labelID);
					label.innerHTML = "(Doses Cannot Be 0)";

					//Highlight transaction doses by changing color to red 
					row.cells[7].style.backgroundColor = "#B00000";
					row.cells[7].style.color = "white";

					//Allow user to edit cell
					row.cells[7].setAttribute('contentEditable', "true");

				}
				else{ //If transaction doses are positive, update the transaction information

					//If transQty < 0, then make it positive for database storage (this could happen (based on the preceeding if statments) if the user entered a negative value for a Loan Out or Administer transaction)
					if(transQty < 0)
					{
						transQty = transQty * -1;
					}

					//Set background color of quantity cell to inherit (in case it was turned to red b/c the value was <= 0)
					row.cells[7].style.backgroundColor = "inherit";
					row.cells[7].style.color = "#666666";

					//Set message in the quantity label to ""
					var labelID = "transDosesTransID" + transID;
					var label = document.getElementById(labelID);
					label.innerHTML = '';

					//AJAX request to update record
					$.ajax({
						url: "<?php echo site_url('Inventory/EditSingleTransaction'); ?>",
						method: "POST",
						data: {'TransID': transID, 'LotNum': lot, 'Expiration': expiration, 'TransQty': transQty, 'TransType': transType, 'PackageQty': packageQty, 'DosesPerPackage': dosesPerPackage},
						dataType: "JSON",
						success: function(updatedRecord){
							//console.log("Success");
							console.log(updatedRecord);
						},
						error: function(errorObject){
							console.log("Error");
						}
					});

					//If transaction is "Invoice", clear the temporary table in Transaction Doses & replace it with the new total doses amount (packages * doses per package) as well as an error message label. Also covers Loan Return
					if(transType == 'Invoice' || transType == 'Loan Return')
					{
						row.cells[7].innerHTML = transQty + "<label id='transDosesTransID" + transID + "'></label>";
					}
					else if(transType == 'Administer' || transType == 'Loan Out') //Turns the Administer & Loan Out DISPLAYED quantities negative (stored values are always positive) 
					{
						row.cells[7].innerHTML = (transQty * -1) + "<label id='transDosesTransID" + transID + "'></label>";
					}

				}

				break;
			case "delete":
				 rowID = rowID.substr(6, rowID.length);
				// console.log("Here's rowid without delete:" + rowID);

				var row = document.getElementById('transRow' + rowID);

				//Prompt the user to make sure they want to delete the record
				//(later)

				//If user clicks "yes" then delete record, else return to results page
				//(later)

				//Delete record
				$.ajax({
					url: "<?php echo site_url('Inventory/DeleteTransaction'); ?>",
					method: "POST",
					data: {'TransID': transID, 'TransType': row.cells[8].innerHTML},
					dataType: "JSON",
					success: function(returnValue)
					{
						if(returnValue == 'True')
						{
							console.log("Deleted");

							//refresh the the transaction table (so old value isn't there)
							//$("input[type='radio']").change();
							//Find the selected radio button
							selectedType = $("input[type='radio']:checked").val();
							//alert(selectedType);

							FillTransTable(selectedType);
						}
						else
						{
							console.log("Not deleted");
						}

					},
					error: function(errorObject)
					{
						console.log("An error occurred. Transaction was not deleted");
					}

				}); //End $.ajax()

				break;
			default:
				console.log("An Error Occurred");
				break;
		}


		//alert(btnType.getAttribute('data-modifyType'));



		//	$(this).data("modifyType"));


			// var clickedBtn = $(this);
			// var btnType = clickedBtn.data("modifyType");

			// //identify the id of the parent row
			// var parentID = $(clickedBtn).parent();
			// parentID = $(parentID).attr("id");

			// console.log(parentID);

		//get all children of that <tr> element (will allow you to select which columns in the result are updateable)


		//siblings element with class .modifyTransBtn


		//select the sibling that is type "update"




		// switch(btnType)
		// {
		// 	case 'edit':
		// 		//Make certain columns in rows editable

		// 		//Unhide the "update button" for that row only
		// 		$(clickedBtn).siblings()
		// 		break;
		// 	case 'delete':

		// 		break;
		// 	case 'update':
		// 		//Make row uneditable

		// 		//Send new data off to server to update record
				
		// 		//Hide the update button
				
		// 		break;
		// 	default:

		// 		break;
		// }


	}); //End $(.modifyTransBtn).click()


</script>





<!--Body closed in footer-->