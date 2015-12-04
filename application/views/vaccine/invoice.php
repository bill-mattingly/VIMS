<!--Body opened in header-->
		
	<div class="row">

			<h1 id="invoice-heading">Vaccine Order Form</h1>
			<p id="invoice-text">Please Add Vaccines To Clinic Inventory By Filling Out The Below Form:</p>

			<?php
				echo validation_errors();

				$attributes = array('id' => 'invoice-form');
				echo form_open('Inventory/Invoice', $attributes);
		
				//Begin form fields
				echo "<div class='form-group'>";
					echo "<label for='ndc10' class='actionform'>NDC 10:</label>";
					echo "<input id='ndc10' type='text' name='ndc10' readonly value=".$ndc10."><br/>";
				echo "</div>"; //End .form-group

			    // echo "<div class='form-group'>";
				// 	echo "<label for='ndc11' class='actionform'>HIPAA NDC 11:</label>";
				// 	echo "<input id='ndc11' type='text' name='ndc11' readonly value=".$ndc11."><br/>";
				// echo "</div>"; //End .form-group

				echo "<div class='form-group'>";
					echo "<label for='lotNum' class='actionform'>Lot Number:</label>";
					echo "<input id='lotNum' type='text' name='lotNum' value=".$lotNum."><br/>";
				echo "</div>"; //End .form-group

				echo "<div class='form-group'>";
					echo "<label for='datepicker' class='actionform'>Expiration Date (mm/dd/yyyy):</label>";
					echo "<input id='datepicker' type='text' name='expireDate' value=".$expireDate."><br/>";
				echo "</div>"; //End .form-group

				echo "<div class='form-group'>";
					echo "<label for='clinicCost' class='actionform'>Cost Per Dose:</label>";
					echo "<input id='clinicCost' type='text' name='clinicCost' value=".$clinicCost."><br/>";
				echo "</div>"; //End .form-group

			?>

			<div class="form-group">
				<label id="packageQtyError"></label> <!--Provides error message if validation for packageQty field fails (i.e. if packageQty field <= 0) -->
				<label for="packageQty" class='actionform'>Total # of Packages:</label>
				<input id="packageQty" type="number" name="packageQty" min='1'><br/>
			</div> <!-- /End .form-group -->

			<div class="form-group">
				<label for="dosesPerPackage" class='actionform'>Doses Per Package</label>
				<?php
					echo "<input id='dosesPerPackage' type='number' name='dosesPerPackage' value=".$numDosesPackage." readonly><br/>"
				?>
			</div> <!-- /End .form-group -->

			<div class="form-group">
				<input type="submit" name="Add" value="Add">
			</div> <!-- /End .form-group -->

		</form>

		<!--Cancel button to get the user out of the invoice process-->
		<button type="button"><?php echo anchor("Inventory/Index", "Cancel"); ?></button>

	</div> <!-- /End .row -->


<!--	
	 <script type="text/javascript" src="<?php //echo base_url()?>js/script.js">
	 </script>
-->

		<script>
			$(document).ready(function(){
				$("#packageQtyError").html(''); //Clear packageQtyError message
				$("#packageQtyError").css('display', 'hidden');

				//Validate package quantity
				$("#packageQty").keyup(function(){
					var invoiceQty = Number($(this).val());

					if(invoiceQty == NaN)
					{
						//Display & set error message
						$("#packageQtyError").css('display', 'block');
						$("#packageQtyError").html("Enter a Number");

						//Clear value in packageQty textbox
						$("#packageQty").val('');
					}
					else if(invoiceQty <= 0)
					{
						var minQty = $("#packageQty").attr('min');

						//Display & set error message in packageQtyError label element
						$("#packageQtyError").css('display', 'block');

						if(minQty == 1)
						{
							$("#packageQtyError").html("Cannot Add Less than 1 Package");
							
						}
						else
						{
							$("#packageQtyError").html("Cannot Add Less than " + minQty + " Packages");
						}

						//Set packageQty field = to the minimum quantity (as specified in the <input> element)
						$("#packageQty").val(minQty);

					}
					else
					{
						//Hide & clear error message
						$("#packageQtyError").css('display', 'hidden');
						$("#packageQtyError").html('');
					}
				});
			});

		</script>

<!--Body tag closed in the footer page-->