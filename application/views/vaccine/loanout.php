<!--Body opened in header file-->

	<h1>Vaccine Loan Out Form</h1>
	<p>Please Add Information For Intrahospital Vaccine Loan Outs to the Form Below:</p>

	<?php
		echo validation_errors();
		echo form_open("Inventory/LoanOut");
	?>

		<label for="linBarcode">Barcode</label>
		<input id="linBarcode" type="text" name="linBarcode"><br/>
		
		<label for="expireDate">Expiration Date</label>
		<input id="expireDate" type="date" name="expireDate"><br/>
		
		<label for="lotNum">Lot Number</label>
		<input id="lotNum" type="text" name="lotNum"><br/>

		<label for="packageQty">Number of Packages</label>
		<input id="packageQty" type="number" name="packageQty"></br/>

		<label for="dosesPerPackage">Doses Per Package</label>
		<input id="dosesPerPackage" type="number" name="dosesPerPackage"><br/>
		
		<label for="borrowerID">Borrowing Department</label>
		<select id="borrowerID" name="borrowerID">
			<option value='-1' selected>&lt;Select Department&gt;</option>
			
			<?php
				foreach($listOfBorrowers as $aBorrower)
				{
					echo "<option value ='".$aBorrower->BorrowerID."'>";
					echo $aBorrower->EntityName;
					echo "</option>";
				}
			?>

		</select><br/>
		<input type="submit" name="Add">
	</form>

<!--Body closed in footer file-->