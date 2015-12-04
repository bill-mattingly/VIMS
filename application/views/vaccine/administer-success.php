<!--Body opened in header-->

	<h1>Invoice Transaction Summary</h1>
	<br/>
	
	<p>Vaccine Administered!</p>

	<h2>Transaction Summary</h2>
	<?php
		echo $tblSummary;
		echo "<br/><br/>";
		
		echo anchor("Inventory/ScanAdminister", "Administer Another Vaccine", array('id' => 'ScanAdminister'));
	?>

	

<!--Body closed in footer-->
