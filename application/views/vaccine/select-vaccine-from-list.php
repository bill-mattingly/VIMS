<!--Body opened in header-->

	
	<h1 id="select-heading">Select Vaccine</h1>

	<p id="select-text">Select the Option Which Best Describes the Vaccine:</p>

	<?php
		echo validation_errors();

		$attributes = array('id' => 'select-form');
		echo form_open("Inventory/SelectVacFromList", $attributes); // /$ndc10
	?>
		<div class="form-group">
			<select id="vaccineList" name="vaccineList">
				<option value='-1' selected>Select a Description</option>

					<?php
						$indexVal = 0;

						foreach($vacList as $vaccine)
						{
							//var_dump($vaccine);
							//$indexVal (rather than drugID) is used for value so that the index of the selected vaccine in the vacList array can be chosen in the controller
							echo "<option value='$indexVal'>".$vaccine->{'Package Description'}."</option>"; //PackageDescrip</option>";
							$indexVal++;
						}
					?>

			</select>
		</div>

		<div class="form-group">
			<input id="select-submit" type="submit" value="Select">
		</div>
	</form>

	<button type='button' id='cancelBtn'><a href="<?php echo site_url('Inventory/Index') ?>">Cancel</a></button>

<!--Body closed in footer -->