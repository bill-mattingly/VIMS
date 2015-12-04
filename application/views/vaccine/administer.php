<!--Body opened in header file-->

	<div class="row col-md-12">
		<h1>Administer Vaccine Form</h1>
		<p>Enter Vaccine Data Below:</p>

		<?php
	        echo validation_errors();

	        $attributes = array('id' => 'administer-form');
			echo form_open("Inventory/Administer", $attributes);

			// echo "<div class='form-group'>";
			// 	echo "<input id='travel' class='patientType' type='radio' name='patientType' data-price='".$trvlPrice."' onclick='SelectPatientPrice();'>";
			// 	echo "<label id='administer-travel' for='travel'>Travel Patient</label>";
			
			// 	echo "<input id='refugee' class='patientType' type='radio' name='patientType' data-price='".$refugeePrice."' onclick='SelectPatientPrice();'>";
			// 	echo "<label id='administer-refugee' for='refugee'>Refugee Patient</label><br/>";
			// echo "</div>";

			echo "<div class='form-group'>";
				echo "<label for='ndc10' class='actionform'>NDC 10:</label>";
				echo "<input id='ndc10' type='text' name='ndc10' value='".$ndc10."' readonly><br/>";
			echo "</div>";

			// echo "<div class='form-group'>";
			// 	echo "<label for='ndc11' class='actionform'>HIPAA NDC:</label>";
			// 	echo "<input id='ndc11' type='text' name='ndc11' value='".$ndc11."' readonly>";
			// echo "</div>";

			echo "<div class='form-group'>";

				//echo $this->session->barcodeArray['drugID'];

				echo "<label for='lotNumList' class='actionform'>Lot Number:</label>";
				//echo "<input id='lotNum' type='text' name='lotNum' value='".$lotNum."' placeholder='Enter a Lot Number'>";

				echo "<select id='lotNumList' name='lotNumList'>";
					echo "<option value='-1' data-expireDate='' selected>Select Lot Number</option>";

					foreach($vaccineArray as $lotArray)
					{

						echo "<option value='".$lotArray->{'Lot Number'}."' data-expireDate='".$lotArray->{'Expire Date'}."'>".$lotArray->{'Lot Number'}."</option>";
					}
				echo "</select>";

			echo "</div>";

			echo "<div class='form-group'>";
				echo "<label for='expireDate' class='actionform'>Expiration Date:</label>";
				echo "<input id='expireDate' name='expireDate' type='text' placeholder='Expiration Date' readonly>"; //Will create datepicker widget: id='datepicker' //Old value: value='".$expireDate."'
			echo "</div>";

			echo "<div class='form-group'>";
				echo "<label for='clinicCost' class='actionform'>Clinic Cost:</label>";
				echo "<input id='clinicCost' type='text' name='clinicCost' value='".$clinicCost."' placeholder='Enter Clinic&#39;s Cost'>"; //' readonly>";
			echo "</div>";


			echo "<div class='form-group'>";
				echo "<input id='travel' class='patientType' type='radio' name='patientType' data-price='".$trvlPrice."' onclick='SelectPatientPrice();'>";
				echo "<label id='administer-travel' for='travel'>Travel Patient</label>";
			
				echo "<input id='refugee' class='patientType' type='radio' name='patientType' data-price='".$refugeePrice."' onclick='SelectPatientPrice();'>";
				echo "<label id='administer-refugee' for='refugee'>Refugee Patient</label><br/>";

		//	echo "<div class='form-group'>";
				echo "<label for='customerChrg' class='actionform'>Patient Charge:</label>";
				echo "<input id='customerChrg' type='text' name='customerChrg' placeholder='Enter Patient&#39;s Charge'>";
			echo "</div>";
		?>

		<div>
			<label id="doseQtyErrorMsg"></label>
		</div>

		<div class='form-group'>
			<label for="doseQty" class='actionform'>Doses Administered:</label>
			<?php 
				//var_dump($dataAttributeArray);
				echo "<input id='doseQty' type='number' name='doseQty' $dataAttributes value='' min='1' step='1' max='' placeholder='Doses Administered'>";
			?>
			<label id="maxDoseQty"></label> <!--Displays the current maximum dose quantity for a given lot number-->
		</div>

		<div class='form-group'>
			<input type="submit" name="Add">
		</div>

	</form> <!--Close form-->

	<!--Takes users back to home page-->
	<button type="button"><?php echo anchor('Inventory/Index', 'Cancel'); ?></button>

</div> <!-- /End .row -->

<script>
	//When page has loaded, make AJAX request for expiration date &
	//then populate the ExpireDate field
	$(document).ready(function(){

		//Initial page state
		//Disable Clinic Cost, Patient Charge Radio buttons & textboxes, Doses Administered textbox, & submit button until user selects a lot number
		$("#clinicCost").prop('disabled', true);
		$("#travel").prop('disabled', true);
		$("#refugee").prop('disabled', true);
		$("#customerChrg").prop('disabled', true);
		$("#doseQty").prop('disabled', true);
		$("input[type=submit]").prop('disabled', true);

		//Hide the doseQtyErrorMsg label
		$("#doseQtyErrorMsg").hide(); //.css("display", "none");


		//Change value in expireDate element and the "max" property in the doseQty element based on the selected lot number
		$("#lotNumList").change(function()
		{
			//Get selected lotNumber
			var selectList = document.getElementById("lotNumList");
			var option = selectList.options[selectList.selectedIndex];

			if(selectList.selectedIndex == 0) //If the first option in the list is selected ("Select Lot Number"), then clear expiration date & doses administered fields
			{
				//Clear expiration date field
				$("#expireDate").val('');

				//Clear doses administered field
				var doseQty = document.getElementById('doseQty');

				//doseQty.innerHTML = ''; //Clear displayed value
				doseQty.value = ''; //Clear value attribute
				doseQty.setAttribute('max', ''); //Clear max attribute
				doseQty.setAttribute('min', ''); //Clear min attribute

				//Clear the maxDoseQty label
				$("#maxDoseQty").html('');

				//Disable Clinic Cost, Patient Charge Radio buttons & textboxes, Doses Administered textbox, & submit button
				$("#clinicCost").prop('disabled', true);
				$("#travel").prop('disabled', true);
				$("#refugee").prop('disabled', true);
				$("#customerChrg").prop('disabled', true);
				$("#doseQty").prop('disabled', true);
				$("input[type=submit]").prop('disabled', true); //See jQuery documentation: https://api.jquery.com/submit-selector/

				//Clear any value in customerChrg textbox & deselect any selected radio options (either the 'travel' or 'refugee' radio option)
				$("#travel").prop("checked", false);
				$("#refugee").prop("checked", false);
				$("#customerChrg").val("");

			}
			else
			{
				//Set value in expireDate
				var date = option.getAttribute('data-expireDate');
				$("#expireDate").val(date);

				//Set value of doseQty to 1 (as default)
				$("#doseQty").val(1);

				//Set "max" attribute in doseQty element and display this max value in the maxDoseQty label element
				var lotNumber = option.innerHTML;
				var maxDoseQty = document.getElementById('doseQty').getAttribute('data-'+lotNumber); //.dataset.lotNumber; //$('#doseQty').data("'"+lotNumber+"'");
				//console.log(maxDoseQty);

				$('#doseQty').attr('max', maxDoseQty); //set 'max' attribute
				$('#doseQty').attr('min', '1'); //set 'min' attribute
				$('#maxDoseQty').html("Max Qty: " + maxDoseQty); //set value of label element

				//Enable Clinic Cost, Travel & Refugee Radio buttons, Patient Charge textbox, Doses Administered Textbox, & Submit button
				$("#clinicCost").prop('disabled', false);
				$("#travel").prop('disabled', false);
				$("#refugee").prop('disabled', false);
				$("#customerChrg").prop('disabled', false);
				$("#doseQty").prop('disabled', false);
				$("input[type=submit]").prop('disabled', false);

			}

			$("#doseQty").keyup(function(eventObject)
			{
				var min = parseInt($("#doseQty").prop('min'), 10); //parseInt(string, radix) - "radix" is the base of the number (i.e. base 2, base 10, etc.)
				var max = parseInt($("#doseQty").prop('max'), 10);

				var qty = parseInt(document.getElementById('doseQty').value, 10); //$("#doseQty").val();


				if(qty > max)
				{
					//console.log("Qty greater than max");
					//Set doseQty value = max
					$("#doseQty").val(max);
					
					//Display error message
					$("#doseQtyErrorMsg").show(); //.prop("display", "block");

					if(max == 1)
					$("#doseQtyErrorMsg").val("Cannot Administer More than 1 Dose");
					else
					{
						$("#doseQtyErrorMsg").html("Cannot Administer More than " + max + " Doses");
					}

				}
				else if(qty < min)
				{
					//console.log("Qty less than min");
					//Set doseQty value = min & tell user they can't enter less than min
					$("#doseQty").val(min);

					//Display error message
					$("#doseQtyErrorMsg").show(); //.prop("display", "block");
					$("#doseQtyErrorMsg").html("Cannot Administer Less than 1 Dose");

				}
				else //If qty >= min and qty <= max then clear the error message 
				{
					//Clear the error message & hide the element
					$("#doseQtyErrorMsg").html("");
					$("#doseQtyErrorMsg").hide(); //.prop("display", "none");
				}

			});

		});






		// var currentURL = document.location.href;


		// if($('#lotNum').val() != null && $('#lotNum').val() != undefined)
		// {
		// 	$.ajax(
		// 		url: 
		// 		data: {vaccineLotNum: $('#lotNum').val()}, 
		// 		type: 'POST',
		// 		success: function(result)
		// 		{
		// 			//Set value in the ExpireDate field
		// 			$('#expireDate').val(result);
		// 		},
		// 		error: function(result)
		// 		{
		// 			console.log('An error occurred');
		// 		}
		// 	);
		// }

		// );
	});

</script>

<!--Body closed in footer file-->