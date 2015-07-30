<!--Body opened in header-->

	<p>Vaccine was Administered Successfully</p>

	<br/>
	<p>Transaction Summary:</p>

	<?php
		echo $AdminTrans;
		echo "<br/><br/>";
		
		// echo $InventorySum;

		echo "<br/><br/>";		
		echo anchor("Inventory/Administer", "Administer Another Vaccine");
	?>

<!--Body closed in footer-->
