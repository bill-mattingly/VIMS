<!Doctype html>
<html>
	<head>
		<title>Vaccine Clinic</title>
	</head>

	<body>
		<!--<p>Header File</p>-->

		<p>Options:</p>
		<ul>
			<li><?php echo anchor("Inventory/Index", "Home"); ?></li>
			
			<li><?php echo anchor("Inventory/Order", "Add To Inventory"); ?></li>
			
			<li><?php echo anchor("Inventory/Administer", "Administer Vaccine"); ?></li>
			
			<li><?php echo anchor("Inventory/LoanOut", "Loan Out Vaccine"); ?></li>

			<li><?php echo anchor("Inventory/LoanReturn", "Loan Return"); ?></li>
		</ul>

		<br/>
