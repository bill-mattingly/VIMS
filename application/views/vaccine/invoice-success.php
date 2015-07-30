<!--Body tag is opened in header-->


<!--
			<p>You added the following vaccine:</p>
			<table>
				<thead>
					<tr>
						<?php
						/*foreach($currentTrans as $key => $val)
						{
							echo "<th>$key</th>";
						}*/
						?>
					</tr>
				</thead>
				<tbody>
					<tr>
						<?php /*
						foreach($currentTrans as $key => $val)
						{
							echo "<td>$val</td>";
						} */
						?>
					</tr>
				</tbody>
			</table>

			<br/><br/>

			<p>This vaccine&#39;s inventory is now the following:</p>
			<table>
				<thead>
					<tr>
						<?php /*
						foreach($vacSummary as $key => $val)
						{
							echo "<th>$key</th>";
						} */
						?>
					</tr>
				</thead>

				<tbody>
					<tr>
						<?php /*
						foreach($vacSummary as $key => $val)
						{
							echo "<td>$val</td>";
						} */
						?>
					</tr>
				</tbody>
			</table>
			<br/><br/>
-->

			<!--
			//date_default_timezone_set("America/New_York");
			//$stamp = date('Y-m-d H:i:sa', strtotime($timestamp));
			// echo $timestamp."<br/>";
			// echo $transid."<br/>";
			-->

			<!--//Link to add new transaction-->
			<?php
				echo $transSummary;
				echo "<br/><br/>";
				echo anchor('Inventory/Order', 'Add Another Vaccine');
			?>

<!--//Body tag is closed in footer-->


