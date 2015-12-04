<!--Body opened in header file-->

<div class="row">

	<h1>Vaccine Loan Return Form</h1>
	<p>Add Information For Vaccine Loan Returns Below:</p>

	<?php
		echo validation_errors();

		$attributes = array('id' => 'loanreturn-form');
		echo form_open('Inventory/LoanReturn', $attributes);

		echo "<div class='form-group'>";
			echo "<label for='ndc10' class='actionform'>NDC 10:</label>";
			echo "<input id='ndc10' type='text' value='".$ndc10."' readonly><br/>";
		echo "</div>";

		// echo "<div class='form-group'>";
		// 	echo "<label for='ndc11' class='actionform'>HIPAA NDC 11:</label>";
		// 	echo "<input id='ndc11' type='text' name='ndc11' value='".$ndc11."' readonly><br/>";
		// echo "</div>";
	?>

		<div class="form-group">
			<label for="borrowerID" class='actionform'>Returning Department:</label>
			<select id="borrowerID" name="borrowerID">
				<option value='-1' selected>&lt;Select Department&gt;</option>

				<?php
					foreach($borrowerList as $borrower)
					{
						echo "<option value='".$borrower->BorrowerID."'>";
						echo $borrower->EntityName;
						echo "</option>";
					}
				?>
			</select>
		</div>
		
		<div class="form-group">
			<label for="expireDate" class='actionform'>Expiration Date:</label>
			<input id="datepicker" type="text" name="expireDate"><br/>
		</div>

		<div class="form-group">
			<label for="lotNum" class='actionform'>Lot Number:</label>
			<input id="lotNum" type="text" name="lotNum"><br/>
		</div>

		<div class="form-group">
			<label for="packageQty" class='actionform'>Number of Packages Returned:</label>
			<input id="packageQty" type="number" name="packageQty"><br/>
		</div>

		<div class="form-group">
			<label for="dosesPerPackage" class='actionform'>Doses Per Package:</label>
			<input id="dosesPerPackage" type="number" name="dosesPerPackage"><br/>
		</div>

		<div class="form-group">
			<input type="submit" name="Add">
		</div>

	</form>
</div> <!-- /End .row -->

<!--Body closed in footer file-->