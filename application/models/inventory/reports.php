<?php

class Reports extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}



	//Select Statement
	public function InventorySummary()
	{
		
		$sql = 
		"SELECT 
			tbl1.ProprietaryName as 'Proprietary Name', 
			tbl1.packagedescrip as 'Package Description', 
			tbl1.LotNum as 'Lot Number', 
			(IFNULL(tbl1.Invoices, 0) - IFNULL(tbl2.Administer, 0) - IFNULL(tbl3.LoanOut, 0) + IFNULL(tbl4.LoanReturn, 0)) as 'Net Inventory (Doses)',
			IFNULL(tbl1.invoices, 0) as 'Order Invoices (Doses)', 
			IFNULL(tbl2.administer, 0) as 'Total Administered (Doses)', 
			IFNULL(tbl3.LoanOut, 0) as 'Loaned Out (Doses)', 
			IFNULL(tbl4.LoanReturn, 0) as 'Loan Returned (Doses)'
		FROM
			/*Order Invoices*/
			(
				SELECT 
					vt.drugid as DrugID, 
					vt.lotnum as LotNum, 
					pr.proprietaryname, 
					pa.packagedescrip, 
					sum(oi.packageqty * oi.doses_per_package) as invoices 
				FROM `order_invoice` oi inner join 
					 `vaccinetrans` vt on oi.invoiceid = vt.transid inner join 
					 `fda_drug_package` pa on vt.drugid = pa.drugid inner join 
					 `fda_product` pr on pa.productid = pr.productid 
				GROUP BY 
					vt.drugid, vt.lotnum
			) as tbl1

			LEFT OUTER JOIN

			/*Administered*/
			(
				SELECT 
					vt.drugid as DrugID,
					a.Package_DrugID as PackageDrugID,
					vt.lotnum as LotNum,
					sum(a.doses_given) as administer 
				FROM 
					`administer` a inner join 
					`vaccinetrans` vt on a.administerid = vt.transid
				GROUP BY
					vt.drugid, vt.lotnum
			) as tbl2 on tbl1.DrugID = tbl2.PackageDrugID AND tbl1.LotNum = tbl2.LotNum
			
			LEFT OUTER JOIN
			
			/*LoanOut*/
			(
				SELECT 
					vt.DrugId as DrugId,
					vt.LotNum as LotNum,
					vt.ExpireDate,
					Sum(lo.total_doses) as LoanOut
				FROM `vaccinetrans` vt inner join 
					 `loanout` lo on vt.TransId = lo.LoanId 
				GROUP BY
					vt.DrugId, vt.LotNum
			) as tbl3 on tbl1.DrugID = tbl3.DrugID AND tbl1.lotnum = tbl3.lotnum

			LEFT OUTER JOIN

			/*LoanReturn*/
			(
				SELECT 
					vt.DrugId as DrugId,
					vt.LotNum as LotNum,
					vt.ExpireDate,
					Sum(lr.total_doses) as LoanReturn 
				FROM 
					`vaccinetrans` vt inner join 
					`loanreturn` lr on vt.TransId = lr.ReturnId
				GROUP BY 
					vt.DrugId, vt.LotNum
			) as tbl4 on tbl1.drugid = tbl4.drugid AND tbl1.lotnum = tbl4.lotnum
			";

		
		//$sql =

			/* "SELECT 
				pr.proprietaryname as 'Proprietary Name',
				pr.nonproprietaryname as 'Non-Proprietary Name', 
				pr.labelername as 'Labeler Name', 
				pa.salendc10 as 'Carton NDC10', 
				pa.usendc10 'Dose NDC10', 
				pa.fulldescrip as 'Description', 
				pa.drug_cost as 'Clinic Cost', 
				pa.trvl_chrg as 'Travel Patient Chrg', 
				pa.refugee_chrg as 'Refugee Patient Chrg', 
				net.drugid as 'Drug ID', 
				net.lotnum as 'Lot Number', 
				net.expiredate as 'Expire Date', 
				sum(net.vacdoses) as 'Net Doses'
			*/
	
		/*
			"SELECT 
				pr.proprietaryname as 'Proprietary Name',
				pa.packagedescrip as 'Description',  
				net.lotnum as 'Lot Number', 
				net.expiredate as 'Expire Date', 
				sum(net.vacdoses) as 'Net Doses'
			FROM
				(
					/*Invoice transactions
						(SELECT
						 	vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, sum(oi.doses_per_package * oi.packageqty) as vacdoses 
						 FROM 
						 	vaccinetrans vt inner join order_invoice oi on vt.transid = oi.invoiceid 
						 GROUP BY 
						 	vt.drugid, vt.lotnum, vt.expiredate)

					UNION
					/*Administer transactions 
						(SELECT 
							a.package_drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, SUM( a.doses_given)*-1 AS vacdoses /* Multiplied by '-1' to show a reduction in inventory
						FROM 
							vaccinetrans vt INNER JOIN administer a ON vt.transid = a.administerid
						GROUP BY 
							vt.drugid, vt.lotnum, vt.expiredate)

					UNION
					/*LoanOut transactions
						(SELECT 
							vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, SUM(lo.doses_per_package * lo.packageqty)*-1 AS vacdoses /* Multiplied by '-1' to show a reduction in inventory
						FROM
							vaccinetrans vt INNER JOIN loanout lo ON vt.transid = lo.borrowerid
						GROUP BY
							vt.drugid, vt.lotnum, vt.expiredate)

					UNION
					/*LoanReturn transactions
						(SELECT
							vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, sum(lr.doses_per_package * lr.packageqty) as vacdoses
						FROM
							vaccinetrans vt INNER JOIN loanreturn lr on vt.transid = lr.returnid
						GROUP BY
							vt.drugid, vt.lotnum, vt.expiredate
						)

				) net /*Every table has to have it's own alias according to MySQL spec

				INNER JOIN

				fda_drug_package pa on net.drugid = pa.drugid INNER JOIN
				fda_product pr on pa.productid = pr.productid

			GROUP BY 
				net.drugid, net.lotnum, net.expiredate";
		*/

		$qryResult = $this->db->query($sql);

		//return $qryResult;

		//Turn query result into an array of row objects (each row in the result is an object)
		$arrayResult = $qryResult->result();
		//var_dump($arrayResult);

		//Remove rows where net inventory == 0
		$modifiedResult = null;
		$counter = 0;

		//var_dump($arrayResult);

		foreach($arrayResult as $vacInventoryRow)
		{
			if($vacInventoryRow->{"Net Inventory (Doses)"} != 0)
			{
				$modifiedResult[$counter] = $vacInventoryRow;
				$counter++;
			}
		}

		if($modifiedResult == null) //If query result == null  (i.e. nothing is in inventory after rows == 0 are removed), return the query's column headers
		{
			$modifiedResult[0] = "headerOnly";

			$headerRowObject = new stdClass(); //anonymous object to store header row information
				//Search the $sql string variable for everything within single quotes ('') between the SELECT & FROM statements
				$headerStr = "";

				//Break the $sql variable into arrays of strings (each array index will contain a segment broken up by ",")
				$completeHeadingArray = str_getcsv($sql, ",");

				//Create new properties for $headerRowObject by searching for the alias name of each column in the $sql string. Each alias name will then become the name of a new property of the anonymous $headerRowObject object
				foreach($completeHeadingArray as $strSegment)
				{
					$startPosition = strpos($strSegment, "'") + 1;
					$endPosition = strpos(substr($strSegment, ($startPosition + 1)), "'") + $startPosition; //gets the start position of the 2nd "'". The string to search is the substring of $strSegement beginning immediately after the first "'". $startPosition needs to be added back in order to get the true position of the 2nd "'" since $strPos only returns the position of the first occurance of whatever is searched (thus the search string was the part of the original string that began after the very first "'")
					$strLength = ($endPosition - $startPosition) + 1;

					//var_dump($startPosition);
					//var_dump($endPosition);
					//var_dump($strLength);


					$headerStr = substr($strSegment, $startPosition, $strLength);  //(strlen($strSegment) - )length);
					//var_dump($headerStr);

					if($strLength > 1) //This condition exists to filter out the $strSegment variables that don't contain any single quotes ('') - if you look at the $sql variable some string segments won't contain "'" since the $sql variable was broken into segments using the "," delimiter
					{
						$headerRowObject->$headerStr = $headerStr; //Assign each column name as property to anonymous $headerRowObject object
					}

				}//End foreach

				//substr($sql);



			$modifiedResult[1] = $headerRowObject;

			//var_dump($modifiedResult);
		}


		//Return modified query result to the calling function (result no longer includes rows where net inventory == 0)
		return $modifiedResult;


	} //End InventorySummary()


	public function TransactionsByType($transType)
	{
		//$type = $transType;

		$sql = 
		   "SELECT
		    net.transid as 'Transaction ID',
		    net.transdate as 'Transaction Date',
		    pr.proprietaryname as 'Proprietary Name',
			pr.nonproprietaryname as 'Non-Proprietary Name',  
		    net.lotnum as 'Lot Number', 
			net.expiredate as 'Expire Date',
			pa.fulldescrip as 'Description',
			net.vacdoses as 'Transaction Doses',
			net.transtype as 'Transaction Type'

		FROM
			(
				/*
					Using transid column in each of the unioned result sets b/c it provides a unique identifier for each column
					A unique id is needed for each column to prevent columns from being removed from the result set.
					Non-unique columns will be removed from result sets b/c UNION, by default, removes duplicate rows.
					UNION ALL includes duplicate rows. Added 'transid' column as an extra precaution.
				*/
				/*Invoice transactions*/
					(SELECT
					 	vt.transid as transid, t.transdate as transdate, vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, (oi.doses_per_package * oi.packageqty) as vacdoses, 'Invoice' as transtype 
					 FROM 
					 	vaccinetrans vt inner join order_invoice oi on vt.transid = oi.invoiceid inner join transaction t on t.transid = vt.transid
					)

				UNION ALL /*Need keyword ALL to prevent 'duplicate rows' from being removed; duplicate row removal is the default action of UNION command) */
				/*Administer transactions*/ 
					(SELECT 
						vt.transid as transid, t.transdate as transdate, a.package_drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, (a.doses_given)*-1 AS vacdoses, 'Administer' as transtype /* Multiplied by '-1' to show a reduction in inventory*/
					FROM 
						vaccinetrans vt INNER JOIN administer a ON vt.transid = a.administerid inner join transaction t on t.transid = vt.transid
					)

				UNION ALL
				/*LoanOut transactions*/
					(SELECT 
						vt.transid as transid, t.transdate as transdate, vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, (lo.total_doses)*-1 AS vacdoses, 'Loan Out' as transtype /* Multiplied by '-1' to show a reduction in inventory*/
					FROM
						vaccinetrans vt INNER JOIN loanout lo ON vt.transid = lo.loanid INNER JOIN transaction t on t.transid = vt.transid /*lo.borrowerid*/
					)

				UNION ALL
				/*LoanReturn transactions*/
					(SELECT
						vt.transid as transid, t.transdate as transdate, vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, (lr.total_doses) as vacdoses, 'Loan Return' as transtype
					FROM
						vaccinetrans vt INNER JOIN loanreturn lr on vt.transid = lr.returnid inner join transaction t on t.transid = vt.transid
					)

			) net /*Every table has to have it's own alias according to MySQL spec*/

			INNER JOIN

			fda_drug_package pa on net.drugid = pa.drugid INNER JOIN
			fda_product pr on pa.productid = pr.productid ";


			switch($transType) //Filter query results based on user request
			{
				case 'all':
					//If "All", then don't have a where clause (otherwise include a where clause)
					break;
				case 'invoice':
					$sql .= "WHERE net.transtype = 'Invoice' ";
					break;
				case 'administer':
					$sql .= "WHERE net.transtype = 'Administer' ";
					break;
				case 'loanout':
					$sql .= "WHERE net.transtype = 'Loan Out' ";
					break;
				case 'loanreturn':
					$sql .= "WHERE net.transtype = 'Loan Return' ";
					break;
				case 'outstandingloan':
					//$sql .= "";
					break;
				default: 
					$resultsArray = $transType; 
					return $resultsArray; //An error occurred
					break;
			}


			$sql .= "ORDER BY
						net.transid";

			//return $sql;

			//Submit query to get results
			$theResult = $this->db->query($sql); //table->generate($sql); //db->query($sql);
			$resultsArray = $theResult->result();

			//If number of rows is 0, then assign an array of header values to create the table
//			if($theResult->num_rows() == 0)
//			{
				//Normal query results are returned as an array of objects (each item in the array is a row in the result set)
				//Thus, need to create a generic object with just the information for the header row so the "Inventory" can display a blank table without throwing an error

				//Header row object
//				$headerRowObj = new stdClass();
//				$headerRowObj->{'Transaction ID'} = 'Transaction ID';
//				$headerRowObj->{'Transaction Date'} = 'Transaction Date';
//				$headerRowObj->{'Proprietary Name'} = 'Proprietary Name';
//				$headerRowObj->{'Non-Proprietary Name'} = 'Non-Proprietary Name';
//				$headerRowObj->{'Lot Number'} = 'Lot Number';
//				$headerRowObj->{'Expire Date'} = 'Expire Date';
//				$headerRowObj->{'Description'} = 'Description';
//				$headerRowObj->{'Transaction Doses'} = 'Transaction Doses';
//				$headerRowObj->{'Transaction Type'} = 'Transaction Type';

				//Add header row object to $resultArray array variable (to mimick the array of objects result returned by a query with 1 or more records)
//				$resultsArray[0] = $headerRowObj;

//			} //End if


			// 	$resultsArray = array(
			// 		0 => 'Transaction ID',
			// 		1 => 'Transaction Date',
			// 		2 => 'Proprietary Name',
			// 		3 => 'Non-Proprietary Name',
			// 		4 => 'Lot Number',
			// 		5 => 'Expire Date',
			// 		6 => 'Description',
			// 		7 => 'Transaction Doses',
			// 		8 => 'Transaction Type'
			// 	);
			// }
			// $bool = ($theResult->num_rows() == 0);
			// $resultsArray[0] = $bool; //$theResult->num_rows();
			// //$resultsArray = 'hi';
			return $resultsArray;

	} //End TransactionsByType()


} //End Reports class

?>