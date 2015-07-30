

<!-- <!Doctype html>
// <html>
// <head>
// 	<title></title>
// </head>

// <body> -->
		
		<h1>Vaccine Order Form</h1>
		<p>Please Add Vaccines To Clinic Inventory By Filling Out The Below Form:</p>

		<?php
			echo validation_errors();
			echo form_open('Inventory/Order');
		?>

		<label for="linBarcode">Barcode:</label>
		<input id="linBarcode" type="text" name="linBarcode"><br/>
		
		<label for="expireDate">Expiration Date:</label>
		<input id="expireDate" type="date" name="expireDate"><br/>
		
		<label for="lotNum">Lot Number:</label>
		<input id="lotNum" type="text" name="lotNum"><br/>

		<label for="clinicCost">Cost Per Dose:</label>
		<input id="clinicCost" type="text" name="clinicCost"><br/>

		<label for="packageQty">Total # of Packages:</label>
		<input id="packageQty" type="text" name="packageQty"><br/>

		<label for="dosesPerPackage">Doses Per Package</label>
		<input id="dosesPerPackage" type="text" name="dosesPerPackage"><br/>
		
		<input type="submit" name="Add" value="Add">
	</form>

<!--Body tag closed in the footer page-->

<!--	<script>
		/*function store()
		{
			sessionStorage.setItem('linBarcode', document.getElementById('linBarcode').value);
			sessionStorage.setItem('expireDate', document.getElementById('expireDate').value);
			sessionStorage.setItem('lotNum', document.getElementById('lotNum').value);
			sessionStorage.setItem('clinicCost', document.getElementById('clinicCost').value);
			sessionStorage.setItem('packageQty', document.getElementById('packageQty').value);
			sessionStorage.setItem('dosesPerPackage', document.getElementById('dosesPerPackage').value);
		}*/
	</script>

</body>
</html> -->
