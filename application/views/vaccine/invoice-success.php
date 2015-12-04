<!--Body opened in header-->

	<h1>Invoice Transaction Summary</h1>
	<br/>
	
	<p>Invoice Added Successfully!</p>

	<h2>Transaction Summary</h2>
	<?php
		echo $tblSummary;
		echo "<br/><br/>";
		echo anchor('Inventory/ScanInvoice', 'Add Another Vaccine', array('id' => 'ScanInvoice'));
	?>

<!--//Body closed in footer-->


