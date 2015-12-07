
<div class='row col-md-12'>
	<h1>Manage Users</h1>
	<p id='userMsg'></p>

	<div>
		<!--Selection determines what the form is used for-->
		<label for='manageRdoRegister'>Register User:</label>
		<input id='manageRdoRegister' type='radio' value='register' name='formType' checked='true'>

		<label for='manageRdoManage'>Manage Users:</label>
		<input id='manageRdoManage' type='radio' value='manage' name='formType'>
	</div>


	<!--Create opening form tag for Register User form-->
	<?php
//		$attributes = array('id' => 'registerUserForm');

//		echo form_open('Inventory/ManageUsers', $attributes); //First argument is the url for the 'action' attribute in the form tag. 2nd argument is an array containing attributes for the form tag.
	?>

	<div id='registerUserForm'>
		<div id='registerFormControls'>
			<div class='form-group'>
				<label for='registerFName'>First Name:</label>
				<input id='registerFName' type="text" name='registerFName'><br/>

				<label for='registerLName'>Last Name:</label>
				<input id='registerLName' type='text' name='registerLName'><br/>

				<label for='registerRole'>Role:</label>
				<select id='registerRole' name='registerUserRole' required disabled>
				</select><br/> <!--Populate dynamically with AJAX-->
			</div> <!-- /end .form-group -->

			<div class='form-group'>
				<label for='registerUsername'>Username:</label>
				<input id='registerUsername' type='text' name='registerUsername'><br/>

				<label for='registerEmail'>Email:</label>
				<input id='registerEmail' type='email' name='registerEmail'><br/>

				<label for='registerPassword'>Password:</label>
				<input id='registerPassword' type='password' name='registerPassword' placeholder='&lpar;6 character minimum&rpar;'><br/>
			</div> <!-- /end .form-group -->
		</div> <!-- /End #registerFormControls -->
		<input id='btnRegisterUser' type='button' value='Register' name='btnRegisterUser'>

<!--	</form>  /#registerUserForm -->
	</div> <!-- /end #registerUserForm -->


	<?php
//		$attributes = array('id' => 'manageUserForm');

//		echo form_open('Inventory/ManageUsers', $attributes);
	?>

	<div id='manageUserForm'>
		<div id="manageFormControls">
			<select id='manageUserList'>
			</select> <!-- /End #userList -->

			<div class="form-group">
				<label id='manageUsername_label' class='manageUserControl' for='manageUsername'>Username:</label>
				<input id='manageUsername' class='manageUserControl' type='text' name='manageUsername' readonly><br/>

				<label id='manageEmail_label' class='manageUserControl' for='manageEmail'>Email:</label>
				<input id="manageEmail" class='manageUserControl' type="text" name='manageEmail'><br/>

	<!-- (Add later)
				<button id='btnResetPass'>Reset Password</button><br/>
	-->

			<!--
				<label id='managePassword_label' class='manageUserControl' for='managePassword'>Change Password:</label>
				<input id='managePassword' class='manageUserControl' type='password' name='managePassword'><br/>
			-->

			</div> <!-- /End .form-group -->

			<div class='form-group'>
				<label id='manageFName_label' class='manageUserControl' for='manageFName'>First Name:</label>
				<input id='manageFName' class='manageUserControl' type='text' name='manageFName'><br/>

				<label id='manageLName_label' class='manageUserControl' for='manageLName'>Last Name:</label>
				<input id='manageLName' class='manageUserControl' type='text' name='manageLName'><br/>
			</div> <!-- /End .form-group -->
		</div> <!-- /End #manageFormControls -->

		<!--Form submit buttons-->
		<input id='btnUpdateUser' type='button' value='Update' name='btnUpdateUser'>
		<button id='btnDeleteUser'>Delete</button>


<!--	</form> /end #manageUserForm -->
	</div> <!-- /end #manageUserForm -->


	<!-- Full screen image to display for AJAX requests -->
	<div id="AJAXPreloader"></div>

</div> <!-- /end .row col-md-12 -->

<script>
//Displays the correct user form based on the selected radio button
$("input[type='radio']").change(function(){
	var formType = $(this).val();

	DisplayForm(formType);

});

//Changes the displayed controls based on whether or not a valid user is selected
$("#manageUserList").change(function()
{
	var selectedUserID = $(this).val();

	if(selectedUserID != -1) //If valid user is selected (i.e. default option (which == -1) isn't selected), then display controls
	{
		//Display the controls which will contain a specific user's information
		//$('.manageUserControl').css('display', 'inline');
		$('.form-group').css('display', 'block');

		$("#btnUpdateUser").css('display', 'inline-block');
		$("#btnDeleteUser").css('display', 'inline-block');


		//Call method to get info for selected user. This method will populate form controls
		GetSpecificUser(selectedUserID);

	} //End if
	else
	{
		//Clear control values for controls related to specific user (username, password, first/last name)
		$('#manageUsername').val('');
		$('#manageEmail').val('');
		$('#manageFName').val('');
		$('#manageLName').val('');

		//Hide controls
		$('.form-group').css('display', 'none'); //Hide form fields
		$('#btnUpdateUser').css('display', 'none'); //Hide update button
		$('#btnDeleteUser').css('display', 'none'); //Hide delete button

	} //End else

}); //End #manageUserList.change()


$("#btnRegisterUser").click(function(){

	//Validate information is in form fields
	var fname = $("#registerFName").val();
	var lname = $("#registerLName").val();
	var role = $("#registerRole").val();

	var username = $("#registerUsername").val();
	var email = $("#registerEmail").val();
	var password = $("#registerPassword").val();



	//AJAX request
	$.ajax({
		url: "<?php echo site_url('Inventory/RegisterUser'); ?>",
		method: "POST",
		data: {'Username': username, 'Password': password, 'Email': email, 'Role': role, 'FName': fname, 'LName': lname},
		dataType: "JSON",
		success: function(registerResult){
			//User Feedback
			//Assign userMsg element with message
			var msg = "'" + registerResult.fname + " " + registerResult.lname + "' was registered successfully";
			$("#userMsg").html(msg);

			//Display userMsg element
			$("#userMsg").css('display', 'block');
			$("#userMsg").css('margin-left', '341.5px');
			$("#userMsg").css('margin-right', '341.5px');

			//Change background color & text color
			$("#userMsg").css('background-color', '#99ccff');
			$("#userMsg").css('color', 'black');

			//Clear registration form controls
			$("#registerFName").val('');
			$("#registerLName").val('');
			$("#registerRole").val(-1); //Reset "role" to default selection

			$("#registerUsername").val('');
			$("#registerEmail").val('');
			$("#registerPassword").val('');

			//Set focus to registerFName field (to for registration of next user)
			$("#registerFName").focus();

			//Reset sessionStorage variable (controls the .focusout() method for #registerUsername & #registerEmail elements)
			sessionStorage.setItem('usernameOK', 'FALSE');


		}, //End success()
		error: function(errorResult){
			//User feedback
			var msg = "Registration unsuccessful";
			$("#userMsg").html(msg);

			//Display userMsg element
			$("#userMsg").css('display', 'block');
			$("#userMsg").css('margin-left', '341.5px');
			$("#userMsg").css('margin-right', '341.5px');

			//Change background color & text color
			$("#userMsg").css('background-color', '#f76e6e');
			$("#userMsg").css('color', 'black');

			//Reset session variable to 'FALSE'
			sessionStorage.setItem('usernameOK', 'FALSE');

		} //End error()
	}); //End $.ajax()
}); //End #btnRegisterUser.click()


//Check if email == existing email (if so, exit. If not, check to make sure email != any other email)
$("#manageEmail").focusout(function(){
	var userid = $("#manageUserList").val();
	var email = $("#manageEmail").val();

	$.ajax({
		url: "<?php echo site_url('Inventory/UpdateUser'); ?>",
		method: "POST",
		data: {'UserID': userid, 'Email': email},
		dataType: "JSON",
		success: function(emailResult){
			
		}, //End success
		error: function(errorResult){

		} //End error

	}); //End $.ajax()

}); //End #manageEmail.focusout()


$("#btnUpdateUser").click(function(){
	alert("hi");



}); //End #btnUpdateUser.click()

$("#btnDeleteUser").click(function(){
	alert('hi');

}); //End #btnDeleteUser.click()

//Check to make sure username doesn't exist already in system
$("#registerUsername").focusout(function(){
	//alert("Lost focus");
	var username = $("#registerUsername").val();
	username = username.trim();

	if(sessionStorage.getItem('usernameOK') == 'TRUE')
	{
		return;
	}

//	$("#registerUsername").focus(); //Prevents focus from leaving #registerUsername 

	if(username.length < 1) //Checks if username field is empty
	{
		DisplayErrorMsg();
		var msg = "Username cannot be blank. Please add a username";
		$("#userMsg").html(msg);
		$("#registerUsername").focus();

	} //End if
	else
	{
		$.ajax({
			url: "<?php echo site_url('Inventory/CheckUsername'); ?>",
			method: 'POST',
			data: {'Username': username},
			dataType: "JSON",
			success: function(usernameResult){

				if(usernameResult.result) //If result == true, then username exists
				{
					//Clear registerUsername field (b/c username already exists in system)
					$("#registerUsername").val('');
					$("#registerUsername").focus();

					//Display userMsg element
					DisplayErrorMsg();
	//				$("#userMsg").css('display', 'block');
	//				$("#userMsg").css('margin-left', '341.5px');
	//				$("#userMsg").css('margin-right', '341.5px');

					//Change background color & text color
	//				$("#userMsg").css('background-color', '#f76e6e');
	//				$("#userMsg").css('color', 'black');

					//userMsg message
					var msg = "'" + usernameResult.username + "' already exists. Please use a different name";
					$("#userMsg").html(msg);

					$("#registerUsername").focus(); //Set focus on registerUsername control

				} //End if
				else
				{
					var msg = usernameResult.username + " is available";
					$("#userMsg").html(msg);

					//Display userMsg element
					DisplaySuccessMsg();
	//				$("#userMsg").css('display', 'block');
	//				$("#userMsg").css('margin-left', '341.5px');
	//				$("#userMsg").css('margin-right', '341.5px');

					//Change background color & text color
	//				$("#userMsg").css('background-color', '#99ccff');
	//				$("#userMsg").css('color', 'black');

					sessionStorage.setItem('usernameOK', 'TRUE'); //Change value of usernameOK (controls the '[#element].focusout()' event for the registerUsername & registerEmail elements)
					$('#registerEmail').focus(); //Set focus on email element

				} //End else



			}, //End success()
			error: function(errorResult){
				console.log("CheckUsername error");

			} //End error()
		}); //End .ajax()
	} //End else

	//Initiate session variable
	sessionStorage.setItem('usernameOK', 'FALSE'); //Default is username is not valid (aka "FALSE");

}); //End #registerUsername.focusout()

//Check to make sure username doesn't exist already in system
$("#registerEmail").focusout(function(){
	//alert("Lost focus");

	if(sessionStorage.getItem('usernameOK') == 'FALSE')
	{
		return;
	}

	var email = $("#registerEmail").val();
	email = email.trim();

	if(email.length < 1)
	{
		$("#userMsg").html("Email cannot be blank. Please add an email");

		DisplayErrorMsg();

		$("#registerEmail").focus();
	} //End if
	else
	{
		$.ajax({
			url: "<?php echo site_url('Inventory/CheckEmail'); ?>",
			method: 'POST',
			data: {'Email': email},
			dataType: "JSON",
			success: function(emailResult){

				if(emailResult.emailExists) //If result == true, then username exists
				{
					//Clear registerUsername field (b/c username already exists in system)
					$("#registerEmail").val('');
					$("#registerEmail").focus();

					//Display userMsg element
					DisplayErrorMsg();

//					$("#userMsg").css('display', 'block');
//					$("#userMsg").css('margin-left', '341.5px');
//					$("#userMsg").css('margin-right', '341.5px');

					//Change background color & text color
//					$("#userMsg").css('background-color', '#f76e6e');
//					$("#userMsg").css('color', 'black');

					//userMsg message
					var msg = "'" + emailResult.email + "' already exists. Please use a different email";
					$("#userMsg").html(msg);

				//	$("#registerEmail").focus();

				} //End if
				else
				{
					var msg = emailResult.email + " is available";
					$("#userMsg").html(msg);

					//Display userMsg element
					DisplaySuccessMsg();
//					$("#userMsg").css('display', 'block');
//					$("#userMsg").css('margin-left', '341.5px');
//					$("#userMsg").css('margin-right', '341.5px');

					//Change background color & text color
//					$("#userMsg").css('background-color', '#99ccff');
//					$("#userMsg").css('color', 'black');

				} //End else

			}, //End success()
			error: function(errorResult){
				console.log("CheckEmail error");

			} //End error()
		}); //End .ajax()
	} //End else

}); //End #registerEmail.focusout()

//Check Password Length
$("#registerPassword").focusout(function(){

	var password = $("#registerPassword").val();
	password = password.trim();

	$.ajax({
		url: "<?php echo site_url('Inventory/CheckPasswordLength'); ?>",
		method: "POST",
		data: {"Password": password},
		dataType: "JSON",
		success: function(passResult){ //Don't care if password is minimum length or above, only if below min length

			if(passResult.lengthOK == false)
			{
				DisplayErrorMsg();
				$("#userMsg").html("Password must be a minimum of " + passResult.minLength + " characters");

				//Password needs to be [x] characters long
				//var msg passResult.minLength;

				//Change placeholder text to whatever the minimum is
				var placeholderTxt = "(" + passResult.minLength + " character minimum)";
				$("#registerPassword").prop('placeholder', placeholderTxt);

				//Clear password field
				$("#registerPassword").val('');

				//Set focus to #registerPassword
				$("#registerPassword").focus();

			} //End if
			else
			{
				DisplaySuccessMsg();

				$("#userMsg").html('Password length ok');
				$("#btnRegisterUser").focus();
			} //End else


		}, //End success
		error: function(errorResult){
			console.log("Error checking password length occurred");
		} //End error
	}); //End $.ajax()

}); //End #registerPassword.focusout()

//Controls display of #userMsg element if AJAX requests are successful or user input is valid
function DisplayErrorMsg()
{
	//Display userMsg element
	$("#userMsg").css('display', 'block');
	$("#userMsg").css('margin-left', '341.5px');
	$("#userMsg").css('margin-right', '341.5px');

	//Change background color & text color
	$("#userMsg").css('background-color', '#f76e6e');
	$("#userMsg").css('color', 'black');
} //End DisplaySuccessMsg()

//Controls display of #userMsg element in AJAX requests are unsuccessful or user input is invalid
function DisplaySuccessMsg()
{
	//Display userMsg element
	$("#userMsg").css('display', 'block');
	$("#userMsg").css('margin-left', '341.5px');
	$("#userMsg").css('margin-right', '341.5px');

	//Change background color & text color
	$("#userMsg").css('background-color', '#99ccff');
	$("#userMsg").css('color', 'black');
} //End DisplayErrorMsg()

//Used to determine which content is displayed in the #manageUserForm (controls for "Register" or for "Modify")
function DisplayForm(theFormType)
{
	// //Hide both form control types (2 types: 'register' & 'manage')
//	$("#registerFormControls").css('display', 'none');
//	$("#manageFormControls").css('display', 'none');
	
	$('#registerUserForm').css('display', 'none');
	$('#manageUserForm').css('display', 'none');


	if(theFormType == 'register') //Display the "register" form controls
	{
		//Show register form controls
		$('#registerUserForm').css('display', 'block');

		$('.form-group').css('display', 'block');
//		$("#registerFormControls").css('display', 'block');

		//Populate "roles" select element
		GetRoles();

		//Display & change text on submit button
//		$('#manageUserSubmit').css('display', 'inline');
//		$('#manageUserSubmit').val('Register');

		//User feedback
		console.log("register");
	}
	else if(theFormType == 'manage') //Display the 'manage' form controls
	{

		$('#manageUserForm').css('display', 'block');

		//Show manage form controls
//		$("#manageFormControls").css('display', 'inline');

		//Change text on submit button
//		$('#manageUserSubmit').val('Update');

		//Hide the controls which will list a specific user's information (also hide the submit button)
		$('.form-group').css('display', 'none');
		//$(".manageUserControl").css('display', 'none');
		$("#btnUpdateUser").css('display', 'none');
		$("#btnDeleteUser").css('display', 'none');


		//Populate "users" select element
		GetUserList();

		//User feedback
		console.log("manage");
	}
	else
	{
		console.log("An unrecognized form type was selected on the \'manage users\' page");
	}
} //End DisplayForm()


//Populate #registerUserRole select element
function GetRoles()
{
	//Start AJAX proloader image (to prevent use of the form until data from AJAX request is loaded)
	$("#AJAXPreloader").css('display', 'block');

	//Empty select element (#registerRole)
	$('#registerRole').empty();

	//Add default value to select element (#registerRole)
	$('#registerRole').append("<option value='" + -1 + "' selected>Select Role</option>");


	//Get user roles for "Register User" version of form
	$.ajax(
	{
		url: "<?php echo site_url('Inventory/GetUserRoles'); ?>",
		method: "POST", //'method' should be used after jQuery 1.9
		dataType: "JSON",
		success: function(roleResults)
		{
			console.log(roleResults);

			//Populate #registerUserRole select box with roleResults
			var max = roleResults.length;

			for(counter = 0; counter < roleResults.length; counter++)
			{
				var role = roleResults[counter];
				var id = null;
				var descrip = null;

				//Store each role's id & description in variables to add to an option tag element
				//Note: each "role" object in the roleResults array has 2 properties: 1) id and 2) description
				$.each(role, function(key, value){
					if(key == "ID")
					{
						id = value;
					}
					else
					{
						descrip = value;
					}
				}); //End each()

				//Add role to #registerUserRole select element
				$("#registerRole").append("<option value='" + id + "'>" + descrip + "</option>");
			} //End for loop


			//Enable #registerUserRole select box control
			$("#registerRole").removeProp('disabled');

			//Hide AJAX processing image
			$("#AJAXPreloader").css('display', 'none');


		}, //End success function
		error: function(errorResult)
		{
			//User feedback
			console.log("An error occurred\n" + errorResult);

			//Hide AJAX processing image
			$("#AJAXPreloader").css('display', 'none');

		} //End error function

	}); //End .ajax()

} //End GetRoles() function

//Populate #userList select element on the #manage
function GetUserList()
{
	//Clear all existing option tags in the #manageUserList select element
	$('#manageUserList').empty();

	//Add default selection to select element
	$('#manageUserList').append("<option value='" + -1 + "' selected>Select User</option>");

	//Start AJAX preloader image
	$("#AJAXPreloader").css('display', 'block');

	//Get users for "Manage User" version of form
	$.ajax({
		url: "<?php echo site_url('Inventory/GetUserList'); ?>",
		method: "POST",
		dataType: "JSON",
		success: function(userList)
		{
			console.log("Success");

			var count = userList.length;

			for(x = 0; x < count; x++)
			{
				var userid = null;
				var fname = null;
				var lname = null;

				$.each(userList[x], function(key, value){
					switch(key)
					{
						case "ID":
							userid = value;
							break;
						case "First Name":
							fname = value;
							break;
						case "Last Name":
							lname = value;
							break;
						default:
							console.log("Switch doesn't account for this key/value pair:\n" + key + ": " + value);
							break;
					} //End switch statement
				}); //End $.each()

				//Add user to #manageUserList select element
				$("#manageUserList").append("<option value='" + userid + "'>" + lname + ", " + fname + "</option>");
			} //End for loop

			//Hide AJAX Preloader image
			$("#AJAXPreloader").css('display', 'none');


		}, //End success()
		error: function(errorResult){
			console.log("An error occurred");

			//Hide AJAX Preloader image
			$("#AJAXPreloader").css('display', 'none');

		} //End error()

	}); //End .ajax()

} //End GetUsers()

//Populate ManageUser form controls with a specific user's information (when a user is selected from the #userList select element)
function GetSpecificUser(userID)
{
	//Display AJAX Preloader image
	$('#AJAXPreloader').css('display', 'block');

	//Get requested user
	$.ajax({
		url: "<?php echo site_url('Inventory/GetSpecificUser'); ?>",
		method: "POST",
		data: {'UserID': userID},
		dataType: "JSON",
		success: function(selectedUser)
		{
			//Populate form controls with user's data
			$("#manageUsername").val(selectedUser.Username);
			$("#manageEmail").val(selectedUser.Email);
			$("#manageFName").val(selectedUser['First Name']);
			$("#manageLName").val(selectedUser['Last Name']);

			//Display submit button
			$("#manageUserSubmit").css('display', 'inline');

			//Hide AJAX Preloader image
			$("#AJAXPreloader").css('display', 'none');

		},
		error: function(errorResult)
		{
			//User feedback
			console.log("An error gathering the user\'s information occurred");

			//Hide AJAX Preloader image
			$("#AJAXPreloader").css('display', 'none');
		}
	});
} //End GetSpecificUser()


</script>