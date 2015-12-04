<?php

class Inventory extends CI_Controller
{

	public function __construct(){
		parent::__construct();

		//Load libraries that may be useful to all controller methods
		$this->load->database(); //Loads the database library
		$this->load->library('table'); //Helper class to create HTML tables
		$this->load->library('session'); //Helper class to load
		$this->load->helper('url'); //Helper class to create anchor links
		$this->load->model('inventory/vaccine'); //Loads the vaccine model
		$this->load->model('inventory/borrower'); //Loads the borrower model


	}

	public function Index()
	{
		
		$qryInventorySum = "
		select tbl1.ProprietaryName, tbl1.packagedescrip, tbl1.LotNum, IFNULL(tbl1.invoices, 0) as 'Order Invoices (Doses)', IFNULL(tbl2.administer, 0) as 'Total Administered (Doses)', IFNULL(tbl3.LoanOut, 0) as 'Loaned Out (Doses)', IFNULL(tbl4.LoanReturn, 0) as 'Loan Returned (Doses)', (IFNULL(tbl1.Invoices, 0) - IFNULL(tbl2.Administer, 0) - IFNULL(tbl3.LoanOut, 0) + IFNULL(tbl4.LoanReturn, 0)) as 'Net Inventory (Doses)'
		from
		/*Order Invoices*/
		(select vt.drugid as DrugID , vt.lotnum as LotNum, pr.proprietaryname, pa.packagedescrip, sum(oi.packageqty * oi.doses_per_package) as invoices from `order_invoice` oi inner join `vaccinetrans` vt on oi.invoiceid = vt.transid inner join `fda_drug_package` pa on vt.drugid = pa.drugid inner join `fda_product` pr on pa.productid = pr.productid group by vt.drugid, vt.lotnum) as tbl1
		left outer join
		/*Administered*/
		(select vt.drugid as DrugID, a.Package_DrugID as PackageDrugID, vt.lotnum as LotNum, sum(a.doses_given) as administer from `administer` a inner join `vaccinetrans` vt on a.administerid = vt.transid group by vt.drugid, vt.lotnum) as tbl2 on tbl1.DrugID = tbl2.PackageDrugID AND tbl1.LotNum = tbl2.LotNum
		left outer join
		/*LoanOut*/
		(select vt.DrugId as DrugId, vt.LotNum as LotNum, vt.ExpireDate, Sum(lo.PackageQty * lo.Doses_Per_Package) as LoanOut from `VaccineTrans` vt inner join `LoanOut` lo on vt.TransId = lo.LoanId group by vt.DrugId, vt.LotNum) as tbl3 on tbl1.DrugID = tbl3.DrugID and tbl1.lotnum = tbl3.lotnum
		left outer join
		(select vt.DrugId as DrugId, vt.LotNum as LotNum, vt.ExpireDate, Sum(lr.PackageQty * lr.Doses_Per_Package) as LoanReturn from `VaccineTrans` vt inner join `LoanReturn` lr on vt.TransId = lr.ReturnId group by vt.DrugId, vt.LotNum) as tbl4 on tbl1.drugid = tbl4.drugid and tbl1.lotnum = tbl4.lotnum
		";


/*		"
		select tbl1.ProprietaryName, tbl1.packagedescrip, tbl1.LotNum, tbl1.invoices as 'Order Invoices', tbl2.administer as 'Administered', (tbl1.invoices - tbl2.administer) as 'Net Inventory'
		from
		/*Order Invoices*/
		/*(select vt.drugid as DrugID , vt.lotnum as LotNum, pr.proprietaryname, pa.packagedescrip, sum(oi.packageqty * oi.doses_per_package) as invoices from `order_invoice` oi inner join `vaccinetrans` vt on oi.invoiceid = vt.transid inner join `fda_drug_package` pa on vt.drugid = pa.drugid inner join `fda_product` pr on pa.productid = pr.productid group by vt.drugid, vt.lotnum) as tbl1
		left outer join
		/*Administered*/
		/*(select vt.drugid as DrugID, vt.lotnum as LotNum, sum(a.doses_given) as administer from `administer` a inner join `vaccinetrans` vt on a.administerid = vt.transid group by vt.drugid, vt.lotnum) as tbl2 on tbl1.DrugID = tbl2.DrugID AND tbl1.LotNum = tbl2.LotNum
		";*/

	//	$qryVacByNDC = "SELECT T.UseNDC10 AS 'NDC', M.MakerName AS 'Maker/Labeler', T.LotNum AS 'Lot Number', T.ExpireDate AS 'Expiration Date', SUM(T.TransQty) as 'Total Quantity' FROM Manufacturer M INNER JOIN Vaccine V on M.MakerID = V.MakerID INNER JOIN Transaction T on V.UseNDC10 = T.UseNDC10 GROUP BY T.UseNDC10";
	//	$qryVacByLotNum = "SELECT T.UseNDC10 AS 'NDC', M.MakerName AS 'Maker/Labeler', T.LotNum AS 'Lot Number', T.ExpireDate AS 'Expiration Date', SUM(T.TransQty) as 'Quantity' FROM Manufacturer M INNER JOIN Vaccine V on M.MakerID = V.MakerID INNER JOIN Transaction T on V.UseNDC10 = T.UseNDC10 GROUP BY T.LotNum";

		$qryResult = $this->db->query($qryInventorySum);

	//	$resultVacByNDC = $this->db->query($qryVacByNDC);
	//	$resultVacByLotNum = $this->db->query($qryVacByLotNum);

	//	echo $this->table->generate($resultVacByNDC);
	//	echo $this->table->generate($resultVacByLotNum);

		$data['tblSummary'] = $this->table->generate($qryResult);

	//	$data['tblNDC'] = $this->table->generate($resultVacByNDC);
	//	$data['tblLotNum'] = $this->table->generate($resultVacByLotNum);

		//Load view
		$this->load->view('vac-header');
		$this->load->view("vaccine/index", $data);
		$this->load->view('vac-footer');

	} //End Index()

	public function Order()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$vaccine = new Vaccine();

	    $data['title'] = 'Add a Vaccine Invoice';

	    $this->form_validation->set_rules('linBarcode', 'Linear Barcode', 'required');
	    $this->form_validation->set_rules('expireDate', 'Expire Date', 'required');
		$this->form_validation->set_rules('lotNum', 'Lot Number', 'required');
		$this->form_validation->set_rules('clinicCost', 'Cost Per Dose', 'required');
		$this->form_validation->set_rules('packageQty', 'Package Qty', 'required');
		$this->form_validation->set_rules('dosesPerPackage', 'Doses Per Package', 'required');

	    if ($this->form_validation->run() === FALSE)
	    {
	    	$this->session->linBarcode = $this->input->post('linBarcode');
	    	$this->session->expireDate = $this->input->post('expireDate');
	    	$this->session->lotNum = $this->input->post('lotNum');

	    	$this->session->clinicCost = $this->input->post('clinicCost');
	    	$this->session->packageQty = $this->input->post('packageQty');
	    	$this->session->dosesPerPackage = $this->input->post('dosesPerPackage');

	    	//Reload form
	    	$this->load->view('vac-header');
	    	$this->load->view('vaccine/invoice', $data);
			$this->load->view('vac-footer');
	    }
	    else
	    {
	    	//Store form data in a session array (CodeIgniter syntax; CI syntax makes use of PHP $_SESSION global var)
	    	$this->session->linBarcode = $this->input->post('linBarcode');
	    	$this->session->expireDate = $this->input->post('expireDate');
	    	$this->session->lotNum = $this->input->post('lotNum');

	    	$this->session->clinicCost = $this->input->post('clinicCost');
	    	$this->session->packageQty = $this->input->post('packageQty');
	    	$this->session->dosesPerPackage = $this->input->post('dosesPerPackage');
	    	$this->session->PageOrigin = "invoice";

			$barcodeArray = $vaccine->ParseBarcode($this->session->linBarcode, TRUE);

			// echo "Here's the ndc:[";
			// echo $barcodeArray['ndc'];
			// echo "]";

			$vaccineArray = $vaccine->GetVaccine($barcodeArray['ndc'], FALSE); //$vaccine->Order($this->input->post('linBarcode'));

			if(count($vaccineArray) > 1)
	    	{
	    		$this->session->WasAnOrder = FALSE;

				$this->SelectVacFromList($barcodeArray['ndc']);//$var1, $var2);//$barcodeArray['ndc'], $var2, $var3);

	    	}
	    	else
	    	{
	    		//Insert the selected vaccine in the database & give user feedback
	    		$transArray = $vaccine->Order($vaccineArray[0]->DrugID);

	    		$data['transid'] = $transArray['TransID'];

				//Timestamp value is stored in database in UTC time
				//To return it to the user, set the local timezone & then assign the UTC timestamp value to a new variable
				//date_default_timezone_set("America/New_York");
				//$data['timestamp'] = date("Y-m-d h:i:sa", $transArray['TransDate']);
				$data['timestamp'] = $transArray['TransDate'];
				$data['drugID'] = $vaccineArray[0]->DrugID;

				$currentTrans = $vaccine->GetTransaction($transArray['TransID'], $this->session->PageOrigin, FALSE);
				$vacSummary = $vaccine->GetSingleVacSum($vaccineArray[0]->DrugID);

				//Data from query results				
				$data['transSummary'] = $this->table->generate($currentTrans); //pass the first result object in the array to the $data array
				//$data['vacSummary'] = $vacSummary[0]; //pass the first result object in the array to the $data array

				//Load view
				$this->load->view("vac-header");
				$this->load->view("vaccine/invoice-success", $data);
				$this->load->view("vac-footer");
			}
	
	    }
	

	} //End Order()


	public function Administer()
	{
		//Load helpers
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('table');
		$vaccine = new Vaccine();

		//Validation Rules
		$this->form_validation->set_rules('linBarcode', 'Linear Barcode', 'required');
		$this->form_validation->set_rules('expireDate', 'Expire Date', 'required');
		$this->form_validation->set_rules('lotNum', 'Lot Number', 'required');
		$this->form_validation->set_rules('customerChrg', 'Customer Charge', 'required');
		$this->form_validation->set_rules('doseQty', 'Dose Quantity', 'required');


		if($this->form_validation->run() === FALSE)
		{
			$this->load->view('vac-header');
			$this->load->view("vaccine/administer");
			$this->load->view('vac-footer');
		}
		else
		{
			//Form variables
			$this->session->linBarcode = $this->input->post('linBarcode');
			$this->session->expireDate = $this->input->post('expireDate');
			$this->session->lotNum = $this->input->post('lotNum');
			$this->session->doseQty = $this->input->post('doseQty');
			$this->session->customerChrg = $this->input->post('customerChrg');


			//Get vaccine
			$barcodeArray = $vaccine->ParseBarcode($this->session->linBarcode, FALSE);
			//echo "Lin barcode: ".$this->session->linBarcode;
			//echo "<br/>Parsed barcode: ".$barcodeArray['ndc'];

			$theNDC = $barcodeArray['ndc'];

			$theVaccines = $vaccine->GetVaccine($theNDC, TRUE);


			//If more than one vaccine with ndc num, then display list for user to select
			if(count($theVaccines) > 1)
			{
				//Display all vaccines to user for them to choose
				$this->session->PageOrigin = 'administer';
				$this->session->WasAnOrder = FALSE;

				$this->SelectVacFromList($barcodeArray['ndc']);
			}
			else
			{
				//After identified, input data to database
				//Add data to database

				$transData = $vaccine->Administer($theVaccines[0]->DrugID); //$this->input->post('linBarcode'));

				//Gather transaction data for display to user
				$qryAdministerTrans = 
				"SELECT T.TRANSDATE As 'Transaction Date', PA.SALENDC10 As 'Bulk Carton/Package NDC', PA.USENDC10 As 'Individual Vial/Dose NDC', PA.PACKAGEDESCRIP As 'Description', PR.PROPRIETARYNAME As 'Proprietary Name', 
				PR.NONPROPRIETARYNAME As 'Non-Proprietary Name', PR.LABELERNAME As 'Labeler Name', VT.EXPIREDATE As 'Expiration Date', 
				VT.LOTNUM As 'Lot Number', A.CUST_PER_DOSE_CHRG As 'Customer Per Dose Charge', A.DOSES_GIVEN As 'Doses Given'
				FROM
				`FDA_PRODUCT` PR INNER JOIN `FDA_DRUG_PACKAGE` PA ON PR.PRODUCTID = PA.PRODUCTID INNER JOIN
				`VACCINETRANS` VT ON VT.DRUGID = PA.DRUGID INNER JOIN
				`TRANSACTION` T ON T.TRANSID = VT.TRANSID INNER JOIN
				`ADMINISTER` A ON VT.TRANSID = A.ADMINISTERID
				WHERE A.ADMINISTERID = (SELECT MAX(ADMINISTERID) FROM `ADMINISTER`)";

				$qryResultAT = $this->db->query($qryAdministerTrans);

				$tblAdminTrans = $this->table->generate($qryResultAT);
				
				$data['AdminTrans'] = $tblAdminTrans;

				//Display message to user success view
				$this->load->view("vac-header.php"); //Header file
				$this->load->view("vaccine/administer-success", $data);
				$this->load->view("vac-footer.php"); //Footer file

			} //End Else
		} //End Else

	} //End Administer()


	public function LoanOut()
	{
		//Method variable
		$vaccine = new Vaccine();
		$borrower = new Borrower();

		//Load helpers
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('table');

		$data['listOfBorrowers'] = $borrower->DisplayBorrowers();
		//$this->session->ListofBorrowers = $data['listOfBorrowers'];

		//Validation rules
		$this->form_validation->set_rules('linBarcode', 'Linear Barcode', 'required');
		$this->form_validation->set_rules('expireDate', 'Expire Date', 'required');
		$this->form_validation->set_rules('lotNum', 'Lot Number', 'required');
		$this->form_validation->set_rules('packageQty', 'Package Quantity', 'required');
		$this->form_validation->set_rules('dosesPerPackage', 'Doses Per Package', 'required');
		$this->form_validation->set_rules('borrowerID', 'Borrower', 'callback_CheckDropdownSelect[$aBorrowerID]');
		$this->form_validation->set_message('CheckDropdownSelect', 'Please Select A Borrower From The List');


		if($this->form_validation->run() === FALSE)
		{
			//Load view
			$this->load->view("vac-header");
			$this->load->view("vaccine/loanout", $data);
			$this->load->view("vac-footer");
		}
		else
		{
			//Check for ndc number in INVENTORY in the database
			//echo "Value of borrowerid: ".$_POST['borrowerID'];
			//echo "<br/>";

			$this->session->expireDate = $this->input->post('expireDate');
			$this->session->lotNum = $this->input->post('lotNum');
			$this->session->BorrowerID = $this->input->post('borrowerID');
			$this->session->DosesPerPackage = $this->input->post('dosesPerPackage');
			$this->session->PackageQty = $this->input->post('packageQty');

			$barcodeArray = $vaccine->ParseBarcode($this->input->post('linBarcode'), TRUE);


			// echo "Here is the borrower id...[";
			// echo $this->session->BorrowerID;
			// echo "]";


			$ndc = $barcodeArray['ndc'];

			$qryArray = $vaccine->GetVaccine($ndc, FALSE);

			// echo "Here is the count of the array...".count($qryArray)."<br/>";

			// echo "Here is the info in the first index (0) of the vaccine query array:<br/>";
			// var_dump($qryArray[0]);

			//If ndc query returns multiple values, display "select" screen
			if(count($qryArray) > 1)
			{
				$this->session->PageOrigin = 'loanout';
				$this->session->BorrowerID = $this->input->post('borrowerID');
				//echo "The borrowerid is: [".$this->input->post('borrowerID')."]";

				$this->SelectVacFromList($qryArray[0]->SaleNDC10);
			}
			else //Else, input data into database
			{
				//Enter data into database
				$transData = $vaccine->loanout($qryArray[0]->DrugID, $this->session->BorrowerID);//$this->input->post('borrowerID'));
				

				//Data for summary page
				$data['borrowerID'] = $this->session->BorrowerID;
				$data['transSummary'] = $this->table->generate($transData['tblSummary']);

				//Return summary of transaction for user
				$this->load->view('vac-header');
				$this->load->view('vaccine/loanout-success', $data);
				$this->load->view('vac-footer');

			}
		}


	} //End LoanOut()

	public function LoanReturn()
	{
		//Helpers
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('table');

		$vaccine = new Vaccine();
		$borrower = new Borrower();

		//Validation Rules
		$this->form_validation->set_rules('linBarcode', 'Linear Barcode', 'required');
		$this->form_validation->set_rules('expireDate', 'Expire Date', 'required');
		$this->form_validation->set_rules('lotNum', 'Lot Number', 'required');
		$this->form_validation->set_rules('packageQty', 'Package Quantity', 'required');
		$this->form_validation->set_rules('dosesPerPackage', 'Doses Per Package', 'required');
		$this->form_validation->set_rules('borrowerID', 'Borrower', 'callback_CheckDropdownSelect[$aBorrowerID]');
		$this->form_validation->set_message('CheckDropdownSelect', 'Please Select A Borrower From The List');


		$data['borrowerList'] = $borrower->DisplayBorrowers();

		//validation
		if($this->form_validation->run() === FALSE) //If validation fails, return the form
		{
			//Load view
			$this->load->view("vac-header");
			$this->load->view("vaccine/loanreturn", $data);
			$this->load->view("vac-footer");
		}
		else //If validation succeeds, check the ndc num to see if it matches multiple vaccines from the FDA_DRUG_PACKAGE table
		{
			//echo "Here is the passed ndc: ".$this->input->post('linBarcode');
			$this->session->expireDate = $this->input->post('expireDate');
			$this->session->lotNum = $this->input->post('lotNum');
			$this->session->PackageQty = $this->input->post('packageQty');
			$this->session->DosesPerPackage = $this->input->post('dosesPerPackage');
			$this->session->PageOrigin = 'loanreturn';
			$this->session->BorrowerID = $this->input->post('borrowerID');


			$barcodeArray = $vaccine->ParseBarcode($this->input->post('linBarcode'), TRUE);
			$ndc = $barcodeArray['ndc'];

			$qryArray = $vaccine->GetVaccine($ndc, FALSE);

			if(count($qryArray) > 1)
			{
				$this->SelectVacFromList($ndc);

			}
			else
			{		
				//Input transaction into database
				$transData = $vaccine->LoanReturn($qryArray[0]->DrugID, $this->session->BorrowerID);//$this->input->post('borrowerID'));

				$transData['transSummary'] = $this->table->generate($transData['tblSummary']);

				//Provide summary of the the transaction
				$this->load->view('vac-header');
				$this->load->view('vaccine/loanreturn-success', $transData);
				$this->load->view('vac-footer');
			}
		}

	} //End LoanReturn()


	public function SelectVacFromList($anNDC) // $originPage = null) //$originPage, $getVaccineTrueFalse)
	{

		//echo "This is the origin value:".$this->session->PageOrigin."<br/>";
		//echo "This is the WasAnOrder value:".$this->session->WasAnOrder;

		$vaccine = new Vaccine();

		//Load validation helpers
		$this->load->helper('form');
		$this->load->library('form_validation');

		$data['vacList'] = $vaccine->GetVaccine($anNDC, $this->session->WasAnOrder);
		//$data['selectVal'] = $this->input->post('vaccineList');
		$data['title'] = "Select From List";

		$this->session->NDC = $anNDC;

		$data['ndc'] = $this->session->NDC;


		$this->form_validation->set_rules('vaccineList', 'Select Vaccine Description', 'callback_CheckDropdownSelect[$drugID]');
		$this->form_validation->set_message('CheckDropdownSelect', "Select A Vaccine Description");


		if($this->form_validation->run() == FALSE)
		{
			//reload the form
			$this->load->view('vac-header');
			$this->load->view('vaccine/select-vaccine-from-list', $data);
			$this->load->view('vac-footer');
		}
		else
		{
			switch ($this->session->PageOrigin)
			{
				case 'invoice':
					//Input the data into the database (call the "Order" method in the Vaccine model)
					//echo "The salendc is: $anNDC";
					//echo "The drug id should be: ".$this->input->post('vaccineList');
					$transData = $vaccine->Order($this->input->post('vaccineList'));

					$data['transid'] = $transData['TransID'];
					$data['timestamp'] = $transData['TransDate'];
					$data['drugID'] = $transData['DrugID'];

					$currentTrans = $vaccine->GetTransaction($transData['TransID'], $this->session->PageOrigin, FALSE);
					$vacSummary = $vaccine->GetSingleVacSum($transData['DrugID']);

					$data['transSummary'] = $this->table->generate($currentTrans);
					//$data['vacSummary'] = $vacSummary[0];

					//Load confirmation page
					$this->load->view('vac-header');
					$this->load->view('Vaccine/Invoice-Success', $data);
					$this->load->view('vac-footer');
					break;


				case "administer":
					//Input data into database
					$vaccine->Administer($this->input->post('vaccineList'));

					//Load confirmation page
					$this->load->view('vac-header');
					$this->load->view('Vaccine/Adminster-Success');
					$this->load->view('vac-footer');
					break;


				case "loanout":
					//Input data into the database
					$transSum = $vaccine->LoanOut($this->input->post('vaccineList'), $this->session->BorrowerID);

					$data['borrowerID'] = $this->session->BorrowerID;
					$data['transSummary'] = $this->table->generate($transSum['tblSummary']);
					// echo "<br/>Here's what's in the data arry after tbl fctn is run...:[<br/>";
					// var_dump($data['transSummary']);
					// echo "<br/>]";

					//Confirmation page
					$this->load->view('vac-header');
					$this->load->view('vaccine/loanout-success', $data);
					$this->load->view('vac-footer');
					break;


				case "loanreturn":
					//Input data into the database
					$transData = $vaccine->LoanReturn($this->input->post('vaccineList'), $this->session->BorrowerID);

					$data['transSummary'] = $this->table->generate($transData['tblSummary']);

					//Confirmation page
					$this->load->view('vac-header');
					$this->load->view('Vaccine/loanreturn-success', $data);
					$this->load->view('vac-footer');
					break;


				default:
					break;
			}

		}
	 } //End SelectVacFromList

	//Callback validation method for Order form
	public function CheckDropdownSelect($anOptionValue) //CheckVacSelect($strDrugID)
	{
		if($anOptionValue == '-1' or $anOptionValue == null)
		{
			// echo "False";
			return FALSE;
		}
		else
		{
			// echo "True";
			return TRUE;
		}
	} //End CheckDropdownSelect



}

?>