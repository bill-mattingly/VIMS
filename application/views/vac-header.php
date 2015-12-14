<!Doctype html>
<html>
	<head>
		<title>Vaccine Clinic</title>

		<!-- jQuery CDN (load first)-->
		<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
		<!--<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>-->

		<!-- Latest compiled and minified CSS -->
<!-- 		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

		Optional theme
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css"> -->
		<link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.5/paper/bootstrap.min.css" rel="stylesheet" integrity="sha256-hMIwZV8FylgKjXnmRI2YY0HLnozYr7Cuo1JvRtzmPWs= sha512-k+wW4K+gHODPy/0gaAMUNmCItIunOZ+PeLW7iZwkDZH/wMaTrSJTt7zK6TGy6p+rnDBghAxdvu1LX2Ohg0ypDw==" crossorigin="anonymous">

		<!--Custom CSS -->
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/custom.css">

		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

		
	<!--
		<script>
		// 	$(document).ready(function(){
		// 		$("*").click(function(event){
		// 			alert(event.target.id);
		// 		});
		// 	});
		</script>
	-->
		<script>

			$(document).ready(function(){

				if(typeof FillTransTable === "function") //Check to see if "FillTransTable" function exists in the loaded page. If it does, then run the function, otherwise do nothing
				{
					FillTransTable("all");
				}

				//DisplayForm is a function from the manage-users.php view
				if(typeof DisplayForm === "function") //Check to see if "DisplayForms" function exists in loaded page. If so, run it else do nothing.
				{
					DisplayForm('register'); //Display the register form by default
					//var theForm = "<?php echo $this->session->UserForm; ?>";
					//console.log("Here is the form session variable: " + theForm);
					//DisplayForm(theForm);

				}

				//DisplayOutstandingLoans is a function from the loanreimbursement.php view
				if(typeof DisplayOutstandingLoans === "function")
				{
					DisplayOutstandingLoans(); //Populates the page with outstanding vaccine loans
				}

				//Gets id value of the anchor tag which was clicked
				$("a").click(function(e){
					window.sessionStorage.setItem("tabId", e.target.id);
				});

				//This function is tied to "a.click", however it doesn't correctly set the active navigation tab
				//unless it is outside the a.click function
				SetActiveTab();

				/*----------------*/

				//Set the Expiration Date field (in the Administer & LoanOut pages) based on the selected Lot Number
				$("#lotNumList").change(function(){

					var selectList = document.getElementById('lotNumList');
					var option = selectList.options[selectList.selectedIndex];
					var date = option.getAttribute('data-expireDate');

					$("#expireDate").val(date);

				}); //End $('#lotNumList').change


			});

			//Sets which tab is active in the navigation header 
			function SetActiveTab()
			{
				//variables
				var tab = window.sessionStorage.getItem('tabId');
				tab = $("#" + tab); //Assign DOM element to tab variable
				//alert("Tab id: " + tab.id);

				//Set active tab
				if(tab != null && tab != undefined){
					//Add 'active' class to selected tab
					tab = $(tab).parent(); //Get anchor element's parent "li" element

					//alert("This is the id:" + tab.id);

					$(tab).addClass('active');

					//Remove 'active' class from any other tabs
					$(tab).siblings().removeClass('active');

					var selectedAction = window.sessionStorage.getItem('tabId');

					//alert("This is the selectedAction: " + selectedAction);

					//AJAX request to pass selected action to controller method
					$.ajax(
					{
						url: "<?php echo site_url('Inventory/ScanBarcodeAction'); ?>",
						method: "POST",
						data: {"action":selectedAction},
						success: function(result)
						{
												
							//alert("This is the result: " + result);

							console.log(result);

							//What's happening is that the page is loading before the php session variable
							//gets reset with the newly selected tab's value by the AJAX request
							//Thus, the old session variable's value is used to load the page, and yet
							//when the result comes back from the AJAX request, the result comes back with the correct value
							//Thus, what needs to be done is for the page values to be modified after the AJAX result comes back.

							//So b/c the link & ajax requests are embedded in the Document.Load function,
							// the page load first, pulls the old session value, & then the ajax request runs & updates the php session variable

							//Check if form action matches up with the result's action

							//store form action in variable
							var formURL = $("#scanbc-form").attr('action');
							//console.log("Here's the form's action: " + formURL);
							
							var currentURL = document.location.href; 
							

							//console.log("Here's the ajax url: " + currentURL);

							if(formURL === currentURL)
							{
								//Do nothing
							}
							else
							{
								//Change formAction to currentURL
						//		document.getElementById('scanbc-form').action = currentURL;
						//		console.log("here's the non-jquery formurl: " + document.getElementById('scanbc-form').action);

								//Change formAction to ajaxResult
								$("#scanbc-form").attr('action', function(){
									return currentURL;
								});
								//var newURL = $("#scanbc-form").attr('action');


								//console.log("Here is the formURL after the change: " + newURL);
							}


						},
						failure: function(result)
						{
							console.log("An error occurred");
						}
					});

					//Set session variable = null
					window.sessionStorage.setItem('id', null);
				}
				else if(tab.id == 'home') //Clear 'active' class from all tabs if the 'home' tab is selected
				{
					tab = $(tab).parent();
					$(tab).siblings().removeClass('active');

					window.sessionStorage.setItem('id', null);
				}
				
			} //End SetActiveTab


		</script>

	</head>

	<body>

		<div class="container-fluid">

		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#"></a> <!--CTRSU brand image should go within this a tag-->
				</div>
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav navbar-right">
						

						<?php
							//Display Log Out link if user is logged in
							if($this->ion_auth->logged_in()):
						?>
								<li>
									<?php echo anchor("Inventory/Index", "Home", array('id'=>'tabHome')); ?>
									<!--<span class="sr-only">()-->
								</li>

								<li>
									<?php echo anchor("Inventory/ScanInvoice", "Add to Inventory", array('id' => 'ScanInvoice')); ?>
								</li>
								<li>
									<?php echo anchor("Inventory/ScanAdminister", "Administer Vaccine", array('id'=>'ScanAdminister', 'data-action' => 'administer', 'data-navlink' => 'administer', 'class' => 'navlink')); ?>
								</li>
								<li>
									<?php echo anchor("Inventory/ScanLoanOut", "Loan Out Vaccine", array('id'=>'ScanLoanOut', 'data-action' => 'administer', 'data-navlink' => 'loanout', 'class' => 'navlink')); ?>						
								</li>
														
								<li class='dropdown'>
									<a class='dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'>
										Administrative<span class='caret'></span>
									</a>
									<ul class='dropdown-menu'>
										<li>
											<?php echo anchor('Inventory/Reports', 'Reports', array('data-navlink' => 'reports', 'class' => 'navlink')); ?>
										</li>
										<li>
											<?php echo anchor("Inventory/LoanReturn", "Loan Reimbursement"); //, array('id'=>'ScanLoanReturn', 'data-navlink' => 'loanreturn', 'class' => 'navlink')); ?>
										</li>
										<li>
											<?php echo anchor('Inventory/EditTransactions', 'Modify Transaction'); ?>
										</li>
										<li>
											<?php echo anchor('Inventory/UpdatePriceAndCost', 'Change Prices', array('data-navlink' => 'priceandcost', 'class' => 'navlink')); ?>											
										</li>
										<li>
											<?php echo anchor('Inventory/ManageUsers', 'Manage Users'); ?>
										</li>
									</ul>
								</li>

								<li>
									<?php echo anchor('Auth/Logout', 'Logout'); ?>
								</li>

						<?php
							else:
						?>

							<li>
								<?php echo anchor('Auth/Login', 'Login'); ?>
							</li>

						<?php
							endif;
						?>
						
						

	<!--Original Code-->
<!-- 								//echo "<li class='nav' data-toggle='tab'>";
								

//								echo "<li class='".($this->uri->segment(2)== 'Add' ? 'active' : '')."'>";
								?>

								<li data-toggle='tab'>

								<?php 
							//		echo anchor("Inventory/ScanBarcode", "Add To Inventory"); //, array('id' => 'invoice', 'data-navlink' => 'invoice', 'class' => 'navlink'));
									//echo "<a src='".base_url()."Inventory/ScanBarcode' id='invoice'>Add To Inventory</a>";
								
								//echo "</li>";
								?>
								</li>

								<?php
							/*
								echo "<li>";
									echo anchor("Inventory/ScanBarcode", "Administer Vaccine", array('id' => 'administer', 'data-navlink' => 'administer', 'class' => 'navlink'));
								echo "</li>";

								echo "<li>";
									echo anchor("Inventory/ScanBarcode", "Loan Out Vaccine", array('id' => 'loanout', 'data-navlink' => 'loanout', 'class' => 'navlink'));
								echo "</li>";

								echo "<li>";
									echo anchor("Inventory/ScanBarcode", "Loan Return", array('id' => 'loanreturn', 'data-navlink' => 'loanreturn', 'class' => 'navlink'));
								echo "</li>";

								echo "<li class='dropdown'>";
									echo "<a class='dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'>
										Administrative<span class='caret'></span>
									</a>";
									echo "<ul class='dropdown-menu'>";
										echo "<li>";
											echo anchor('Inventory/Reports', 'Reports', array('data-navlink' => 'reports', 'class' => 'navlink'));
										echo "</li>";
										echo "<li>";
											echo anchor('Inventory/UpdatePriceAndCost', 'Update Prices and Costs', array('data-navlink' => 'priceandcost', 'class' => 'navlink'));
										echo "</li>";
									echo "</ul>";
								echo "</li>";
							
								echo "<li>";
								echo anchor('Auth/Logout', 'Log Out');
								echo "</li>";
							*/
							
					//	?>
					<! -->

					</ul>
				</div> <!-- /End .collapse navbar-collapse -->
			</div> <!-- /End .container-fluid -->
		</nav> <!-- /End .navbar navbar-default -->