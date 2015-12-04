<!-- Body opened in header -->

	<h1>Vaccine Loan Return Summary</h1>
	<br/>

	<p>Loan Return Successful</p>

	<h2>Transaction Summary:</h2>
	<?php
		echo $tblSummary;
		echo "<br/><br/>";

		echo anchor('Inventory/ScanLoanReturn', 'Return Another Loan');
	?>

<!-- Body closed in footer -->