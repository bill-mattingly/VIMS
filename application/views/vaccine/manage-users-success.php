
<div class="row col-md-12">
<h1>Success</h1>

<p><?php echo $feedback; ?></p>
<p><?php //var_dump($returnedValue); ?></p>

<h2>User Information</h2>
<div id="userdata">
	<label>Username:</label><span><?php echo " $username"; ?></span><br/>
	<label>Email:</label><span><?php echo " $email"; ?></span><br/>
	<label>First Name:</label><span><?php echo " $fname"; ?></span><br/>
	<label>Last Name:</label><span><?php echo " $lname"; ?></span><br/>
</div>

<br/>

<?php echo anchor(site_url('Inventory/ManageUsers'), "Manage Users"); ?>
</div> <!--End .row .col-md-12 -->