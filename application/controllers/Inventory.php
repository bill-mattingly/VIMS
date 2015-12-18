<?php

class Inventory extends CI_Controller
{

	public function __construct(){
		parent::__construct();

		//Load libraries that may be useful to all controller methods
		$this->load->database(); //Loads the database library
		$this->load->library('Ion_auth'); //Loads the Ion_auth library
		$this->load->library('table'); //Helper class to create HTML tables
		$this->load->library('form_validation');
		$this->load->library('session'); //Helper class to load
		$this->load->helper('form');
		$this->load->helper('url'); //Helper class to create anchor links
		$this->load->model('inventory/vaccine'); //Loads the vaccine model
		$this->load->model('inventory/borrower'); //Loads the borrower model
		$this->load->model('inventory/reports'); //Loads the reports model
		//$this->output->enable_profiler(TRUE);


		//Modify the default CodeIgniter table template to include Bootstrap classes
		//See CodeIgiter "HTML Table Class" in CI documentation
		$template = array(
				'table_open' => "<table class='table table-bordered table-striped table-hover'>"
			);
		$this->table->set_template($template);

		if(!$this->ion_auth->logged_in())
		{
			//redirect to login page
			redirect('auth/login', 'refresh');
		}

	}

	public function Index()
	{
		$aReport = new Reports();		

		$tableRows = $aReport->InventorySummary();

		//Get the array keys needed to access the object properties in $tableRows array (its an array of objects)
		if($tableRows[0] == "headerOnly") //If first array index position contains "headerOnly", then the array only contains an object with a list of header name aliases (from the sql query). The object is located in the second position in the array (index position 1)
		{
			$firstRow = get_object_vars($tableRows[1]);
		}
		else
		{
			$firstRow = get_object_vars($tableRows[0]); //get_object_vars() gets all properties of an object & returns an associative array
		}


		$arrayKeys = array_keys($firstRow); //array_keys() function needs an array as its first object; we want the array of keys for each row object rather than the array of all row objects (thus we select one of the rows to get the keys for each result object)

			
		//create html table string with the $tableRows variable
		$tableString = "";

			//Begin table
			$tableString .= "<table class='table table-bordered table-striped table-hover'>"; //Open table

			//Table header
			$tableString .= "<thead>";
			$tableString .= "<tr>"; //Begin table header row
				foreach($arrayKeys as $key)
				{
					$tableString.= "<th>$key</th>";
				}

			$tableString .= "</tr>";
			$tableString .= "</thead>"; //End table header


		if($tableRows[0] != "headerOnly") //Build table body if row 0 == "headerOnly" - this value is assigned in the Reports class (InventorySummary method) if query result doesn't have any results or all lot numbers in the results have 0 net inventory
		{
			//Table body
			$tableString .= "<tbody>"; //Begin table body
			foreach($tableRows as $row)
			{
				$tableString .= "<tr>"; //Begin row
				foreach($arrayKeys as $objectProperty) //for($counter=0; $counter<=count($arrayKeys); $counter++) //populate table row
				{
					$tableString .= "<td>".$row->$objectProperty."</td>"; //[$arrayKeys[$counter]]."</td>"; //$arrayKeys contains the keys which refer to each column's name. Remember, $row contains an object, so you have to access its as an object rather than as an array
				}

				$tableString .= "</tr>"; //End row
			} 
			$tableString .= "</tbody>"; //End table body

		} //End if


		//Close table
		$tableString .= "</table>";
		


		$data['tblSummary'] = $tableString; //Assign html code to build inventory table to variable used by the view
	
		//Load view
		$this->load->view('vac-header');
		$this->load->view("vaccine/index", $data);
		$this->load->view('vac-footer');

	} //End Index()


	public function ScanInvoice()
	{
		//Vaccine variables
		$barcodeArray = null;
		$vaccineArray = null;
		$vaccine = new Vaccine();

		//Load helpers
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('vaccine');

		//Set validation rules
		$this->form_validation->set_rules('barcode', 'Barcode', 'required');

		if($this->form_validation->run() === FALSE)
		{
			$this->load->view('vac-header');
			$this->load->view('vaccine/scan-barcode');
			$this->load->view('vac-footer');
		}
		else
		{
			//Store form variables in session variables
			$this->session->barcode = $this->input->post('barcode');

			$barcodeArray = $vaccine->ParseBarcode($this->session->barcode, TRUE);
			$vaccineArray = $vaccine->GetVaccine($barcodeArray['ndc10'], FALSE);

			if(count($vaccineArray) < 1)
			{
				//If $vaccineArray's count = 0, it means the user scanned a "Use" rather than "Sale" barcode & thus the incorrect database query was used
				//Display error message to user
				$this->session->error = "Please Scan a Box/Carton Barcode Rather than a Vial Barcode For Loans and Invoices";

				//Reload scan-barcode page
				redirect('Inventory/ScanInvoice', 'refresh');
			}

			//Determine whether multiple vaccines share the same ndc or whether it's one vaccine
			//If multiple vaccines, go to the "selectvaccine" method
			elseif(count($vaccineArray) > 1)
			{
				$this->session->barcodeArray = $barcodeArray;
				$this->session->vaccineArray = $vaccineArray;

				redirect('Inventory/SelectVacFromList');
			}
			else
			{
				$barcodeArray['drugID'] = $vaccineArray[0]->DrugID;
				$barcodeArray['clinicCost'] = $vaccineArray[0]->Drug_Cost;
				$barcodeArray['trvlPrice'] = $vaccineArray[0]->Trvl_Chrg;
				$barcodeArray['refugeePrice'] = $vaccineArray[0]->Refugee_Chrg;

				//Store barcodeArray and vaccineArray in session variables
				$this->session->barcodeArray = $barcodeArray;
				$this->session->vaccineArray = $vaccineArray;

				//Call the method for the action the user requested (ex. "Invoice")
				redirect('Inventory/Invoice');

			} //End else
		} //End else
	} //End ScanInvoice()


	public function ScanAdminister()
	{
		//Vaccine variables
		$barcodeArray = null;
		$vaccineArray = null;
		$vaccine = new Vaccine();

		//Load helpers
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('vaccine');

		//Set validation rules
		//(Check to make sure the scanned barcode actually exists in inventory
		// to prevent administering a drug the clinic doesn't have)
		$this->form_validation->set_rules('barcode', 'Barcode', "callback_CheckBarcodeInventory[FALSE]"); //"FALSE" is the 2nd arguement to the callback function. The value is TRUE/FALSE based on whether the scanned barcode will contain a "Sale" (TRUE) or "Use" (FALSE) NDC number (a "carton" or "vial" barcode respectively). Administered vaccines will scan the vial barcode & thus contain a "Use" ndc number (so it is "FALSE" that the ndc is a "Sale" ndc number));
		

		if($this->form_validation->run() === FALSE)
		{
			$this->load->view('vac-header');
			$this->load->view('vaccine/scan-barcode');
			$this->load->view('vac-footer');
		}
		else
		{
			//Check count of inventoryArray session variable
			if(count($this->session->inventoryArray) > 1) //If more than one vaccine in inventory, then redirect to SelectVacFromList method
			{
				redirect('Inventory/SelectVacFromList', 'refresh');
			}
			else //If only one vaccine in inventory, redirect to Administer method (there is at least 1 in inventory b/c the validation method checked this)
			{
				redirect('Inventory/Administer', 'refresh');
			}

		} //End else
	} //End ScanAdminister()

	public function ScanLoanOut()
	{
		//Vaccine variables
		$barcodeArray = null;
		$vaccineArray = null;
		$vaccine = new Vaccine();

		//Load helpers
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('vaccine');

		//Set validation rules
		$this->form_validation->set_rules('barcode', 'Barcode', "callback_CheckBarcodeInventory[TRUE]");

		if($this->form_validation->run() === FALSE)
		{
			$this->load->view('vac-header');
			$this->load->view('vaccine/scan-barcode');
			$this->load->view('vac-footer');
		}
		else
		{
			//Store form variables in session variables
			$this->session->barcode = $this->input->post('barcode');

			//Parse Barcode & Get Vaccine
			$barcodeArray = $vaccine->ParseBarcode($this->session->barcode, TRUE);
			$vaccineArray = $vaccine->GetVaccine($barcodeArray['ndc10'], FALSE);

			if(count($vaccineArray) < 1)
			{
				//If $vaccineArray's count = 0, it means the user scanned a "Use" rather than "Sale" barcode & thus the incorrect database query was used
				//Display error message to user
				$this->session->error = "Please Scan a Box/Carton Barcode Rather than a Vial Barcode For Loans and Invoices";

				//Reload scan-barcode page
				redirect('Inventory/ScanLoanOut', 'refresh');
			}

			//Determine whether multiple vaccines share the same ndc or whether it's one vaccine
			//If multiple vaccines, go to the "selectvaccine" method
			elseif(count($vaccineArray) > 1)
			{
				$this->session->barcodeArray = $barcodeArray;
				$this->session->vaccineArray = $vaccineArray;

				redirect('Inventory/SelectVacFromList');
			}
			else
			{
				$barcodeArray['drugID'] = $vaccineArray[0]->DrugID;
				$barcodeArray['clinicCost'] = $vaccineArray[0]->Drug_Cost;
				$barcodeArray['trvlPrice'] = $vaccineArray[0]->Trvl_Chrg;
				$barcodeArray['refugeePrice'] = $vaccineArray[0]->Refugee_Chrg;

				//Store barcodeArray and vaccineArray in session variables
				$this->session->barcodeArray = $barcodeArray;
				$this->session->vaccineArray = $vaccineArray;

				//Call the method for the action the user requested (ex. "Invoice")
				redirect('Inventory/LoanOut');

			} //End else
		} //End else
	} //End ScanLoanOut()


	public function SelectVacFromList()
	{
		//Load validation helpers
		$this->load->helper('form');
		$this->load->library('form_validation');

		//Data to pass to form
		if($this->session->theAction == 'ScanInvoice')
		{
			$data['vacList'] = $this->session->vaccineArray;
		}
		else
		{
			$data['vacList'] = $this->session->inventoryArray;
		}

		//Form validation
		$this->form_validation->set_rules('vaccineList', 'Select Package Description', 'callback_CheckVacSelect'); //'Select Vaccine Description', 'callback_CheckDropdownSelect[$drugID]');
		

		if($this->form_validation->run() == FALSE)
		{
			//Reload the form
			$this->load->view('vac-header');
			$this->load->view('vaccine/select-vaccine-from-list', $data);
			$this->load->view('vac-footer');
		}
		else
		{
			$arrayIndex = $this->input->post('vaccineList');

			if($this->session->theAction == 'ScanInvoice')
			{
				$vaccineArray = $this->session->vaccineArray[$arrayIndex];
				$this->session->vaccineArray = $vaccineArray;
			}
			else
			{
				$inventoryArray = $this->session->inventoryArray[$arrayIndex];
				$this->session->inventoryArray = $inventoryArray; //overwrites the inventory array with the vaccine in the inventory array selected by the user
			}

			switch ($this->session->theAction)
			{
				case 'ScanInvoice':
					redirect('Inventory/Invoice');
					break;

				case 'ScanAdminister':
					redirect('Inventory/Administer');
					break;

				case 'ScanLoanOut':
					redirect('Inventory/LoanOut');
					break;

				case 'ScanLoanReturn':
					redirect('Inventory/LoanReturn');
					break;

				default:
					echo "Default";
					break;
			} //End switch
		} //End else
	 } //End SelectVacFromList


	public function Invoice()
	{
		//Method variables
		$vaccine = new Vaccine();

		//Load helpers
		$this->load->helper('form');
		$this->load->library('form_validation');	

		//Data to pass to form
	    $data['title'] = 'Add a Vaccine Invoice';
	    $data['ndc10'] = $this->session->barcodeArray['ndc10'];
	    $data['ndc11'] = $this->session->barcodeArray['ndc11'];

	    $data['lotNum'] = $this->session->barcodeArray['lotNum'];
	    $data['expireDate'] = $this->session->barcodeArray['expireDate'];

	    $data['clinicCost'] = $this->session->vaccineArray->{'Clinic Cost'};
	    $data['numDosesPackage'] = $this->session->vaccineArray->{'Number Doses Package'};

	    //Form validation
	    $this->form_validation->set_rules('expireDate', 'Expire Date', 'required');
		$this->form_validation->set_rules('lotNum', 'Lot Number', 'required');
		$this->form_validation->set_rules('clinicCost', 'Cost Per Dose', 'required');
		$this->form_validation->set_rules('packageQty', 'Package Qty', 'required'); //Come back & set custom validation method to prevent invalid data


	    if ($this->form_validation->run() === FALSE)
	    {
	    	//Reload form
	    	$this->load->view('vac-header');
	    	$this->load->view('vaccine/invoice', $data);
			$this->load->view('vac-footer');
	    }
	    else
	    {
			//Process date value into MySQL yyyy-mm-dd format
	    	$aDate = $this->input->post('expireDate');
	    	$aDate = substr($aDate, 6, 4)."-".substr($aDate, 0, 2)."-".substr($aDate, 3, 2);

	    	//Store form data in a session array (CodeIgniter syntax; CI syntax makes use of PHP $_SESSION global var)
			$this->session->expireDate = $aDate;
			$this->session->lotNum = $this->input->post('lotNum');
			$this->session->clinicCost = $this->input->post('clinicCost');
			$this->session->packageQty = $this->input->post('packageQty');
			$this->session->dosesPerPackage = $this->input->post('dosesPerPackage');

			//Insert the selected vaccine in the database & give user feedback
    		$transArray = $vaccine->OrderInvoice($this->session->vaccineArray->{'Drug ID'});

    		$data['transid'] = $transArray['TransID'];

			//Timestamp value is stored in database in UTC time
			//To return it to the user, set the local timezone & then assign the UTC timestamp value to a new variable
			//date_default_timezone_set("America/New_York");
			//$data['timestamp'] = date("Y-m-d h:i:sa", $transArray['TransDate']);
			$data['timestamp'] = $transArray['TransDate'];
			$data['clinicCost'] = $this->session->vaccineArray->{'Clinic Cost'};
			$data['tblSummary'] = $this->table->generate($transArray['tblSummary']);

			//Load view
			$this->load->view("vac-header");
			$this->load->view("vaccine/invoice-success", $data);
			$this->load->view("vac-footer");

	    } //End else
	} //End Order()


	public function Administer()
	{
		//Method variables
		$vaccine = new Vaccine();

		//Load helpers
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('table');


		//Data to pass to forms		
		$vaccineArray = $vaccine->GetSingleVacInventory($this->session->inventoryArray[0]->{'Drug ID'});
		$data['vaccineArray'] = $vaccineArray;

		$data['trvlPrice'] = $vaccineArray[0]->{"Travel Patient Chrg"};
		$data['refugeePrice'] = $vaccineArray[0]->{"Refugee Patient Chrg"};
		$data['ndc10'] = $vaccineArray[0]->{"Dose NDC10"};
		$data['clinicCost'] = $vaccineArray[0]->{"Clinic Cost"};

		//Calculate $maxDoseQty based on how many doses are available for each lot number
		$dataAttributes = "";

		$maxDoseAndPackageArray;

		foreach($vaccineArray as $lotQty)
		{
			$dataAttributes .= "data-".$lotQty->{'Lot Number'}."=".$lotQty->{'Net Doses'}." ";
			
			for($counter = 0; $counter <= 1; $counter++) //Set up 2 dimensional array
			{
				if($counter == 0)
				{
					$maxDoseAndPackageArray[$lotQty->{'Lot Number'}][0] = $lotQty->{'Net Doses'};	
				}
				else
				{
					$maxDoseAndPackageArray[$lotQty->{'Lot Number'}][1] = null;
				}
			}
		}

		$this->session->MaxDoseAndPackageArray = $maxDoseAndPackageArray;

		$data['dataAttributes'] = $dataAttributes; //A string listing the "Net Doses" quantities by the lot numbers listed in the dropdown list in the Administer.php view

		//Validation Rules
		$this->form_validation->set_rules('lotNumList', 'Lot Number', 'callback_CheckLot');
		$this->form_validation->set_rules('clinicCost', 'Clinic Cost', 'required');
		$this->form_validation->set_rules('customerChrg', 'Customer Charge', 'required');
		$this->form_validation->set_rules('doseQty', 'Dose Quantity', "callback_CheckDoseQty");


		if($this->form_validation->run() === FALSE)
		{
			$this->load->view('vac-header');
			$this->load->view("vaccine/administer", $data);
			$this->load->view('vac-footer');
		}
		else
		{
			//Data from Form fields
			$this->session->lotNum = $this->input->post('lotNumList');
			$this->session->expireDate = $this->input->post('expireDate');
			$this->session->doseQty = $this->input->post('doseQty');
			$this->session->customerChrg = $this->input->post('customerChrg');

			$transData = $vaccine->Administer($this->session->inventoryArray[0]->{'Drug ID'});

			//Data to pass to forms
			$data['tblSummary'] = $this->table->generate($transData['tblSummary']);

			//Display message to user success view
			$this->load->view("vac-header.php"); //Header file
			$this->load->view("vaccine/administer-success", $data);
			$this->load->view("vac-footer.php"); //Footer file

		} //End Else
	} //End Administer()


	public function LoanOut()
	{
		//Method variables
		$vaccine = new Vaccine();
		$borrower = new Borrower();

		//Load helpers
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('table');

		//Data for form
		$selectedVaccine = $this->session->inventoryArray; //Store the selected vaccine object from the session variable into a local variable

		$vaccineArray = $vaccine->GetSingleVacInventory($selectedVaccine->{'Drug ID'}); //Provide DrugID property to GetSingleVacInventory method
		$data['vaccineArray'] = $vaccineArray;

		//Data for form
		$data['listOfBorrowers'] = $borrower->DisplayBorrowers();
		$data['ndc10'] = $this->session->barcodeArray['ndc10'];
		$data['clinicCost'] = $vaccineArray[0]->{'Clinic Cost'};
		$data['maxDoses'] = $vaccineArray[0]->{'Net Doses'};


		//Validation rules
		$this->form_validation->set_rules('lotNumList', 'Lot Number', 'callback_CheckLot');
		$this->form_validation->set_rules('borrowerID', 'Borrower', 'callback_CheckBorrowerList');
		$this->form_validation->set_rules('loanSigner', 'Loan Signer', 'required');

		if($this->form_validation->run() === FALSE)
		{
			//Load view
			$this->load->view("vac-header");
			$this->load->view("vaccine/loanout", $data);
			$this->load->view("vac-footer");
		}
		else
		{
			//Store form values
			$this->session->lotNum = $this->input->post('lotNumList');
			$this->session->expireDate = $this->input->post('expireDate');
			$this->session->BorrowerID = $this->input->post('borrowerID');
			$this->session->DosesPerPackage = $this->input->post('dosesPerPackage');
			$this->session->PackageQty = $this->input->post('packageQty');

			//Enter data into database
			$drugID = $selectedVaccine->{'Drug ID'};
			$transData = $vaccine->loanout($drugID, $this->session->BorrowerID, $this->input->post('loanSigner'));
			

			//Data for summary page
			$data['borrowerID'] = $this->session->BorrowerID;
			$data['tblSummary'] = $this->table->generate($transData['tblSummary']);

			//Return summary of transaction for user
			$this->load->view('vac-header');
			$this->load->view('vaccine/loanout-success', $data);
			$this->load->view('vac-footer');

		} //End else
	} //End LoanOut()

	public function LoanReturn()
	{
		//Loan view
		$this->load->view('vac-header');
		$this->load->view('vaccine/loanreimburse');
		$this->load->view('vac-footer');
	} //End LoanReturn()

	public function EditTransactions()
	{
		//Note: AJAX processes the request to build the transaction table
		$this->load->view("vac-header");
		$this->load->view("vaccine/edit-transactions");
		$this->load->view("vac-footer");

	} //End EditTransactions()

	public function Reports()
	{
		$this->load->view('vac-header');
		$this->load->view('Vaccine/Reports');
		$this->load->view('vac-footer');
	} //End Reports()

	public function UpdatePriceAndCost()
	{
		//Form validation helpers
		$this->load->helper('form');
		$this->load->library('form_validation');

		//Validation rules
		//$this->form_validation->set_rules('vacBarcode', 'Vaccine Barcode Type', array('required' => 'Choose the Type of Container Being Scanned'));


		if($this->form_validation->run() == FALSE)
		{
			$this->load->view('vac-header');
			$this->load->view('Vaccine/UpdatePriceAndCost');
			$this->load->view('vac-footer');
		} //End If
		else
		{
			$aVaccine = new Vaccine();
			$aBarcodeArray = $aVaccine->ParseBarcode($this->input->post('barcode'));

			if($this->input->post('cartonBarcode') == TRUE)
			{
				$aVaccineArray = $aVaccine->GetVaccine($aBarcodeArray['ndc10'], FALSE);

				echo "Carton Barcode";
			} //End If
			else
			{
				$aVaccineArray = $aVaccine->GetVaccine($aBarcodeArray['ndc10'], TRUE);
				echo "Vial Barcode";
			} //End Else


			$data['vaccineData'] = $aVaccineArray;//vaccine data

			$this->load->view('vac-header');
			$this->load->view('Vaccine/UpdatePriceAndCost', $data);
			$this->load->view('vac-footer');
		} //End Else

	} //End UpdatePriceAndCost

	public function ManageUsers()
	{
		$this->load->view('vac-header');
		$this->load->view('vaccine/manage-users');
		$this->load->view('vac-footer');
	} //End ManageUsers()

/*******************************/
/*BEGIN CALLBACK METHOD SECTION*/
/*******************************/
	//Tests the lot number selected from the dropdown on the Administer & Loan Out pages
	public function CheckLot($lot)
	{
		if($lot == -1)
		{
			$this->form_validation->set_message('CheckLot', 'Select a Lot Number from the List');
			return false;
		}
		else
		{
			$this->session->selectedLot = $lot; //Stores the Lot Number from the select control. This value is later used in the validation method for the 'Doses Administers' / 'Doses Loaned Out' control (the lot number is used to get the maximum dose quantity from the Lot Number array)
			return true;
		}
	} //End CheckLot()

	//Tests the select element from the dropdown on the SelectVacFromList controller method
	public function CheckVacSelect($indexVal)
	{
		if($indexVal == -1)
		{
			$this->form_validation->set_message("CheckVacSelect", 'Select a Description from the List');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	} //End CheckVacSelect()

	//Tests the select element from the dropdown on the LoanOut controller method
	public function CheckBorrowerList($borrowerID)
	{
		if($borrowerID == -1)
		{
			$this->form_validation->set_message('CheckBorrowerList', 'Select a Borrower from the List');
			return false;
		}
		else
		{
			return true;
		}
	} //End CheckBorrowerList()

	//Checks to see if the scanned barcode is in inventory or not (used to check the scanned codes for the Administer & LoanOut features - this method is irrelevant to the Invoice feature)
	//The 2nd argument, $isSaleNDC, is necessary b/c sometimes the NDC numbers used for the same vaccine is different between the box/container & the individual vial
	public function CheckBarcodeInventory($aBarcode, $isSaleNDC = null)
	{
		$aVaccine = new Vaccine();
		$aVaccineArray = null;


		//Check to make sure values have been given to function parameters
		//Get barcode
		$scannedBarcode = trim($aBarcode);
		
		if($scannedBarcode == null) //If the user didn't scan a barcode, return false
		{
			$this->form_validation->set_message('CheckBarcodeInventory', "Please Scan a Barcode");
			return FALSE;
		}
		elseif($isSaleNDC == null) //If the type of NDC (box or vial) isn't specificed, return false (need this value to determine which column of NDC values to check the barcode against in the FDA_DRUG_Package table)
 		{
			$this->form_validation->set_message("CheckBarcodeInventory", "An error occurred");
			return FALSE;
		}


		//Get the ndc number from the barcode
		//NDC number contained within $aBarcodeArray
		//Access ndc number with: $aBarcodeArray['ndc10']
		$aBarcodeArray = $aVaccine->ParseBarcode($scannedBarcode, $isSaleNDC);
		
		//Get all vaccines with the barcode's ndc
		if($isSaleNDC == 'TRUE') //If SaleNDC is true, then the carton ndc value is passed to GetVaccine (the 2nd argument in that function is FALSE b/c it the 2nd argument asks if the vaccine was administered - so in this case, FALSE)
		{
			$aVaccineArray = $aVaccine->GetVaccine($aBarcodeArray['ndc10'], FALSE);
		}
		else //If SaleNDC is false, then a vial ndc value is passed to GetVaccine (& the 2nd argument, $vacAdministerd should equal TRUE)
		{
			$aVaccineArray = $aVaccine->GetVaccine($aBarcodeArray['ndc10'], TRUE);
		}

		$drugIDArray; //Declare array variable

		//If the user scanned the barcode of some other product or entered random numbers,
		//return FALSE & tell the user (this will trigger if the parsed NDC value can't be found in the 'FDA Package' table)
		if($aVaccineArray == null) //count($aVaccineArray) < 1) //Occurs if the query doesn't find any vaccine in inventory
		{
			$this->form_validation->set_message('CheckBarcodeInventory', 'Please Scan a Valid Vaccine Barcode');
			return FALSE;
		}
		elseif(count($aVaccineArray) == 1) //Evaluates to true if a CartonNDC only returned 1 drug id or if a the scanned barcode was a VialNDC
		{
			if($isSaleNDC == 'TRUE') //If the scanned barcode came from a carton, then use the DrugID that came from the 1 record found in the database search
			{
				$drugIDArray[0] = $aVaccineArray[0]->{'Drug ID'};
			}
			else //If the scanned barcode came from a vial, then use the DrugID that came from the carton (rather than the vial's DrugID)
			{
				/*
				Need to get the PackageNDC to check inventory rather than using the Use/Vial NDC.
				The reason is that when a vaccine is administered, the vial ndc is different (sometimes)
				than the carton ndc. So, to get the correct inventory levels, you need to capture the use/vial ndc (through the barcode),
				then locate the SaleNDC for that UseNDC. After identifying the SaleNDC (which is more generic than the Vial NDC), search
				the database for all records with the SaleNDC. A couple records will be returned in the query result set.
				You need to select the minimum drug id value out of that result set. The minimum drug id will be the drug id value of the carton.
				You need the drug id of the carton b/c inventory is added through the "Invoice" feature based on the drug id value of the carton.
				*/

				$drugIDArray[0] = $aVaccine->GetPackageDrugID($aVaccineArray[0]->{'Drug ID'});
			} //End else
		} //End elseif

		else //If $aVaccineArray contains more than 1 vaccine (& thus more than 1 DrugID), loop through the $aVaccineArray to create a DrugID array.
			 //This array will be used to check inventory for each DrugID. If a DrugID in the array doesn't have inventory, then that drug won't be
			 //listed in the dropdown in the SelectVacFromList page (b/c there isn't any inventory of that drug).
		{
			$counter = 0;

			foreach($aVaccineArray as $vaccine)
			{
				$drugIDArray[$counter] = $vaccine->{'Drug ID'};
				$counter++;
			}

		} //End else

		$inventoryArray = $aVaccine->GetMultiVacInventory($drugIDArray);
		
		if(count($inventoryArray) >= 1) //If there are 1 or more lot numbers for the same DrugID, then do the following
		{
			//Store the vaccine array & barcode array in a session variable (allow the controller method (rather than the callback method) to finish processesing)
			$this->session->barcodeArray = $aBarcodeArray;
			$this->session->vaccineArray = $aVaccineArray;
			$this->session->inventoryArray = $inventoryArray;

			return TRUE;
		}
		else //If none of the IDs are in inventory, return false
		{
			$this->form_validation->set_message('CheckBarcodeInventory', "The Vaccine is not Currently in Inventory. Please First Add to Inventory or Scan a Different Barcode.");
			return FALSE;
		}

	} //End CheckBarcodeInventory()

	//Check the number of doses from the Administer or LoanOut forms against the number of doses listed in the database
	public function CheckDoseQty($numDoses) //$numDoses comes directly from the input control
	{
		$index = $this->session->selectedLot;
		$maxDoseAndPackageArray = $this->session->MaxDoseAndPackageArray;

		$maxDoseQty = $maxDoseAndPackageArray[$index][0]; //Lot Index session variable will always be 1 more than the array b/c the select element has "Select Lot Number" in index position 0

		$packageQty = $maxDoseAndPackageArray[$index][1];
		
		$totalNumDoses; //Variable to store the number of doses attempting to be loaned or administered

		//Get total number of doses trying to be administered or loaned out by the user
		if($packageQty == null) //Means the user is administering the vaccine (rather than loaning out). This is b/c the user won't administer a "package" of vaccine (only individual doses)
		{
			$totalNumDoses = $numDoses;
		}
		else //If $numPackages has a value, multiply $numPackages & $numDoses to get the $totalNumDoses being requested by the user
		{
			$totalNumDoses = $numDoses * $packageQty;
		}

		//Check the $totalNumDoses requested by the user against inventory for the $lotNumber (eventually need to just check against the total inventory (regardless of lot number), but need to check against lot number for the mean time)
		if($totalNumDoses > $maxDoseQty)
		{
			$this->form_validation->set_message('CheckDoseQty', 'Reduce Dose Quantity. Exceeded the Number of Doses Available in Inventory');
			return FALSE;
		}
		elseif ($totalNumDoses < 1) 
		{
			$this->form_validation->set_message('CheckDoseQty', 'Increase Quantity. Must Administer at Least 1 Dose');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	} //End CheckDoseQty()

	public function CheckUserRole($indexVal)
	{
		if($indexVal == -1) //'-1' = the default & is not a valid role
		{
			$this->form_validation->set_message('CheckUserRole', 'Role must be a valid value');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	} //End CheckUserRole()

/*****************************/
/*END CALLBACK METHOD SECTION*/
/*****************************/


/************************/
/* Begin AJAX Functions */
/************************/

//AJAX call from vac-header.php view
//Function is called whenever an "anchor" element is clicked (was initially created just to capture the a tags in the navigation header: "Add to Inventory", "Administer Vaccine", "Loan Out Vaccine")
//The only thing needed to make the function run on a new anchor link is to add an id attribute:
// "ScanInvoice", "ScanAdminister", or "ScanLoanOut" so that the "$this->session->action" variable is set to the right value
function ScanBarcodeAction()
{
	//Store selected action
	$selectedAction = $this->input->post('action'); //"Action" is the "key" used in the AJAX request to access the data in the key/value POST array. It isn't the id of any page element, it is just the id used to access the AJAX request

	//Store value as session variable
	$this->session->theAction = $selectedAction;

	echo json_encode($this->session->theAction);
}

function SearchProprietaryName()
{
	//Store search string in variable
	$vac = $this->input->post('proprietaryName');

	//Search database   //, COUNT(PR.PROPRIETARYNAME) AS 'Count'
	$sql = "SELECT PR.PROPRIETARYNAME AS 'Name'
			FROM `fda_drug_package` PA INNER JOIN `fda_product` PR ON PA.PRODUCTID = PR.PRODUCTID
			WHERE PR.PROPRIETARYNAME LIKE '$vac%'
			GROUP BY PR.PROPRIETARYNAME
			ORDER BY PR.PROPRIETARYNAME ASC";

	$result = $this->db->query($sql);
	$resultArray = $result->result();
	
	$ajaxResult = array();

	foreach($resultArray as $row)
	{
		$key = $row->Name;
		$value = $row->Name; //$row->Count;
		$ajaxResult[$key] = $value;
	}

	//Return result
	if(count($ajaxResult) > 0)
	{
		echo json_encode($ajaxResult);
	}	
	else
	{
		http_response_code(500); //No results returned, thus searched string doesn't exist in database

		//By not echoing a JSON encoded response, the calling jQuery AJAX function's error function is triggered
	}
	
} //End SearchProprietaryName()

function SearchBarcode()
{	
	//Need to have this function redirect somewhere if a user tries to
	//access it directly

	//Method variable
	$vaccine = new Vaccine();


	//Store ajax send request data in variable
	$scannedBarcode = $this->input->post('barcodeString');
	$codeType = $this->input->post('barcodeType');
	$isSaleNDC = null;
	$ndc = null;

	//Query database
	if ($codeType == 'carton')
	{
		$field = "SALENDC10";
		$isSaleNDC = TRUE;
	}
	elseif ($codeType == 'vial')
	{
		$field = "USENDC10";
		$isSaleNDC = FALSE;
	}

	//Process the scanned barcode into an NDC number
	$barcodeArray = $vaccine->ParseBarcode($scannedBarcode, $isSaleNDC);
	$ndc = $barcodeArray['ndc10'];

	// *******
	// Write the code for this part
	// *******


	//, COUNT(PR.PROPRIETARYNAME) as Count
	$sql = "SELECT PR.PROPRIETARYNAME as Name
			FROM `fda_drug_package` PA INNER JOIN `fda_product` PR ON PA.PRODUCTID = PR.PRODUCTID
			WHERE PA.".$field." = '$ndc'
			GROUP BY PR.PROPRIETARYNAME
			ORDER BY PR.PROPRIETARYNAME ASC";

	$query = $this->db->query($sql);
	$resultArray = $query->result();

	$ajaxResult = array();

	foreach($resultArray as $vac)
	{
		$key = $vac->Name;
		$value = $vac->Name; //$vac->Count;
		$ajaxResult[$key] = $value;
	}	

	//return query result to ajax request
	if(count($ajaxResult) > 0)
	{
		echo json_encode($ajaxResult);
	}
	else
	{
		http_response_code(500); //Sends server error message

		//By not echoing a json_encoded result, the jQuery ajax function's error function will be triggered

	}
} //End SearchBarcode()

//Used by UpdatePriceAndCost view
function GetVacCostAndPrice()
{
	//Store ajax variable
	$vac = $this->input->post('selectedVac');

	//Search database
	$sql = "SELECT PR.PROPRIETARYNAME as 'Name', PA.DRUG_COST as 'Cost', PA.TRVL_CHRG as 'Trvl_Chrg', PA.Refugee_Chrg as 'Refugee_Chrg'
			FROM `fda_drug_package` PA INNER JOIN `fda_product` PR ON PA.PRODUCTID = PR.PRODUCTID
			WHERE PR.PROPRIETARYNAME = '$vac'
			LIMIT 1";

	$result = $this->db->query($sql);
	$ajaxResult = $result->result();

	//Return result
	echo json_encode($ajaxResult[0]);

} //End GetVacCostAndPrice()

//Used by UpdatePriceAndCost view
function ChangePriceCost()
{
	//Get variables from UpdatePriceAndCost form
	$vacName = $this->input->post('selectedVac');
	$drugCost = $this->input->post('selectedDrugCost');
	$trvlPrice = $this->input->post('selectedTrvlPrice');
	$refugeePrice = $this->input->post('selectedRefugeePrice');

	//Update the records matching the $vacName variable with the data in $drugCost, $trvlPrice, & $refugeePrice
	$sql = "UPDATE `fda_drug_package`
			SET DRUG_COST = '$drugCost', TRVL_CHRG = '$trvlPrice', REFUGEE_CHRG = '$refugeePrice'
			WHERE PRODUCTID IN
				(
				 SELECT PRODUCTID
				 FROM `fda_product`
				 WHERE PROPRIETARYNAME LIKE '%".$vacName."%'
				)
			";

	//Begin transaction
	$this->db->trans_begin();
	$this->db->query($sql);

	if($this->db->trans_status() === FALSE) //Transaction Failed
	{
		$this->db->trans_rollback();
		http_response_code(500);
		return null;
	}
	else //Transaction Successful
	{
		$this->db->trans_commit();
		echo json_encode("success");
	}
	//End transaction

} //End ChangePriceCost()


//Used by manage-user view
function RegisterUser()
{
	//Get data passed from AJAX
	$username = $this->input->post('Username');
	$email = $this->input->post('Email');
	$password = $this->input->post('Password');

	$groupID = $this->input->post('Role');
	$fname = $this->input->post('FName');
	$lname = $this->input->post('LName');

	//Formatting groupID & fname/lname for use in Ion_Auth's "register()" method
	$group = array($groupID);
	$additional_data = array(
			'first_name' => $fname,
			'last_name' => $lname
		);

	//Register User (returns FALSE if failed, the UserID if successful)
	$result = $this->ion_auth->register($username, $password, $email, $additional_data, $group);

	if($result != FALSE)
	{
		$user = array(
			'fname' => $fname, 
			'lname' => $lname
			);

		echo json_encode($user);
	}
	else
	{
		//echo json_encode($result);
	}

} //End RegisterUser()


//used by #manageEmail.focusout() event in manage-users.php view
function CheckUserEmail()
{
	$userid = $this->input->post('UserID');
	$email = $this->input->post('Email');

	//Check to see if email is same as existing email (if different, check to see if it is the same as any other email in the system)
	$sql = "SELECT id
			FROM users
			WHERE email = '$email'";

	$result = $this->db->query($sql);
	$resultArray = $result->result();

	$rowCount = count($resultArray);

	//Method return variable
	$returnArray = array(
		'emailChanged' => null,
		'emailExists' => null,
		'email' => null
	);

	if($rowCount == 0 || $rowCount == 1)
	{
		if($rowCount == 0) //If row count is 0, then the query result was null & the email doesn't exist in the system
		{
			$returnArray['emailChanged'] = 'TRUE';
			$returnArray['emailExists'] = 'FALSE';
			$returnArray['email'] = $email;

			echo json_encode($returnArray);

		} //End if
		else //If 'else' runs, then row count == 1. Check the id from the query against the userid variable to make sure they match. If they match, then the email didn't change (return 'FALSE' for $returnArray['emailChanged']). If they don't match then email is in use by another user (return TRUE for $returnArray['emailExists'])
		{
			if($resultArray[0]->id == $userid)
			{
				$returnArray['emailChanged'] = 'FALSE';
				$returnArray['emailExists'] = 'TRUE'; 
				$returnArray['email'] = $email;

				echo json_encode($returnArray);

			} //End if
			else
			{
				$returnArray['emailChanged'] = 'TRUE';
				$returnArray['emailExists'] = 'TRUE';
				$returbArray['email'] = $email;

				echo json_encode($returnArray);

			} //End else

		} //End else
	} //End if
	else //If array size is greater than 1, then an error occurred (more than 1 user uses the same email)
	{
		// $returnArray['emailChanged'] = 'FALSE';
		// $returnArray['emailExists'] = 'TRUE';

	} //End else
} //End CheckUserEmail()


//Used by manage-user view, btnUpdateUser.click()
function UpdateUser()
{
	$userID = $this->input->post('UserID');

	//Data to be used in the $userData array
	$fName = $this->input->post('FName');
	$lName = $this->input->post('LName');
	$email = $this->input->post('Email');

	$userData = array(
			"first_name" => $fName,
			"last_name" => $lName,
			"email" => $email
		);

	//ion_auth->update() returns TRUE if successful & FALSE if unsuccessful
	$updateResult = $this->ion_auth->update($userID, $userData);

	$returnResult = array(
			'wasSuccess' => $updateResult,
			'userFeedback' => null
		);


	if($updateResult)
	{
		$returnResult['userFeedback'] = "$fName $lName updated successfully";
	} //End if
	else
	{
		$returnResult['userFeedback'] = "Update of $fName $lName failed";
	} //End else

	//Return result to AJAX calling method
	echo json_encode($returnResult);

} //End UpdateUser()


//Used by manage-user view, btnDeleteUser.click() event
function DeleteUser()
{
	$userID = $this->input->post('UserID');
	$fName = $this->input->post('FName');
	$lName = $this->input->post('LName');

	//ion_auth->delete_user returns TRUE if delete successful & FALSE if unsuccessful
	$result = $this->ion_auth->delete_user($userID);

	$returnResult = array(
			'wasSuccess' => $result,
			'userFeedback' => null
		);

	if($result)
	{
		$returnResult['userFeedback'] = "$fName $lName successfully deleted";
	} //End if
	else
	{
		$returnResult['userFeedback'] = "$fName $lName was not successfully deleted";
	} //End else

	//Return result to AJAX function
	echo json_encode($returnResult);

} //End DeleteUser()


//Used by manage-user view (the "Register" form)
function CheckUsername()
{
	$username = $this->input->post('Username');

	//username_check() returns TRUE if username exists in database & FALSE if it doesn't exist
	$result = $this->ion_auth->username_check($username); 

	$resultArray = array(
			'result' => $result,
			'username' => $username
		);

	echo json_encode($resultArray);


} //End CheckUsername()

//Used by the manage-users.php view (the "Register" form)
function CheckEmail()
{
	$email = $this->input->post('Email');

	$result = $this->ion_auth->email_check($email);

	$resultArray = array(
			'emailExists' => $result,
			'email' => $email
		);

	echo json_encode($resultArray);

} //End CheckEmail()

function CheckPasswordLength()
{
	//Load ion_auth config file to access minimum_password_length config variable
	$this->config->load('ion_auth', TRUE);
	
	$pass = $this->input->post('Password');
	$minPassLength = $this->config->item('min_password_length', 'ion_auth');

	$pass = trim($pass);

	//Part of method return variable
	$passOK = null;

	if(strlen($pass) >= $minPassLength)
	{
		$passOK = TRUE;
	} //End if
	else
	{
		$passOK = FALSE;
	}

	//Method return variable
	$result = array(
			'lengthOK' => $passOK,
			'minLength' => $minPassLength
		);

	//Return result to AJAX function
	echo json_encode($result);


} //End CheckPasswordLength()


//Used by LoanOut view
function EditBorrowers()
{
	//Get action from calling AJAX method ()
	$array = $this->input->post('DataObject');

	$action = $array['action'];
	$id = $array['id'];
	$name = $array['name'];
	$contact = $array['contact'];
	$phone = $array['phone'];
	$email = $array['email'];

	$sql = null;

	//Declare the added or edit borrower's id as a session variable
	$this->session->borrowerID = null;

	//Determine which action the user wanted to take ("Add", "Edit", or "Delete")
	switch($action)
	{
		case 'add': //Get information from fields and add to borrower table
			$sql = "INSERT INTO borrower (ENTITYNAME, CONTACT_NAME, PHONE, EMAIL) 
					VALUES ('$name', '$contact', '$phone', '$email')";

			$qryMaxID = "SELECT MAX(BORROWERID) as ID
						 FROM borrower"; //For assigning the borrowerID session variable

			break; //End case: add
		case 'edit': //Get information from fields and update the selected borrower
			$sql = "UPDATE borrower
					SET ENTITYNAME = '$name', CONTACT_NAME = '$contact', PHONE = '$phone', EMAIL = '$email'
					WHERE BORROWERID = $id";

			//Assign borrowerID session variable
			$this->session->borrowerID = $id;

			break; //End case: edit
		case 'delete': //Remove the selected borrower from the borrower table
			//Query to delete borrower
			$sql = "DELETE FROM borrower
					WHERE BORROWERID = $id";

			break; //End case: delete
		default:
			echo "An error occurred"; //The AJAX function's error function will be triggered since a JSON object won't be returned
			break; //End default:
	}

	$newBorrowers = "SELECT BORROWERID as Id, ENTITYNAME as Name
					 FROM borrower 
					 ORDER BY ENTITYNAME ASC";

	$this->db->trans_begin();
	$this->db->query($sql);

	if($this->db->trans_status() === FALSE)
	{
		$this->db->trans_rollback();
	}
	else
	{
		$this->db->trans_commit();

		//Assign borrowerID session variable if a new borrower was added
		if($action == 'add')
		{
			$newBorrower = $this->db->query($qryMaxID);
			$newBorrowerArray = $newBorrower->result();
			$this->session->borrowerID = $newBorrowerArray[0]->ID;
		}

		//Get borrowers to return to AJAX function
		$qryResult = $this->db->query($newBorrowers);
		$qryResultArray = $qryResult->result(); //Return an array of the new list of borrowers to AJAX calling method

		//AJAX return result
		$resultArray = array(2);
		$resultArray[0] = $this->session->borrowerID; //The value for borrowerID is null by default (this will be the value if the "delete" action was selected)
		$resultArray[1] = $qryResultArray; 

		echo json_encode($resultArray); //Return JSON object to cause AJAX function's success function to run
	} //End else


} //End EditBorrowers()

//Used by LoanOut view
function GetBorrower()
{
	$id = $this->input->post('BorrowerID');

	$sql = "SELECT ENTITYNAME as 'Name', CONTACT_NAME as 'Contact', PHONE as 'Phone', EMAIL as 'Email'
			FROM borrower
			WHERE BORROWERID = $id";

	$result = $this->db->query($sql);
	$resultArray = $result->result();

	echo json_encode($resultArray);
}


//Used by EditTransactions controller method
function FilterTransactions()
{
	$theReport = new Reports();

	$transType = $this->input->post('transType');
	$returnType = $this->input->post('dataReturnType');
	$filteredResults = null; //declare variable so scope is higher than if statement 

	$filterResults = $theReport->TransactionsByType($transType);

	echo json_encode($filterResults);

} //End FilterTransactions()


//AJAX function for the EditTransactions page
function EditSingleTransaction()
{
	//Data from POST request: TransID, LotNum, ExpireDate, TransQty
	$aTransID = $this->input->post('TransID');
	$aLotNum = strip_tags($this->input->post('LotNum'));
	$anExpirationDate = strip_tags($this->input->post('Expiration'));
	$aTransQty = strip_tags($this->input->post('TransQty'));
	$transType = $this->input->post('TransType');
	$packageQty = strip_tags($this->input->post('PackageQty'));
	$dosesPerPackage = strip_tags($this->input->post('DosesPerPackage'));

	//Filter against XSS
	$aLotNum = $this->security->xss_clean($aLotNum);
	$anExpirationDate = $this->security->xss_clean($anExpirationDate);
	$aTransQty = $this->security->xss_clean($aTransQty);
	$packageQty = $this->security->xss_clean($packageQty);
	$dosesPerPackage = $this->security->xss_clean($dosesPerPackage);

	//Update record in database
	//Update the record where TransID == $aTransID in the table based on $transType
	//Update the Lot Number (VaccineTrans table), Expiration Date (VaccineTrans table), and Transaction Qty (administer, order_invoice, loanout, or loanreturn table)
	//Update Lot & Expiration Date
	$sql = "UPDATE vaccinetrans
			SET LOTNUM = '$aLotNum', ExpireDate = '$anExpirationDate'
			WHERE TRANSID = $aTransID";

	//Update record
	$this->db->query($sql);

	//Update TransQty
	switch($transType)
	{
		case "Invoice":
			$sql = "UPDATE order_invoice
					SET PACKAGEQTY = $packageQty, DOSES_PER_PACKAGE = $dosesPerPackage
					WHERE INVOICEID = $aTransID";
			break;

		case "Administer":
			$sql = "UPDATE administer
					SET DOSES_GIVEN = $aTransQty
					WHERE ADMINISTERID = $aTransID";
			break;

		case "Loan Out":
			$sql = "UPDATE loanout
					SET TOTAL_DOSES = $aTransQty
					WHERE LOANID = $aTransID";
			break;

		case "Loan Return":
			$sql = "UPDATE loanreturn
					SET TOTAL_DOSES = $aTransQty /*Update this b/c Total_Doses column no longer exists*/
					WHERE RETURNID = $aTransID";
			break;

		default:
			$sql = "error";
			break;
	}

	//Check to make sure default case (in switch statement) wasn't triggered
	if($sql == "error")
	{
		echo json_encode("Failure, default.");
	}
	else //Update record
	{
		$this->db->query($sql);
		echo json_encode("Success");
	}


} //End EditSingleTransaction()


//Used by the EditTransactions page only when editing "Invoice" transactions (this gets the Package & the Doses Per Package quantities)
function GetPackageAndDoses()
{
	//Get id to search for the requested transaction
	$transID = $this->input->post('TransID');

	//Get package & doses per package quantities
	$sql = "SELECT PACKAGEQTY, DOSES_PER_PACKAGE
			FROM ORDER_INVOICE
			WHERE InvoiceID = $transID";

	$result = $this->db->query($sql);
	$resultArray = $result->result();

	echo json_encode($resultArray);
} //End GetPackageAndDoses()

function GetDoses()
{
	//Get id to search for requested transaction
	$transID = $this->input->post('TransID');
	$transType = $this->input->post('TransType');
	$sql = null;

	//Get dose quantity for transaction
	switch($transType)
	{
		case "Administer":
			$sql = "SELECT DOSES_GIVEN as 'Doses Given'
					FROM administer
					WHERE AdministerID = $transID";
			break;
		case "Loan Out":
			$sql = "SELECT TOTAL_DOSES as 'Doses Given'
					FROM loanout
					WHERE LoanID = $transID";
			break;
		case "Loan Return":
			$sql = "SELECT TOTAL DOSES as 'Doses Given'
					FROM loanreturn
					WHERE ReturnID = $transID";
			break;
		default:
			break;
	}

	$result = $this->db->query($sql);
	$resultArray = $result->result();

	//Return dose result
	echo json_encode($resultArray);

} //End GetDoses()

//Used by the Inventory/EditTransactions controller method
function DeleteTransaction()
{
	//Get transaction id & transaction type
	$transID = $this->input->post('TransID');
	$transType = $this->input->post('TransType');

	//Write sql queries
	switch($transType)
	{
		case 'Invoice':
			$sqlTypeTbl = "DELETE FROM Order_Invoice
						   WHERE InvoiceID = '$transID'; ";
			break;
		case 'Administer':
			$sqlTypeTbl = "DELETE FROM Administer
						   WHERE AdministerID = '$transID'";
			break;
		case 'Loan Out':
			$sqlTypeTbl = "DELETE FROM LoanOut
						   WHERE LoanID = '$transID'";
			break;
		case 'Loan Return':
			$sqlTypeTbl = "DELETE FROM LoanReturn
						   WHERE ReturnID = '$transID'";
			break;
		default:
			$sqlTypeTbl = "Error";
			break;
	}

	$sqlVacTransTbl = "DELETE FROM VaccineTrans
			 		   WHERE TransID = '$transID'; ";
	$sqlTransTbl = "DELETE FROM Transaction
			 		WHERE TransID = '$transID';";

	//Run multiple queries (to delete from )
	$this->db->trans_start();
		$this->db->query($sqlTypeTbl);
		$this->db->query($sqlVacTransTbl);
		$this->db->query($sqlTransTbl);
	$this->db->trans_complete();

	//Turn off transactions (transactions are enabled when $this->db->trans_start() is run)
	$this->db->trans_off();

	//Test transaction was deleted
	$sqlCheck = "SELECT TransID
				 FROM Transaction
				 WHERE TransID = '$transID'";

	$result = $this->db->query($sqlCheck);
	$resultCount = $result->num_rows();

	//Return result
	if($resultCount == 0) //If no results in result set, return True, else return False
	{
		echo json_encode("True"); //"True" that the transaction was deleted from the transaction table
	}
	else
	{
		echo json_encode("False"); //"False" that the transaction was deleted from the transaction table
	}


} //End DeleteTransaction()

//Used by the "manage-users.php" view ("GetRoles()" javascript function)
function GetUserRoles()
{
	//Query to get Ion_Auth user roles
	$sql = "SELECT ID, DESCRIPTION as 'Description'
			FROM groups
			ORDER BY DESCRIPTION ASC";

	$results = $this->db->query($sql);
	$resultArray = $results->result();

	if(count($resultArray) > 0) //Only return a json object if there are results, otherwise don't return anything (so that the AJAX function's error function will be triggered)
	{
		echo json_encode($resultArray);
	}

} //End GetUserRoles()

//Used by the 'manage-users.php' view ("GetUserList()" javascript function)
function GetUserList()
{
	$sql = "SELECT ID, FIRST_NAME as 'First Name', LAST_NAME as 'Last Name'
			FROM users
			ORDER BY LAST_NAME, FIRST_NAME ASC";

	$result = $this->db->query($sql);
	$resultArray = $result->result(); 

	if(count($resultArray) > 0) //Return json object if there is more than 0 users (if less than 1 user, then AJAX error function will be triggered b/c a json object is not returned)
	{
		echo json_encode($resultArray);
	}
	
} //End GetUserList()

function GetSpecificUser()
{
	//Store selected User ID
	$userID = $this->input->post('UserID');

	//Find selected user
	$sql = "SELECT Username, Email, First_Name as 'First Name', Last_Name as 'Last Name'
			FROM users
			WHERE ID = $userID";

	$result = $this->db->query($sql);
	$resultArray = $result->result(); //Returns an array of row objects

	//Return result
	if(count($resultArray) == 1) //If user is found, then return json object; if more than one (or if 0) are found, return nothing so that AJAX error function is executed
	{
		$result = $resultArray[0]; //This is done to get rid of the array structure & just return the single result object contained within the array (makes processing within the AJAX success function easier)

		echo json_encode($result);
	}

} //End GetSpecificUser()


//Used by the loanreimburse.php view
function GetOutstandingLoans()
{
		//Get the sort criteria
		//Allowed criteria values:
		//'all' (default value), borrower', 'vacName', 'signer', 'loanDate', 'lotNum', 'expireDate', 'doses'
		$sortCriteria = $this->input->post('SortCriteria');
		$filterCriteria = $this->input->post('FilterCriteria');
		
		$qryWhere = null; //Assigned based on $sortCriteria
		$qryOrderBy = null; //Assigned based on $filterCriteria

		//Where Clause
		if($sortCriteria == 'all')
		{
			$qryWhere == '';
			$qryOrderBy == '';
		} //End if
		elseif($sortCriteria != 'all' && $filterCriteria == 'all') //This means sort (rather than filter) by the selected radio button's category. Thus the WHERE clause is blank, but an ORDER BY clause exists
		{
			$qryWhere = "";

			switch($sortCriteria)
			{
				case "borrower":
					$qryOrderBy = "ORDER BY b.borrowerid";
					break;
				case "vacName":
					$qryOrderBy = "ORDER BY pr.nonproprietaryname";
					break;
				case "signer":
					$qryOrderBy = "ORDER BY lo.signer_name";
					break;
				case "loanDate":
					$qryOrderBy = "ORDER BY t.transdate";
					break;
				case "lotNum":
					$qryOrderBy = "ORDER BY vt.LotNum";
					break;
				case "expireDate":
					$qryOrderBy = "ORDER BY vt.ExpireDate";
					break;
				case "doses":
					$qryOrderBy = "ORDER BY lo.Total_Doses";
					break;
			} //End switch

		} //End elseif
		else
		{
			switch($sortCriteria)
			{
				case "borrower":
					$qryOrderBy = "AND b.borrowerid = '$filterCriteria'
								   ORDER BY b.borrowerid";
					break;
				case "vacName":
					$qryOrderBy = "AND pr.nonproprietaryname = '$filterCriteria'
								   ORDER BY pr.nonproprietaryname";
					break;
				case "signer":
					$qryOrderBy = "AND lo.signer_name = '$filterCriteria'
								   ORDER BY lo.signer_name";
					break;
				case "loanDate":
					$qryOrderBy = "AND t.transdate = '$filterCriteria'
								   ORDER BY t.transdate";
					break;
				case "lotNum":
					$qryOrderBy = "AND vt.LotNum = '$filterCriteria'
								   ORDER BY vt.LotNum";
					break;
				case "expireDate":
					$qryOrderBy = "AND vt.ExpireDate = '$filterCriteria'
								   ORDER BY vt.ExpireDate";
					break;
				case "doses":
					$qryOrderBy = "AND lo.Total_Doses = '$filterCriteria'
								   ORDER BY lo.Total_Doses";
					break;


			} //End switch

		} //End else

		$qry = "SELECT RemainingQty.LoanID as 'Loan ID', pa.drugid as 'Drug ID', lo.borrowerid as 'Borrower ID', lo.loan_dose_price as 'Per Dose Loan Cost', pr.NONPROPRIETARYNAME as 'Non-Proprietary Name', b.entityname as 'Borrower', 
				lo.signer_name as 'Loan Signer', t.transdate as 'Loan Date', vt.lotnum as 'Lot Number', vt.expiredate as 'Expiration Date',
				RemainingQty.LoanedDoses as 'Loaned Doses', RemainingQty.ReturnedDoses as 'Returned Doses', (RemainingQty.LoanedDoses - RemainingQty.ReturnedDoses) as 'Remaining Doses', ((RemainingQty.LoanedDoses - RemainingQty.ReturnedDoses) * lo.LOAN_DOSE_PRICE) as 'Outstanding Loan Value'
				FROM
				(
				SELECT lo.loanid as LoanID, lo.total_doses as LoanedDoses, CAST(IFNULL(ReimbursedQty.ReturnedDoses, 0) as DECIMAL(7,3)) as ReturnedDoses from loanout lo
				left join
				(SELECT LoanID, SUM(DoseQty) as ReturnedDoses
				FROM
				((select lr.loanid as LoanID, d.dose_qty as DoseQty
				from loanreturn lr inner join dose_return_type d on lr.returnid = d.return_id)
				union all
				(select lr.loanid as LoanID, (c.amount/pa.drug_cost) as DoseQty
				from loanreturn lr inner join cash_return_type c on lr.returnid = c.return_id
				inner join loanout lo on lr.loanid = lo.loanid
				inner join vaccinetrans vt on lo.loanid = vt.transid
				inner join fda_drug_package pa on vt.drugid = pa.drugid)) ReturnedQty
				group by LoanID) ReimbursedQty on lo.loanid = ReimbursedQty.loanid
				) RemainingQty
				inner join loanout lo on RemainingQty.loanid = lo.loanid
				inner join borrower b on lo.borrowerid = b.borrowerid

				inner join vaccinetrans vt on RemainingQty.loanid = vt.transid
				inner join generic_transaction t on RemainingQty.loanid = t.transid
				inner join fda_drug_package pa on vt.drugid = pa.drugid
				inner join fda_product pr on pa.productid = pr.productid

				WHERE (RemainingQty.LoanedDoses > RemainingQty.ReturnedDoses)";


		//Add where clause and order by clause to initial $qry variable
		$qryCombined = $qry." ".$qryWhere." ".$qryOrderBy;

		$qryResult = $this->db->query($qryCombined);

		$tableData = $qryResult->result();

		//Headings for the data to be returned to AJAX calling function
		//The headings come from the SQL query results' column headings
		$header = array(
				'Non-Proprietary Name',
				'Borrower',
				'Loan Signer',
				'Loan Date',
				'Lot Number',
				'Expiration Date',
				'Loaned Doses',
				'Returned Doses',
				'Remaining Doses',
				'Outstanding Loan Value'
			); //Names of database result columns

		$loanCount = count($tableData);


		$returnData = array(
				'headerRow' => $header,
				'tableData' => $tableData,
				'numLoans' => $loanCount
			);

		echo json_encode($returnData);

} //End GetOutstandingLoans()


function LoanReimbursement()
{
	//List of all data values passed from the client machine in this AJAX
	//request:
		// 'ReimburseType': reimburseType, 
		// 'IsPartialReimbursement': isPartialReimbursement, 
		// 'LoanID': loanID,
		// 'ReimburseSigner': reimburseSigner,
		
		// 'DrugID': drugID,
		// 'ReimburseAmount': reimburseAmount,
		// 'LotNum': lotNum,
		// 'ExpireDate': expireDate,
		// 'DoseQty': doseQty
	//End list of data values

	//Get data from AJAX send request
	//Get reimbursement type (could be either "cash" or "doses")
	$typeName = $this->input->post('ReimburseType');
	$isPartialReimbursement = $this->input->post('IsPartialReimbursement');
	$loanID = $this->input->post('LoanID');
	$reimburseSigner = $this->input->post('ReimburseSigner');

	//Timestamp information
	date_default_timezone_set('UTC');
	$transTimestamp = date('Y-m-d H:i:s');

	//Employee conducting the transaction
	$userID = $this->ion_auth->get_user_id(); //$this->session->userdata('user_id'); //Pulled this from ion_auth_model.php's "user()" function

	$sqlGenericTransaction = "INSERT INTO generic_transaction (TransDate, EMPLOYEE_ID)
							  VALUES ('$transTimestamp', $userID)";

	//Add new generic transaction
	$this->db->query($sqlGenericTransaction);


	//Create query to get the transID of the newly added transaction
	$sqlTransID = "SELECT MAX(TransID) as MaxTransID
				   FROM generic_transaction";

	//Get transaction's 'TransID' from query result to insert into loanreturn table
	$transID = $this->db->query($sqlTransID);
	$transID = $transID->result(); //Process query result object into an array of objects
	$transID = $transID[0]->MaxTransID; //Store the first array index's value (the max value)

	//Query to insert transaction into loanreturn table
	$sqlLoanReturn = "INSERT INTO loanreturn (RETURNID, LOANID, RETURNER_NAME, IS_PARTIAL_RETURN)
					  VALUES ($transID, $loanID, '$reimburseSigner', $isPartialReimbursement)";

	//Insert loanreturn transaction
	$this->db->query($sqlLoanReturn);

	//Query to insert the type of return transaction ('cash' or 'doses')
	$sqlType = null; //Query assigned in switch

	switch($typeName)
	{
		case 'cash': //If cash, create query to enter cash reimbursement
			$amount = $this->input->post('ReimburseAmount');

			$sqlType = "INSERT INTO cash_return_type (RETURN_ID, AMOUNT)
						VALUES ($transID, $amount)";
			break;
		case 'doses': //If doses, create query to enter dose reimbursement
			$drugID = $this->input->post('DrugID');
			$lotNum = $this->input->post('LotNum');
			$expireDate = $this->input->post('ExpireDate');
			$doseQty = $this->input->post('DoseQty');

			//Process date for database
			$dateArray = explode('-', $expireDate);
			$year = $dateArray[2];
			$month = $dateArray[0];
			$day = $dateArray[1];

			$expireDate = $year."-".$month."-".$day;
			echo $expireDate;

			$sqlType = "INSERT INTO dose_return_type (RETURN_ID, DRUGID, LOTNUM, EXPIREDATE, DOSE_QTY)
						VALUES ($transID, '$drugID', '$lotNum', '$expireDate', $doseQty)";
			break;
		default:
			//An error occurred
			http_response_code(500);
			break;
	} //End switch

	//Insert return type transaction
	$this->db->query($sqlType);
	
	echo json_encode("Return Success!");

} //End LoanReimbursement()


//Get range of loan filter options (used by loanreimburse.php view to populate the <select> element)
function GetLoanFilterOptions()
{
	$filterCategory = $this->input->post('FilterCategory');

	$filterField = null; //Assigned in the switch statement
	$fieldName = null; //Assigned in the switch statement
	$objectProperty = null; //Assigned in switch (stores the name of the column referenced in the SELECT part of the query - in CodeIgniter, the column names becomes the property names of the query result rows (each row is an object))

	switch($filterCategory)
	{
		case 'vacName':
			$filterField = 'pr.nonproprietaryname';
			$objectProperty = 'nonproprietaryname';
			break;
		case 'borrower':
			$filterField = 'b.entityname';
			$objectProperty = 'entityname';
			break;
		case 'signer':
			$filterField = 'lo.signer_name';
			$objectProperty = 'signer_name';
			break;
		case 'loanDate':
			$filterField = 't.transdate';
			$objectProperty = 'transdate';
			break;
		case 'lotNum':
			$filterField = 'vt.LotNum';
			$objectProperty = 'LotNum';
			break;
		case 'expireDate':
			$filterField = 'vt.ExpireDate';
			$objectProperty = 'ExpireDate';
			break;
		case 'doses':
			$filterField = 'lo.Total_Doses';
			$objectProperty = 'Total_Doses';
			break;
		default:
			break;
	} //End switch

	//Query
	$sql = "SELECT $filterField
			FROM
			(
				SELECT lo.loanid as LoanID, lo.total_doses as LoanedDoses, CAST(IFNULL(ReimbursedQty.ReturnedDoses, 0) as DECIMAL(7, 3)) as ReturnedDoses 
				FROM loanout lo
				LEFT JOIN
				(
					SELECT LoanID, SUM(DoseQty) as ReturnedDoses
					FROM
					(
						(
						 SELECT lr.loanid as LoanID, d.dose_qty as DoseQty
						 FROM loanreturn lr INNER JOIN dose_return_type d on lr.returnid = d.return_id
						)
						UNION ALL
						(
						 SELECT lr.loanid as LoanID, (c.amount/pa.drug_cost) as DoseQty
						 FROM loanreturn lr INNER JOIN cash_return_type c on lr.returnid = c.return_id
						 INNER JOIN loanout lo on lr.loanid = lo.loanid
						 INNER JOIN vaccinetrans vt on lo.loanid = vt.transid
						 INNER JOIN fda_drug_package pa on vt.drugid = pa.drugid
						)
					) ReturnedQty
					GROUP BY LoanID
				) ReimbursedQty on lo.loanid = ReimbursedQty.loanid
			) RemainingQty
			INNER JOIN loanout lo on RemainingQty.loanid = lo.loanid
			INNER JOIN borrower b on lo.borrowerid = b.borrowerid

			INNER JOIN vaccinetrans vt on RemainingQty.loanid = vt.transid
			INNER JOIN generic_transaction t on RemainingQty.loanid = t.transid
			INNER JOIN fda_drug_package pa on vt.drugid = pa.drugid
			INNER JOIN fda_product pr on pa.productid = pr.productid

			WHERE (RemainingQty.LoanedDoses > RemainingQty.ReturnedDoses)
			GROUP BY $filterField";

	$result = $this->db->query($sql);
	$resultArray = $result->result();

	$processedArray = null; //Populated in foreach loop
	$counter = 0; //Assigns array index for $processedArray

	foreach($resultArray as $row)
	{
		$processedArray[$counter] = $row->$objectProperty;
		$counter++;
	} //End foreach()

	//Return $processedArray
	echo json_encode($processedArray);

} //End GetLoanFilterOptions()

//Checks to see if a loanid during a loan return transaction
//already exists in the loanreturn table - if so, it means the return transaction
//(the current & prior transaction) are partial transactions
//This function is called by the loanreimburse.php view
function CheckPartialLoanReturn()
{
	$loanid = $this->input->post('LoanID');

	$qry = "SELECT returnid
			FROM loanreturn
			WHERE loanid = $loanid";

	$result = $this->db->query($qry);
	$resultArray = $result->result();

	echo $resultArray;
	var_dump($resultArray);

	//Return json object to trigger success function in calling AJAX 
	echo json_encode($resultArray);

} //End CheckPartialLoanReturn()

/**********************/
/* End AJAX Functions */
/**********************/

} //End Inventory controller class

?>