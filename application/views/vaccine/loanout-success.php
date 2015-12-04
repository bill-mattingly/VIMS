<!--
//<!Doctype html>
//<html>
//<head>
//	<title></title>
//</head>

//<body>
-->

	<p>Vaccine loan was successful</p>

	<br/>
	<p>Transaction Summary:</p>

	<?php
		//echo //$AdminTrans;
		echo "<br/><br/>";
		//echo "Here is the borrowerid: $borrowerID<br/>";

		echo $transSummary;

		echo "<br/><br/>";		
		echo anchor("Inventory/LoanOut", "Loan Another Vaccine");
	?>

<!--</body>
</html>-->
