<!--
<!Doctype html>
<html>
<head>
	<title></title>
</head>

<body>
-->

	<h1>Vaccine Loan Return Form</h1>
	<p>Please Add Information For Intrahospital Vaccine Loan Returns to the Form Below:</p>

	<?php
		echo validation_errors();
		echo form_open('Inventory/LoanReturn');
	?>

		<label for="linBarcode">Barcode</label>
		<input id="linBarcode" type="text" name="linBarcode"><br/>
		
		<p>

		<label for="borrowerID">Returning Department</label>
		<select id="borrowerID" name="borrowerID">
			<option value='-1' selected>&lt;Select Department&gt;</option>

			<?php
				foreach($borrowerList as $borrower)
				{
					echo "<option value='".$borrower->BorrowerID."'>";
					echo $borrower->EntityName;
					echo "</option>";
				}
			?>
		</select><br/>

		<p>Vaccine Return Data</p>
		<label for="expireDate">Expiration Date</label>
		<input id="expireDate" type="date" name="expireDate"><br/>
		
		<label for="lotNum">Lot Number</label>
		<input id="lotNum" type="text" name="lotNum"><br/>

		<label for="packageQty">Number of Packages Returned</label>
		<input id="packageQty" type="number" name="packageQty"><br/>

		<label for="dosesPerPackage">Doses Per Package</label>
		<input id="dosesPerPackage" type="number" name="dosesPerPackage"><br/>
		
		<input type="submit" name="Add">
	</form>

<!--
</body>
</html>
-->