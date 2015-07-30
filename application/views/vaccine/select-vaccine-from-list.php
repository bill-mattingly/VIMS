<!--Body opened in header-->

<!--<?php
	//	$ndc = $vacList[0]->SaleNDC10;
	//	echo "<p>Vaccines with NDC Code: $ndc</p>";
	?> -->

	<!--Open form using w/ CodeIgniter validation-->
	<!-- // $formAttributes = array('id' => 'vacForm', 'data-NDCNum' => '\''.$vacList[0]->SaleNDC10.'\''); -->

	<?php
		echo validation_errors();
		echo form_open("Inventory/SelectVacFromList/$ndc"); // /$ndc )", $formAttributes);
	?>

		<p>Please Select The Option Which Best Describes The Product on The Invoice:</p>

		<select id="vaccineList" name="vaccineList"> <!-- id=\"vaccineList\"  onclick=\"hello();\"> -->
			<option value='-1' selected>&lt;Select Description&gt;</option>

				<?php
					foreach($vacList as $vaccine)
					{
						echo "<option value='$vaccine->DrugID'>$vaccine->PackageDescrip</option>";
					}
				?>

		</select>
		<input type="submit" value="Select">
	</form>

<!--Body closed in footer -->