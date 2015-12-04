<!-- Body opened in header -->
	
	<h1>Vaccine Loan Summary</h1>
	<br/>

	<p>Loan Successful!</p>

	<h2>Transaction Summary</h2>
	<?php
		echo $tblSummary;
		echo "<br/><br/>";

		echo anchor("Inventory/ScanLoanOut", "Create New Loan", array('id' => 'ScanLoanOut'));
	?>

<!-- Body closed in footer -->
