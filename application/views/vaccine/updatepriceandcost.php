<!--Body opened in header -->


<!-- General Price/Cost Change
search from (first 3 letters of proprietary name &amp; first 3 letters of labelername)
scan barcode (a linear, vaccine vial, or carton)
(have user select radio button: vial or carton)

==============
Example query:
SELECT * FROM `fda_drug_package` pa inner join `fda_product` pr on pa.productid = pr.productid where pr.proprietaryname like 'am%' and pr.labelername like 'e%'
==============


Change Current Inventory Prices
(select products by labeler that are in inventory) -->

<h1>Update Vaccine Cost and Price Data</h1>

<div class='row col-md-8'>
<div class='panel panel-default'>
<div class='panel-heading'>
	<p>Select a Method to Search for the Vaccine</p>
</div>

<div class='panel-body'>
<!-- scan a barcode -->
<div class="row col-md-6 col-sm-12">
	<div class="panel panel-default">
		<div class="panel-heading">
			<p>Search By Barcode</p>
		</div> <!-- /End .panel-heading -->
		<div class="panel-body">
			<p>Scan Barcode Below</p>
			<div class='form-group'>
				<input id="cartonBarcode" type="radio" name="vacBarcodeType" value="carton">
				<label for="cartonBarcode">Carton/Box</label>
				<input id="vialBarcode" type="radio" name="vacBarcodeType" value="vial">
				<label for="vialBarcode">Vial</label>
			</div> <!-- /End .form-group -->

			<div class='form-group'>
				<label for='barcode'>Barcode:</label>
				<input id='barcode' type='text' name='barcode' placeholder='Scan Barcode'>
			</div> <!-- /End .form-group -->

			<div class='form-group'>
				<p id="barcodeSearchMsg"></p> <!--Displays msg to user if item can't be found-->
				<button id='btnBarcodeSearch' type="button" disabled>Search</button>
			</div> <!-- /End .form-group -->

		</div> <!-- /End .panel-body -->
	</div> <!-- /End .panel -->
</div> <!-- /End .row -->

<!-- search by proprietary name & labeler name -->

<div class="row col-md-6 col-sm-12">
	<div class="panel panel-default">
		<div class="panel-heading">
			<p>Search By Proprietary Name</p>
		</div> <!-- /End .panel-heading -->
		<div class="panel-body">
			</p>Begin typing a vaccine's proprietary name below:</p>

			<div class='form-group'>
				<label for='proprietaryName'>Proprietary Name:</label>
				<input id='proprietaryName' type='text' name='proprietaryName' placeholder='Proprietary Name'>
			</div> <!-- /End .form-group -->

			<!--
			<div class='form-group'>
				<label for='labelerName'>Labeler Name:</label>
				<input id='labelerName' type='text' name='labelerName' maxlength='3' placeholder='&#40;Ex. &#39;Mer&#39; for Merck&#41;'>
			</div> --> <!-- /End .form-group -->

			<div class='form-group'>
				<p id="nameSearchMsg"></p> <!--Displays msg to user if item can't be found-->
				<button id='btnNameSearch' type='button' disabled>Search</button>
			</div> <!-- /End .form-group -->

		</div> <!-- /End .panel-body -->
	</div> <!-- /End .panel -->
</div> <!-- /End .row -->

 </div>
</div>
</div> 

<!-- display search results -->
<!-- users selects a search result & cost & price info is displayed in form -->
<div class='row col-md-4'>
	<div class='panel panel-default'>
		<div class='panel-heading'>
			<p>Cost and Price Results</p>
		</div> <!-- /End .panel-heading -->

		<div class='panel-body'>


			<select id='vacSelectList' size='5' disabled>
				<option value='-1'>Select Vaccine</option>
				<!--Select options populated based on user's search criteria-->
			</select>

				<div class='form-group'>
					<label for='vacName'>Vaccine Name:</label>
					<input id='vacName' type='text' value='' placeholder='Vaccine Name' disabled>
				</div>

				<div class='form-group'>
					<label for='drugCost'>Clinic Cost:</label>
					<input id='drugCost' type='text' value='' placeholder='Clinic&#39;s Cost' disabled>
				</div>

				<div class='form-group'>
					<label for='travelPrice'>Travel Patient Price:</label>
					<input id='travelPrice' type='text' value='' placeholder='Travel Price' disabled>
				</div>

				<div class='form-group'>
					<label for='refugeePrice'>Refugee Patient Price:</label>
					<input id='refugeePrice' type='text' value='' placeholder='Refugee Price' disabled>
				</div>


			<?php
			// 	echo "<select id='vacSelectList' size='5' disabled>";
			// 		echo "<option value='-1'>Select Vaccine</option>";
			// 		// foreach()
			// 		// {
			// 		// 	echo "<option value='"."'>"."</option>";
			// 		// }
			// 	echo "</select>";

			// 	//Form to display & update cost/price data
				
			// //	echo validation_errors();
			// //	$attributes = array('class' => 'updatePriceCost-form');
			// //	echo form_open('Inventory/UpdatePriceAndCost', $attributes);

			// 	echo "<div class='form-group'>";
			// 		echo "<label for='vacName'>Vaccine Name:</label>";
			// 		echo "<input id='vacName' type='text' value='"."' disabled>";
			// 	echo "</div>";

			// 	echo "<div class='form-group'>";
			// 		echo "<label for='drugCost'>Clinic Cost:</label>";
			// 		echo "<input id='drugCost' type='text' value='"."' disabled>";
			// 	echo "</div>";

			// 	echo "<div class='form-group'>";
			// 		echo "<label for='travelPrice'>Travel Patient Price:</label>";
			// 		echo "<input id='travelPrice' type='text' value='"."' disabled>";
			// 	echo "</div>";

			// 	echo "<div class='form-group'>";
			// 		echo "<label for='refugeePrice'>Refugee Patient Price:</label>";
			// 		echo "<input id='refugeePrice' type='text' value='"."' disabled>";
			// 	echo "</div>";
			?>

				<p id="updateMsg"></p> <!--Display's msg to user if item can't be found-->

				<button id='btnUpdatePriceCost' type="button" disabled>Update</button> <!-- type='submit' value='Update' -->
				
			<!-- </form> -->
		</div> <!-- /End .panel-body -->
	</div> <!-- /End .panel -->
</div> <!-- /End .row -->

	<!--AJAX Preloader Image-->
	<div id="AJAXPreloader"></div>


<script type='text/javascript'>
	
	//Enable update button 
	$("#barcode").change(function(){
		if($("input:checked").val() != undefined && $("#barcode").val().length >= 12){ //Runs if the barcode type radio button is selected 
			$("#btnBarcodeSearch").prop("disabled", false);
		}
		else{
			//Disable & clear select list
			$("#vacSelectList").empty();
			$("#vacSelectList").append("<option value='-1'>Select Vaccine</option>");
			$("#vacSelectList").prop("disabled", true);

			//Disable search button
			$("#btnBarcodeSearch").prop("disabled", true);

			//Want some type of prompt to appear to tell user to select a
			//vaccine type from the radio button options & scan a barcode
		}

	});

	$('#btnBarcodeSearch').click(function(){

		//Begin AJAX Preload animation
		$("#AJAXPreloader").css('display', 'block');

		var theCode = $('#barcode').val();
		var codeType = $("input:checked").val(); //In jQuery syntax, means look for input tags with checked property == true (see documentation: https://api.jquery.com/checked-selector/)
		// var aCartonBarcode = $('#cartonBarcode').val();
		// var aVialBarcode = $('#vialBarcode').val();

		console.log(theCode + ", " + codeType);//+ aCartonBarcode + ", " + aVialBarcode);
		
		//Clear any msg in the "barcodeSearchMsg" element
		$("#barcodeSearchMsg").empty();


		//AJAX request
		$.ajax(
		{
			url: "<?php echo site_url('Inventory/SearchBarcode'); ?>",
			method: "POST",
			data: {'barcodeString':theCode, 'barcodeType':codeType},
			dataType: "JSON",
			success: function(aVacResult)
			{
				//Enable select control
				$("#vacSelectList").prop("disabled", false);

				//Clear select list of previous results
				$("#vacSelectList").html("<option value='-1'>Select Vaccine</option>");
				var theCounter = 0;

				//Populate the select box with search results
				$.each(aVacResult, function(key, value){
					//console.log(theCounter + ": " + key + ", " + value);
					//console.log(theCounter + ": " + key + ", " + value);
					//console.log(theCounter + ": " + aVacResult.Name + ", " + aVacResult.Count);
					//console.log(aVacResult[theCounter].Name + "\n");
					theCounter++;
					//console.log(aVacResult.key.Name);
					$("#vacSelectList").append("<option value='" + key + "'>" + value + "</option>");
					//$("#vacSelectList").append("<option value='" + aVacResult.Name + "'>" + aVacResult.Name + " (" + aVacResult.Count +")</option>");
					//$("#vacSelectList").append("<option value='" + key + "'>" + key + " (" + value +")</option>");					
				});

				//End AJAX Preload animation
				$("#AJAXPreloader").css('display', 'none');

			},
			error: function(errorMsg)
			{
				console.log("An error occured \n" + errorMsg);

				//Display error msg to user
				$("#barcodeSearchMsg").css("display", "block");
				$("#barcodeSearchMsg").css("background-color", "#ffcccc");
				$("#barcodeSearchMsg").html("Barcode Doesn't Match A Vaccine. Please Check Barcode.");

				//End AJAX Preload animation
				$("#AJAXPreloader").css('display', 'none');
			}
		}); //End ajax

		// //End AJAX Preload animation
		// $("#AJAXPreloader").css('display', 'none');

	}); //End btnBarcodeSearch.click

	
	//Controls whether or not btnNameSearch is enabled or disabled
	//based on whether any text has been entered
	$("#proprietaryName").keyup(function(){
		if($("#proprietaryName").val().length < 1){
			//Disable search button
			$("#btnNameSearch").prop("disabled", true);

			//Clear values in select result box & disable select result box
			$("#vacSelectList").empty();
			$("#vacSelectList").append("<option value='-1'>Select Vaccine</option>");
			$("#vacSelectList").prop("disabled", true);
		}
		else{
			//Enable btnNameSearch
			$("#btnNameSearch").prop("disabled", false);
		}
	}); //End #proprietaryName.keyup

	$('#btnNameSearch').click(function(eventObject){//('#proprietaryName').keyup(function(eventObject){
		
		//Begin AJAX Preloader animation
		$("#AJAXPreloader").css('display','block');
		//document.getElementById('AJAXPreloader').style.display = "block";


		//Get the text entered into textbox
		var searchStr = $('#proprietaryName').val();// + eventObject.which;

		//Create ajax request
		$.ajax({
			url: "<?php echo site_url('Inventory/SearchProprietaryName'); ?>",
			method: "POST",
			data: {'proprietaryName': searchStr},
			dataType: "JSON",
			success: function(vacResult){
				
				//Enable the selectVacList control option
				$("#vacSelectList").prop("disabled", false);

				//Populate the select list
				$("#vacSelectList").html("<option value='-1' selected>Select Vaccine</option>");


				$.each(vacResult, function(key, value){
					$('#vacSelectList').append("<option value='" + key + "'>" + value + "</option>");
				});

				$("#AJAXPreloader").css('display', 'none');

			},
			error: function(errorMsg){
				console.log("An error occurred \n" + errorMsg);



				//Display error msg to user
				$("#nameSearchMsg").css("display", "block");
				$("#nameSearchMsg").css("background-color", "#ffcccc");

				$("#AJAXPreloader").css('display', 'none');
			}
		});

		//End AJAXPreloader animation
		//$("#AJAXPreloader").css('display', 'none');
	//	document.getElementById('AJAXPreloader').style.display = "none";

	}); //End #btnNameSearch.click


	$('#vacSelectList').change(function(){
		var selectedVac = $('#vacSelectList').val();
		
		//AJAX preloader
		$("#AJAXPreloader").css("display", 'block');


		//Enable the "Update" button in the vaccine results area
		//(enable the button whenever the user selects any option other than "Select Vaccine" (where selectedVac == -1))
		if(selectedVac == -1)
		{
			//Disable update button
			$("#btnUpdatePriceCost").prop("disabled", true);

			//Clear values in result controls
			ClearResultControls();

			//Disable result controls
			DisableResultControls();

		}
		else
		{
			//Enable all results controls
			EnableSearchResultControls();

			//Enable update price button
			$("#btnUpdatePriceCost").prop("disabled", false);
		}

		console.log("Different selection was chosen" + selectedVac);



		//Display the cost & price info
		$.ajax({
			url: "<?php echo site_url('Inventory/GetVacCostAndPrice');?>",
			method: "POST",
			data: {'selectedVac':selectedVac},
			dataType: "JSON",
			success: function(vacResult)
			{
				console.log("success");
				console.log("\n" + vacResult.Name);
				//console.log("\n" + vacResult[0].Name);

				//Populate result controls with selected vaccine's data
				$('#vacName').val(vacResult.Name);
				$('#drugCost').val(vacResult.Cost);
				$('#travelPrice').val(vacResult.Trvl_Chrg);
				$('#refugeePrice').val(vacResult.Refugee_Chrg);

				//Disable AJAX Preloader
				$("#AJAXPreloader").css("display",'none');

			},
			error: function(errorMsg)
			{
				console.log("An error occurred");

				//Disable AJAX Preloader
				$("#AJAXPreloader").css("display", "none");

				//Display message to user
				$("#updateMsg").css("display", "block");
				$("#updateMsg").css("background-color", '#ffcccc');
				$("updateMsg").css("color", "#000000");
				$("#updateMsg").html("An Error Occurred Retrieving the Requested Information");
			}
		});

	}); //End vacSelectList.change()

	$('#btnUpdatePriceCost').click(function(){

		//Display AJAX Preloader
		$("#AJAXPreloader").css("display", "block");


		//Get the values in the price fields
		var name = $("#vacName").val();
		var cost = $("#drugCost").val();
		var trvlPrice = $("#travelPrice").val();
		var refugeePrice = $("#refugeePrice").val();

		//Clear any value in #updateMsg element
		$("#updateMsg").empty();
		$("#updateMsg").css("display", "block");

		//Send ajax request
		$.ajax({
			url: "<?php echo site_url('Inventory/ChangePriceCost'); ?>",
			method: "POST",
			data: {'selectedVac': name, 'selectedDrugCost': cost, "selectedTrvlPrice": trvlPrice, "selectedRefugeePrice": refugeePrice},
			dataType: "JSON",
			success: function(vacResult){
				console.log("Success");

				//Hide AJAX Preloader
				$("#AJAXPreloader").css("display", "none");

				//Display msg to user
				$("#updateMsg").css("display", "block");
				$("#updateMsg").css("background-color", '#66a3ff');
				$("#updateMsg").css("color", "white");
				$("#updateMsg").html("Update Successful!");


			},
			error: function(errorMsg){
				console.log("An error occurred");

				//Hide AJAX Preloader
				$("#AJAXPreloader").css("display", "none");

				//Display msg to user
				$("#updateMsg").css("display", "block");
				$("#updateMsg").css("background-color", '#ffcccc');
				$("updateMsg").css("color", "#000000");
				$("#updateMsg").html("Update Failed!");
			}
		});

		//If ajax request successful, display message


	}); //End btnUpdatePriceCost.click


	// $(window).ready(function(){
	// 	//Disable search buttons
	// 	$("#btnBarcodeSearch").prop("disabled", true);
	// 	$("#btnNameSearch").prop("disabled", true);

	// 	//Disable results fields
	// 	$("#vacSelectList").prop("disabled", true);
	// 	$("#drugCost").prop("disabled", true);
	// 	$("#travelPrice").prop("disabled", true);
	// 	$("#refugeePrice").prop("disabled", true);
	// 	$("#btnUpdatePriceCost").prop("disabled", true);


	// }); // End document.onReady()

	function EnableSearchResultControls()
	{
		$("#vacSelectList").prop("disabled", false);
		$("#drugCost").prop("disabled", false);
		$("#travelPrice").prop("disabled", false);
		$("#refugeePrice").prop("disabled", false);

	} //End EnableSearch()

	function EnableSearchControls()
	{
		$("#btnBarcodeSearch").prop("disabled", false);
		$("#btnNameSearch").prop("disabled", false);

	}

	function ClearResultControls(){
		$("#vacName").val("");
		$("#drugCost").val("");
		$("#travelPrice").val("");
		$("#refugeePrice").val("");
	}

	function DisableResultControls(){
		$("#drugCost").prop("disabled", true);
		$("#travelPrice").prop("disabled", true);
		$("#refugeePrice").prop("disabled", true);
	}

	
</script>

<!-- Body closed in footer