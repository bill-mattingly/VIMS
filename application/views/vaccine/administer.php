<!--Body opened in header file-->

	<h1>Administer Vaccine</h1>
	<p>Please Enter Vaccine Data Below:</p>

		<?php echo validation_errors(); ?>
		<?php echo form_open("Inventory/Administer"); ?>

		<label for="linBarcode">Barcode:</label>
		<input id="linBarcode" type="text" name="linBarcode"><br/>
		
		<label for="expireDate">Expiration Date:</label>
		<input id="expireDate" type="date" name="expireDate"><br/>
		
		<label for="lotNum">Lot Number:</label>
		<input id="lotNum" type="text" name="lotNum"><br/>

		<label for="customerChrg">Patient Charge:</label>
		<input id="customerChrg" type="text" name="customerChrg"><br/>

		<label for="doseQty">Doses Administered:</label>
		<input id="doseQty" type="number" name="doseQty"><br/>

		<input type="submit" name="Add">

	</form> <!--Close form-->

<!--Body closed in footer file-->