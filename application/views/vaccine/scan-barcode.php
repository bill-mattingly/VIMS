<!--Body opened in header-->

<div class="row col-md-12">

<h2 id="scanbc-heading">Scan Barcode</h2>

<p id="scanbc-text">Select Desired Action then Scan Vaccine Vial or Box Barcode:</p>

<?php
	echo validation_errors();

	if ($this->session->error != null) {
		echo "<p class='errormsg'>".$this->session->error."</p>";
		$this->session->error = null; //Reset session variable value
	}

	//CodeIgniter html attributes array
	$attributes = array('id' => 'scanbc-form');

	$action = $this->session->theAction;
	// echo "Inventory/".$action;
	// echo "\nPage is first...";

	//CodeIgniter, create open form tag
	echo form_open("Inventory/".$action, $attributes);
?>
	<!--
		<div class="form-group">
			<input id="invoice" type="radio" name="vaccine-action" value="invoice">
			<label id="scanbc-invoice" for="invoice">Invoice</label>

			<input id="administer" type="radio" name="vaccine-action" value="administer">
			<label id="scanbc-administer" for="administer">Administer</label>

			<input id="loanout" type="radio" name="vaccine-action" value="loanout">
			<label id="scanbc-loanout" for="loanout">Loan Out</label>

			<input id="loanreturn" type="radio" name="vaccine-action" value="loanreturn">
			<label id="scanbc-loanreturn" for="loanreturn">Loan Return</label>
		</div>
	-->

		<div class="form-group">
			<input id="scanbc-barcode" type="text" name="barcode" placeholder="Scan Barcode Here"><br/>
		</div>

		<div class="form-group">
			<input type="submit" value="Submit">
		</div>
	</form>

</div> <!-- /End .row -->

<!--Body closed in footer-->