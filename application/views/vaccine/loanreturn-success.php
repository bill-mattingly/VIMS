<!--
<!Doctype html>
<html>
<head>
	<title></title>
</head>

<body>
-->
	<h1>Vaccine Loan Return Summary</h1>
	<p>Loan Returned Successfully</p>

	<?php
		echo "Here is a summary of the most recent transaction:<br/>";
		echo $transSummary;

		echo anchor('Inventory/LoanReturn', 'Return Another Loan');
	?>

<!--
</body>
</html>
-->