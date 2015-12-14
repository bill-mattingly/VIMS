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

		//var_dump($firstRow);


		$arrayKeys = array_keys($firstRow); //array_keys() function needs an array as its first object; we want the array of keys for each row object rather than the array of all row objects (thus we select one of the rows to get the keys for each result object)

			
			//var_dump($tableRows);

			// if($tableRows != null)
			// {
			// 	$data['tblSummary'] = $this->table->generate($tableRows);	
			// }
			// else
			// {
			// 	$data['']
			// }
			
			//$data['tblSummary'] = $this->table->generate($tableRows);

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
		//var_dump($tableString);


		//Load view
		$this->load->view('vac-header');
		$this->load->view("vaccine/index", $data);
		$this->load->view('vac-footer');

	} //End Index()


	public function ScanInvoice()
	{

/********************************/

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
		//$this->form_validation->set_rules('vaccine-action', 'Vaccine Action', 'required');

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
			//$this->session->vaccineaction = strtolower($action); //$this->input->post('vaccine-action');

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





		//View

		//Validation

		//Redirect to Invoice function

/*********************************/

	public function ScanAdminister()
	{
		//Vaccine variables
		$barcodeArray = null;
		$vaccineArray = null;
		$vaccine = new Vaccine();

		//$noInventoryMsg = $this->session->

		//Load helpers
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('vaccine');

		//Set validation rules
		$this->form_validation->set_rules('barcode', 'Barcode', "callback_CheckBarcodeInventory[FALSE]"); //"FALSE" is the 2nd arguement to the callback function. The value is TRUE/FALSE based on whether the scanned barcode will contain a "Sale" (TRUE) or "Use" (FALSE) NDC number (a "carton" or "vial" barcode respectively). Administered vaccines will scan the vial barcode & thus contain a "Use" ndc number (so it is "FALSE" that the ndc is a "Sale" ndc number) //'required');
		//$this->form_validation->set_rules('vaccine-action', 'Vaccine Action', 'required');


		// if(count($vaccineArray) == 0 || $vaccineArray[0]->{"Net Doses"} < 1) //If this occurs, the vaccine is not in inventory. To prevent an error, this redirects the user back to Scan-*)
		// {
		// 	$this->session->NoInventoryMsg = "Vaccine is no Longer in Inventory"; //Don't know how to display this error message to the user
		// 	redirect('Inventory/ScanAdminister', 'refresh');
		// }


		if($this->form_validation->run() === FALSE)
		{
			$this->load->view('vac-header');
			$this->load->view('vaccine/scan-barcode'); //, $data);
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


			/**************************/
			/**************************/
			//Begin Original Part of Else Statement:
/*
			//Store form variables in session variables
			$this->session->barcode = $this->input->post('barcode');
			//$this->session->vaccineaction = strtolower($action); //$this->input->post('vaccine-action');

			//Parse Barcode
			$barcodeArray = $vaccine->ParseBarcode($this->session->barcode, FALSE);
			
			//var_dump($this->session->barcode);

			//Get vaccine
			$vaccineArray = $vaccine->GetVaccine($barcodeArray['ndc10'], TRUE);

			//Determine whether multiple vaccines share the same ndc or whether it's one vaccine
			//If multiple vaccines, go to the "selectvaccine" method
			if(count($vaccineArray) > 1)
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
				redirect('Inventory/Administer');
			} //End else
*/


			/**************************/
			/**************************/


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
		$this->form_validation->set_rules('barcode', 'Barcode', "callback_CheckBarcodeInventory[TRUE]"); //'required');
		//$this->form_validation->set_rules('vaccine-action', 'Vaccine Action', 'required');

		if($this->form_validation->run() === FALSE)
		{
			$this->load->view('vac-header');
			$this->load->view('vaccine/scan-barcode'); //, $data);
			$this->load->view('vac-footer');
		}
		else
		{
			//Store form variables in session variables
			$this->session->barcode = $this->input->post('barcode');
			//$this->session->vaccineaction = strtolower($action); //$this->input->post('vaccine-action');

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


	// public function ScanLoanReturn()
	// {
	// 	//Vaccine variables
	// 	$barcodeArray = null;
	// 	$vaccineArray = null;
	// 	$vaccine = new Vaccine();

	// 	//Load helpers
	// 	$this->load->helper('form');
	// 	$this->load->library('form_validation');
	// 	$this->load->model('vaccine');

	// 	//Set validation rules
	// 	$this->form_validation->set_rules('barcode', 'Barcode', 'required');
	// 	//$this->form_validation->set_rules('vaccine-action', 'Vaccine Action', 'required');

	// 	if($this->form_validation->run() === FALSE)
	// 	{
	// 		$this->load->view('vac-header');
	// 		$this->load->view('vaccine/scan-barcode'); //, $data);
	// 		$this->load->view('vac-footer');
	// 	}
	// 	else
	// 	{
	// 		//Store form variables in session variables
	// 		$this->session->barcode = $this->input->post('barcode');
	// 		//$this->session->vaccineaction = strtolower($action); //$this->input->post('vaccine-action');

	// 		//Parse Barcode & Get Vaccine
	// 		$barcodeArray = $vaccine->ParseBarcode($this->session->barcode, TRUE);
	// 		$vaccineArray = $vaccine->GetVaccine($barcodeArray['ndc10'], FALSE);

	// 		if(count($vaccineArray) < 1)
	// 		{	
	// 			//If $vaccineArray's count = 0, it means the user scanned a "Use" rather than "Sale" barcode & thus the incorrect database query was used
	// 			//Display error message to user
	// 			$this->session->error = "Please Scan a Box/Carton Barcode Rather than a Vial Barcode For Loans and Invoices";

	// 			//Reload scan-barcode page
	// 			redirect('Inventory/ScanLoanReturn', 'refresh');
	// 		}

	// 		//Determine whether multiple vaccines share the same ndc or whether it's one vaccine
	// 		//If multiple vaccines, go to the "selectvaccine" method
	// 		elseif(count($vaccineArray) > 1)
	// 		{
	// 			$this->session->barcodeArray = $barcodeArray;
	// 			$this->session->vaccineArray = $vaccineArray;

	// 			redirect('Inventory/SelectVacFromList');
	// 		}
	// 		else
	// 		{
	// 			$barcodeArray['drugID'] = $vaccineArray[0]->DrugID;
	// 			$barcodeArray['clinicCost'] = $vaccineArray[0]->Drug_Cost;
	// 			$barcodeArray['trvlPrice'] = $vaccineArray[0]->Trvl_Chrg;
	// 			$barcodeArray['refugeePrice'] = $vaccineArray[0]->Refugee_Chrg;

	// 			//Store barcodeArray and vaccineArray in session variables
	// 			$this->session->barcodeArray = $barcodeArray;
	// 			$this->session->vaccineArray = $vaccineArray;

	// 			//Call the method for the action the user requested (ex. "Invoice")
	// 			redirect('Inventory/LoanReturn');

	// 		} //End else
	// 	} //End else
	// } //End ScanLoanReturn()




	//****************************
	//ORIGINAL ScanBarcode function
	//****************************


	// public function ScanBarcode()//$action)
	// {
	// 	//echo $action;
	// 	//Form variables
	// //	$title = "";
	// //	$this->session->vaccineaction = strtolower($action);

	// 	// switch($action)
	// 	// {
	// 	// 	case "Invoice":
	// 	// 		$title = "Add Invoice";
	// 	// 		break;
	// 	// 	case "Administer":
	// 	// 		$title = "Administer Vaccine";
	// 	// 		break;
	// 	// 	case "LoanOut":
	// 	// 		$title = "Loan Out";
	// 	// 		break;
	// 	// 	case "LoanReturn":
	// 	// 		$title = "Loan Return";
	// 	// 		break;
	// 	// 	default:
	// 	// 		break;
	// 	// }
		
	// //	$data['title'] = $title;



	// 	//Vaccine variables
	// 	$barcodeArray = null;
	// 	$vaccineArray = null;
	// 	$vaccine = new Vaccine();

	// 	//Load helpers
	// 	$this->load->helper('form');
	// 	$this->load->library('form_validation');
	// 	$this->load->model('vaccine');

	// 	//Set validation rules
	// 	$this->form_validation->set_rules('barcode', 'Barcode', 'required');
	// 	$this->form_validation->set_rules('vaccine-action', 'Vaccine Action', 'required');

	// 	if($this->form_validation->run() === FALSE)
	// 	{
	// 		$this->load->view('vac-header');
	// 		$this->load->view('vaccine/scan-barcode'); //, $data);
	// 		$this->load->view('vac-footer');
	// 	}
	// 	else
	// 	{
	// 		//Store form variables in session variables
	// 		$this->session->barcode = $this->input->post('barcode');
	// 		//$this->session->vaccineaction = strtolower($action); //$this->input->post('vaccine-action');

	// 		//Parse Barcode & Get Vaccine
	// 		if ($this->session->vaccineaction == 'administer') 
	// 		{
	// 			//Parse Barcode
	// 			$barcodeArray = $vaccine->ParseBarcode($this->session->barcode, FALSE);

	// 			//Get vaccine
	// 			$vaccineArray = $vaccine->GetVaccine($barcodeArray['ndc10'], TRUE);
	// 		}
	// 		else
	// 		{
	// 			$barcodeArray = $vaccine->ParseBarcode($this->session->barcode, TRUE);
	// 			$vaccineArray = $vaccine->GetVaccine($barcodeArray['ndc10'], FALSE);

	// 			if(count($vaccineArray) < 1)
	// 			{
	// 				//If $vaccineArray's count = 0, it means the user scanned a "Use" rather than "Sale" barcode & thus the incorrect database query was used
	// 				//Display error message to user
	// 				$this->session->error = "Please Scan a Box/Carton Barcode Rather than a Vial Barcode For Loans and Invoices";

	// 				//Reload scan-barcode page
	// 				redirect('Inventory/ScanBarcode', 'refresh');
	// 			}
	// 		}

	// 		//Determine whether multiple vaccines share the same ndc or whether it's one vaccine
	// 		//If multiple vaccines, go to the "selectvaccine" method
	// 		if(count($vaccineArray) > 1)
	// 		{
	// 			$this->session->barcodeArray = $barcodeArray;
	// 			$this->session->vaccineArray = $vaccineArray;

	// 			redirect('Inventory/SelectVacFromList');
	// 		}
	// 		else
	// 		{
	// 			$barcodeArray['drugID'] = $vaccineArray[0]->DrugID;
	// 			$barcodeArray['clinicCost'] = $vaccineArray[0]->Drug_Cost;
	// 			$barcodeArray['trvlPrice'] = $vaccineArray[0]->Trvl_Chrg;
	// 			$barcodeArray['refugeePrice'] = $vaccineArray[0]->Refugee_Chrg;

	// 			//Store barcodeArray and vaccineArray in session variables
	// 			$this->session->barcodeArray = $barcodeArray;
	// 			$this->session->vaccineArray = $vaccineArray;

	// 			//Call the method for the action the user requested (ex. "Invoice")
	// 			switch($this->session->vaccineaction)
	// 			{
	// 				case "invoice":
	// 					redirect('Inventory/Invoice');
	// 					break;

	// 				case "administer":
	// 					redirect('Inventory/Administer');
	// 					break;

	// 				case "loanout":
	// 					redirect('Inventory/LoanOut');
	// 					break;

	// 				case "loanreturn":
	// 					redirect('Inventory/LoanReturn');
	// 					break;

	// 				default:
	// 					echo "Default...";
	// 					break;
	// 			} //End switch
	// 		} //End else
	// 	} //End else
	// } //End ScanBarcode()


	public function SelectVacFromList()
	{
		//Load validation helpers
		$this->load->helper('form');
		$this->load->library('form_validation');

		//Data to pass to form

		if($this->session->theAction == 'ScanInvoice')
		{
			$data['vacList'] = $this->session->vaccineArray;
			//var_dump($data['vacList']);
		}
		else
		{
			$data['vacList'] = $this->session->inventoryArray; //$this->session->vaccineArray;
			//var_dump($data['vacList']);
		}

		//$data['ndc10'] = $this->session->barcodeArray['ndc10'];

		//Form validation
		$this->form_validation->set_rules('vaccineList', 'Select Package Description', 'callback_CheckVacSelect'); //'Select Vaccine Description', 'callback_CheckDropdownSelect[$drugID]');
		//$this->form_validation->set_message('CheckDropdownSelect', "Select A Vaccine Description");

		//var_dump($this->session->vaccineArray);


		if($this->form_validation->run() == FALSE)
		{
			//reload the form
			$this->load->view('vac-header');
			$this->load->view('vaccine/select-vaccine-from-list', $data);
			$this->load->view('vac-footer');
		}
		else
		{
			$arrayIndex = $this->input->post('vaccineList');

		//New

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


			// $vaccineArray = $this->session->vaccineArray;
			// $barcodeArray = $this->session->barcodeArray;

			// //Store variables for views in session variables
			// $barcodeArray['drugID'] = $vaccineArray[$arrayIndex]->DrugID;
			// $barcodeArray['clinicCost'] = $vaccineArray[$arrayIndex]->Drug_Cost;
			// $barcodeArray['trvlPrice'] = $vaccineArray[$arrayIndex]->Trvl_Chrg;
			// $barcodeArray['refugeePrice'] = $vaccineArray[$arrayIndex]->Refugee_Chrg;
			// $barcodeArray['numDosesPackage'] = $vaccineArray[$arrayIndex]->NumDosesPackage;

			// $this->session->barcodeArray = $barcodeArray;

		//End new

			//var_dump($this->session->theAction);

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

	    // if()
	    // {
	    	
	    // }
	    // else
	    // {
		    $data['lotNum'] = $this->session->barcodeArray['lotNum'];
		    $data['expireDate'] = $this->session->barcodeArray['expireDate'];
	    // }


	    $data['clinicCost'] = $this->session->vaccineArray->{'Clinic Cost'}; //barcodeArray['clinicCost'];
	    $data['numDosesPackage'] = $this->session->vaccineArray->{'Number Doses Package'}; //barcodeArray['numDosesPackage'];

	    // var_dump($data['numDosesPackage']);

	    //var_dump($this->session->vaccineArray);


	    //Form validation
	    $this->form_validation->set_rules('expireDate', 'Expire Date', 'required');
		$this->form_validation->set_rules('lotNum', 'Lot Number', 'required');
		$this->form_validation->set_rules('clinicCost', 'Cost Per Dose', 'required');
		$this->form_validation->set_rules('packageQty', 'Package Qty', 'required'); //Come back & set custom validation method to prevent invalid data
		//$this->form_validation->set_rules('dosesPerPackage', 'Doses Per Package', 'required');

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
    		$transArray = $vaccine->OrderInvoice($this->session->vaccineArray->{'Drug ID'}); //barcodeArray['drugID']);

    		$data['transid'] = $transArray['TransID'];

			//Timestamp value is stored in database in UTC time
			//To return it to the user, set the local timezone & then assign the UTC timestamp value to a new variable
			//date_default_timezone_set("America/New_York");
			//$data['timestamp'] = date("Y-m-d h:i:sa", $transArray['TransDate']);
			$data['timestamp'] = $transArray['TransDate'];
			$data['clinicCost'] = $this->session->vaccineArray->{'Clinic Cost'}; //barcodeArray['clinicCost'];
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
		$vaccineArray = $vaccine->GetSingleVacInventory($this->session->inventoryArray[0]->{'Drug ID'});//$this->session->barcodeArray['drugID']);
		//$vaccineArray = $this->session->inventoryArray;
		//var_dump($vaccineArray);

		//echo $vaccineArray[0]->{"Net Doses"};
		//var_dump($vaccineArray[11]);


		// if(count($vaccineArray) == 0 || $vaccineArray[0]->{"Net Doses"} < 1) //If this occurs, the vaccine is not in inventory. To prevent an error, this redirects the user back to Scan-*)
		// {
		// 	$this->session->NoInventoryMsg = "Vaccine is no Longer in Inventory"; //Don't know how to display this error message to the user
		// 	redirect('Inventory/ScanAdminister', 'refresh');
		// }



		$data['vaccineArray'] = $vaccineArray;


		//var_dump($vaccineArray[0]->{"Proprietary Name"});
		//echo $vaccineArray[0]->{'Proprietary Name'};

		$data['trvlPrice'] = $vaccineArray[0]->{"Travel Patient Chrg"};
		$data['refugeePrice'] = $vaccineArray[0]->{"Refugee Patient Chrg"};
		$data['ndc10'] = $vaccineArray[0]->{"Dose NDC10"};
		$data['clinicCost'] = $vaccineArray[0]->{"Clinic Cost"};
		//$data['dosesPackage'] = $vaccineArray[0]->{"Number Doses Package"};


		//Calculate $maxDoseQty based on how many doses are available for each lot number
		//$counter = 0; //array index variable

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

		//var_dump($maxDoseAndPackageArray);
		$this->session->MaxDoseAndPackageArray = $maxDoseAndPackageArray;
		//$maxDoseAndPackageArray = "test";

		//var_dump($dataAttributes);
		//$data['lotQtyArray'] = $lotQtyArray; //An array of "Net Doses" quantities which is in the same order as the array used to populate the Lot Number dropdown list in the Administer.php view
		$data['dataAttributes'] = $dataAttributes; //A string listing the "Net Doses" quantities by the lot numbers listed in the dropdown list in the Administer.php view


		//Data to pass to forms
		// $data['ndc10'] = $this->session->barcodeArray['ndc10'];
		// $data['ndc11'] = $this->session->barcodeArray['ndc11'];
		// $data['expireDate'] = $this->session->barcodeArray['expireDate'];
		// $data['lotNum'] = $this->session->barcodeArray['lotNum'];
		// $data['clinicCost'] = $this->session->barcodeArray['clinicCost'];
		// $data['trvlPrice'] = $this->session->barcodeArray['trvlPrice'];
		// $data['refugeePrice'] = $this->session->barcodeArray['refugeePrice'];

		//Validation Rules
		$this->form_validation->set_rules('lotNumList', 'Lot Number', 'callback_CheckLot'); //'required');
		//$this->form_validation->set_rules('expireDate', 'Expire Date', 'required');
		$this->form_validation->set_rules('clinicCost', 'Clinic Cost', 'required');
		$this->form_validation->set_rules('customerChrg', 'Customer Charge', 'required');
		$this->form_validation->set_rules('doseQty', 'Dose Quantity', "callback_CheckDoseQty"); //[".$maxDoseAndPackageArray."]");


		if($this->form_validation->run() === FALSE)
		{
			$this->load->view('vac-header');
			$this->load->view("vaccine/administer", $data);
			$this->load->view('vac-footer');
		}
		else
		{
			//Process Date
			// $aDate = $this->input->post('expireDate');
			// $aDate = substr($aDate, 6, 4)."-".substr($aDate, 0, 2)."-".substr($aDate, 3, 2);
//			$this->session->expireDate = $aDate;

			//Data from Form fields
			$this->session->lotNum = $this->input->post('lotNumList');
			$this->session->expireDate = $this->input->post('expireDate');
			$this->session->doseQty = $this->input->post('doseQty');
			$this->session->customerChrg = $this->input->post('customerChrg');

			$transData = $vaccine->Administer($this->session->inventoryArray[0]->{'Drug ID'}); //barcodeArray['drugID']);

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

		$vaccineArray = $vaccine->GetSingleVacInventory($selectedVaccine->{'Drug ID'}); //Provide DrugID property to GetSingleVacInventory method //($this->session->inventoryArray['drugID']);
		$data['vaccineArray'] = $vaccineArray;

		//var_dump($vaccineArray);


		//Data for form
		$data['listOfBorrowers'] = $borrower->DisplayBorrowers();
		$data['ndc10'] = $this->session->barcodeArray['ndc10'];
		// $data['ndc11'] = $this->session->barcodeArray['ndc11'];
		// $data['expireDate'] = $vaccineArray[0]->{"expireDate"}//$this->session->barcodeArray['expireDate'];
		// $data['lotNum'] = //$this->session->barcodeArray['lotNum'];
		$data['clinicCost'] = $vaccineArray[0]->{'Clinic Cost'}; //$this->session->barcodeArray['clinicCost'];
		//$data['dosesPackage'] = $vaccineArray[0]->{'Number Doses Package'};
		$data['maxDoses'] = $vaccineArray[0]->{'Net Doses'};
		//var_dump($vaccineArray);


		//Validation rules
		$this->form_validation->set_rules('lotNumList', 'Lot Number', 'callback_CheckLot'); //'required');//'callback_CheckLot'); //'required');
		$this->form_validation->set_rules('borrowerID', 'Borrower', 'callback_CheckBorrowerList');
		$this->form_validation->set_rules('loanSigner', 'Loan Signer', 'required');
		//$this->form_validation->set_rules('expireDate', 'Expire Date', 'required');
		//$this->form_validation->set_rules('packageQty', 'Package Quantity', 'required');
		//$this->form_validation->set_rules('dosesPerPackage', 'Doses Per Package', 'required');
		//$this->form_validation->set_rules('totalDosesLoaned', 'Doses', 'callback_CheckDoseQty');
		//$this->form_validation->set_message('CheckDropdownSelect', 'Please Select A Borrower From The List');


		if($this->form_validation->run() === FALSE)
		{
			//Load view
			$this->load->view("vac-header");
			$this->load->view("vaccine/loanout", $data);
			$this->load->view("vac-footer");
		}
		else
		{
			//Process date
			//$aDate = $this->input->post('expireDate');
			//var_dump($_POST['loanSigner']);

			//echo $aDate;
			//echo "\n";

			//$aDate = substr($aDate, 6, 4)."-".substr($aDate, 0, 2)."-".substr($aDate, 3, 2);
			//echo $aDate;
			//$this->session->expireDate = $aDate;

			//Store form values
			$this->session->lotNum = $this->input->post('lotNumList');
			$this->session->expireDate = $this->input->post('expireDate');
			$this->session->BorrowerID = $this->input->post('borrowerID');
			$this->session->DosesPerPackage = $this->input->post('dosesPerPackage');
			$this->session->PackageQty = $this->input->post('packageQty');

			//Enter data into database
			$drugID = $selectedVaccine->{'Drug ID'};
			//var_dump(->{'Drug ID'});
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
		// $reports = new Reports();

		// $transResults = $reports->TransactionsByType("All");
		// $data['transResults'] = $transResults;

//		$data['transResults'] = self::FilterTransactions(); //use self:: to reference methods within the same class 
		//var_dump($data);
		//echo "Hi";

		//var_dump($transResults);

		//Note: AJAX processes the request to build the transaction table

		$this->load->view("vac-header");
		$this->load->view("vaccine/edit-transactions"); //, $data);
		$this->load->view("vac-footer");

	} //End EditTransactions()


//*******************************
//ORIGINAL LoanReturn function
//*******************************

	// public function LoanReturn()
	// {
	// 	//Method variables
	// 	$vaccine = new Vaccine();
	// 	$borrower = new Borrower();

	// 	//Helpers
	// 	$this->load->helper('form');
	// 	$this->load->library('form_validation');
	// 	$this->load->library('table');

	// 	//Validation Rules
	// 	$this->form_validation->set_rules('expireDate', 'Expire Date', 'required');
	// 	$this->form_validation->set_rules('lotNum', 'Lot Number', 'required');
	// 	$this->form_validation->set_rules('packageQty', 'Package Quantity', 'required');
	// 	$this->form_validation->set_rules('dosesPerPackage', 'Doses Per Package', 'required');
	// 	$this->form_validation->set_rules('borrowerID', 'Borrower', 'callback_CheckDropdownSelect[$aBorrowerID]');
	// 	$this->form_validation->set_message('CheckDropdownSelect', 'Please Select A Borrower From The List');

	// 	//Data to display in form
	// 	$data['borrowerList'] = $borrower->DisplayBorrowers();
	// 	$data['ndc10'] = $this->session->barcodeArray['ndc10'];
	// 	$data['ndc11'] = $this->session->barcodeArray['ndc11'];


	// 	if($this->form_validation->run() === FALSE) //If validation fails, return the form
	// 	{
	// 		//Load view
	// 		$this->load->view("vac-header");
	// 		$this->load->view("vaccine/loanreturn", $data);
	// 		$this->load->view("vac-footer");
	// 	}
	// 	else
	// 	{
	// 		$this->session->expireDate = $this->input->post('expireDate');
	// 		$this->session->lotNum = $this->input->post('lotNum');
	// 		$this->session->PackageQty = $this->input->post('packageQty');
	// 		$this->session->DosesPerPackage = $this->input->post('dosesPerPackage');
	// 		$this->session->BorrowerID = $this->input->post('borrowerID');

	// 		//Input transaction into database
	// 		$transData = $vaccine->LoanReturn($this->session->barcodeArray['drugID'], $this->session->BorrowerID);
	// 		$data['tblSummary'] = $this->table->generate($transData['tblSummary']);

	// 		//Provide summary of the the transaction
	// 		$this->load->view('vac-header');
	// 		$this->load->view('vaccine/loanreturn-success', $data);
	// 		$this->load->view('vac-footer');

	// 	} //End else
	// } //End LoanReturn()



/**************************/
/*BEGIN CALLBACK METHOD SECTION*/

	// //Callback validation method for Order form
	// public function CheckDropdownSelect($anOptionValue)
	// {
	// 	if($anOptionValue == '-1' or $anOptionValue == null)
	// 	{
	// 		return FALSE;
	// 	}
	// 	else
	// 	{
	// 		return TRUE;
	// 	}
	// } //End CheckDropdownSelect


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
	} //End CheckLot

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
	}

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
	}

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
		//var_dump($aBarcodeArray);
		//echo $isSaleNDC;
		//echo $aBarcodeArray['ndc10'];


		//Get all vaccines with the barcode's ndc
		if($isSaleNDC == 'TRUE') //If SaleNDC is true, then the carton ndc value is passed to GetVaccine (the 2nd argument in that function is FALSE b/c it the 2nd argument asks if the vaccine was administered - so in this case, FALSE)
		{
			$aVaccineArray = $aVaccine->GetVaccine($aBarcodeArray['ndc10'], FALSE);
			//var_dump($aVaccineArray);


			// $this->form_validation->set_message("CheckBarcodeInventory", "SaleNDC True");
			// return FALSE;

		}
		else //If SaleNDC is false, then a vial ndc value is passed to GetVaccine (& the 2nd argument, $vacAdministerd should equal TRUE)
		{
			$aVaccineArray = $aVaccine->GetVaccine($aBarcodeArray['ndc10'], TRUE);
			//var_dump($dumb);
			//var_dump($aBarcodeArray['ndc10']);
			//var_dump($aVaccineArray);

			// var_dump($aVaccineArray);
			// $this->form_validation->set_message('CheckBarcodeInventory', "SaleNDC False");
			// return FALSE;


			

			// $qry = 
			// 	"SELECT MIN(DrugID) as PackageDrugID 
			// 	FROM `fda_drug_package` 
			// 	WHERE SaleNDC10 IN (
			// 		SELECT SaleNDC10
			// 		FROM `fda_drug_package` 
			// 		WHERE DrugID = '".$aVaccineArray[0]->{'Drug ID'}."')";

			// $result = $this->db->query($qry);
			// $resultArray = $result->result();

			//$aVaccineArray['PackageDrugID'] = $resultArray[0]->PackageDrugID; //Store the PackageDrugID for the GetMultiVacInventory
			//var_dump($aVaccineArray);

		}

		//var_dump($aVaccineArray);


		$drugIDArray; //Declare array variable

		//var_dump($aVaccineArray);

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

				$drugIDArray[0] = $aVaccine->GetPackageDrugID($aVaccineArray[0]->{'Drug ID'}); //$aVaccineArray[0]->PackageDrugID;
				//var_dump($drugIDArray);
			}
		}
		else //If $aVaccineArray contains more than 1 vaccine (& thus more than 1 DrugID), loop through the $aVaccineArray to create a DrugID array.
			 //This array will be used to check inventory for each DrugID. If a DrugID in the array doesn't have inventory, then that drug won't be
			 //listed in the dropdown in the SelectVacFromList page (b/c there isn't any inventory of that drug).
		{
			$counter = 0;

			//var_dump($aVaccineArray);

			foreach($aVaccineArray as $vaccine)
			{
				$drugIDArray[$counter] = $vaccine->{'Drug ID'};
				$counter++;
			}

		}

		//var_dump($drugIDArray);

		$inventoryArray = $aVaccine->GetMultiVacInventory($drugIDArray);
		//var_dump($inventoryArray);

		//$this->form_validation->set_message("CheckBarcodeInventory", "Inventory Array");
		//return false;


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

	}

	//Check the number of doses from the Administer or LoanOut forms against the number of doses listed in the database
	public function CheckDoseQty($numDoses) // $maxDoseQty, $numPackages = null) //$numDoses is first in the list b/c that's the value which will be coming directly from the input control (the other values come from other sources)
	{
		//var_dump($this->session->selectedLot);
		//var_dump($this->session->MaxDoseAndPackageArray);

		$index = $this->session->selectedLot;
		$maxDoseAndPackageArray = $this->session->MaxDoseAndPackageArray;

		$maxDoseQty = $maxDoseAndPackageArray[$index][0]; //Lot Index session variable will always be 1 more than the array b/c the select element has "Select Lot Number" in index position 0

		//var_dump($maxDoseQty);

		// if($maxDoseAndPackageArray[$index][1] != null) //If value in column 2 of maxDoseAndPackageArray is not null (meaning if a value is stored there), then the loanout feature is calling this function.
		// {
			$packageQty = $maxDoseAndPackageArray[$index][1];
		// }
		// else
		// {
		// 	$packageQty = null;
		// }


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
		else //If $totalNumDoses
		{
			return TRUE;
		}

		//$data['maxDoseQty'] = ;
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


/*END CALLBACK METHOD SECTION*/
/*************************/


/*
===============================================================
===============================================================
===============================================================
===============================================================
===============================================================
===============================================================
*/

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
			//If button pressed, then 
			//if()

		} //End Else

	} //End UpdatePriceAndCost

	public function ManageUsers()
	{
		$this->load->view('vac-header');
		$this->load->view('vaccine/manage-users');
		$this->load->view('vac-footer');
	} //End ManageUsers()

//===========================
//===========================
//Original ManageUsers()
//===========================
//===========================

// 	{
// 		// var_dump((!isset($_POST['registerUserSubmit']) && !isset($_POST['manageUserSubmit'])));
// 		// //var_dump(!isset($_POST['manageUserSubmit']));

// 		// var_dump((isset($_POST['registerUserSubmit']) && !isset($_POST['manageUserSubmit'])));
// 		// //var_dump(!isset($_POST['manageUserSubmit']));

// 		// var_dump((!isset($_POST['registerUserSubmit']) && isset($_POST['manageUserSubmit'])));
// 		// //var_dump(isset($_POST['manageUserSubmit']));

// 		if((!isset($_POST['registerUserSubmit']) && !isset($_POST['manageUserSubmit']))) //Display 'manage-users' view if submit button on either form has not been clicked
// 		{
// 			// echo 'if...';
// 			 $data['feedback'] = '';
// 			 $this->session->UserForm = 'register';

// 			$this->load->view('vac-header');
// 			$this->load->view('Vaccine/manage-users', $data);
// 			$this->load->view('vac-footer');
// 		}
// 		elseif(isset($_POST['registerUserSubmit']) && !isset($_POST['manageUserSubmit'])) //Validate register form controls & register user if submit button on register form has been clicked
// 		{
// 			//echo "Register";
// 			//self::RegisterUser();
// 			// $this->load->view('dont know');
// 			// 'else if #1...';

// 			$this->session->UserForm = 'register';

// 			//validation rules
// 			$this->form_validation->set_rules('registerUsername', 'Username', 'required'); //Unique patient identifier
// 			$this->form_validation->set_rules('registerPassword', 'User Password', 'required');	//Password
// 			$this->form_validation->set_rules('registerEmail', 'User Email', 'required'); //User email

// 			$this->form_validation->set_rules('registerFName', 'First Name', 'required'); //First name
// 			$this->form_validation->set_rules('registerLName', 'Last Name', 'required'); //Last name
// 			$this->form_validation->set_rules('registerUserRole', 'User Role', 'callback_CheckUserRole'); //System role (admin, general user, etc.)

			
// 			if($this->form_validation->run() === FALSE) //If fields are complete, return the form
// 			{
// 				$data['feedback'] = $this->form_validation->error_string();

// 				$this->load->view('vac-header');
// 				$this->load->view('vaccine/manage-users', $data);
// 				$this->load->view('vac-footer');
// 			} //End validation if
// 			else
// 			{
// 				//Get form data 
// 				//register method signature looks like the following: register($identity, $password, $email, $additional_data = array(), $group_ids = array())
// 				//(see Ion_auth.php file in 'portal/application/libraries/' directory)
// 				$username = $this->input->post('registerUsername');
// 				$password = $this->input->post('registerPassword');
// 				$email = $this->input->post('registerEmail');
// 				$additional_data = array(
// 						'first_name' => $this->input->post('registerFName'),
// 						'last_name' => $this->input->post('registerLName'),

// 					);

// 				$id = $this->input->post('registerUserRole');

// 				$group = array($id); //Assigns the role to which the user is assigned



// 				//Register user
// 				$returnVal = $this->ion_auth->register($username, $password, $email, $additional_data, $group);

// 				if(gettype($returnVal) == 'array') //Occurs if Ion_Auth $config['email_activation'] == TRUE; returns an array with the user's username, email, and activation code 
// 				{
// 					//Feedback for user
// 					$data['feedback'] = "The user was registered successfully.";
// 				//	$data['returnedValue'] = $returnVal; //returns user's username
// 					$data['username'] = $returnVal['identity'];
// 					$data['email'] = $returnVal['email'];
// 					$data['fname'] = $additional_data['first_name'];
// 					$data['lname'] = $additional_data['last_name'];
					

// 					$this->load->view('vac-header');
// 					$this->load->view('vaccine/manage-users-success', $data);
// 					$this->load->view('vac-footer');
// 				} //End if
// 				elseif(gettype($returnVal) == 'integer') //Occurs if Ion_Auth $config['email_activation'] == FALSE (returns the account's 'id' value from the 'users' table
// 				{
// 					//Feedback for user
// 					$data['feedback'] = "The user was registered successfully.";
// 				//	$data['returnedValue'] = $returnVal; //returns user's username
// 					$data['username'] = $username;
// 					$data['email'] = $email;
// 					$data['fname'] = $additional_data['first_name'];
// 					$data['lname'] = $additional_data['last_name'];
					

// 					$this->load->view('vac-header');
// 					$this->load->view('vaccine/manage-users-success', $data);
// 					$this->load->view('vac-footer');

// 				}
// 				else
// 				{
// 					//Feedback for user
// 					$data['feedback'] = "Registration was unsuccessful.";
// 					$data['returnedValue'] = $registeredReturnVal;

// 					$this->load->view('vac-header');
// 					$this->load->view('vaccine/manage-users', $data);
// 					$this->load->view('vac-footer');
// 				} //End else
// 			} //End validation else			
// 		} //End elseif

// 		elseif(!isset($_POST['registerUserSubmit']) && isset($_POST['manageUserSubmit'])) //Validate update form controls & update user if submit button on update form has been clicked
// 		{
// 			//echo "Manage";
// 			//self::UpdateUsers();
// //			echo 'else if #2...';
// 			// $hi = "blah blah blah";
// 			// var_dump($hi);

// 			$this->session->UserForm = 'manage'; //Controls which form is displayed in the manage-users view

// 			//Validation rules
// 			$this->form_validation->set_rules('manageUsername', 'Username', 'required');
// 			$this->form_validation->set_rules('manageEmail', 'Email', 'required');
// 			$this->form_validation->set_rules('manageFName', 'First Name', 'required');
// 			$this->form_validation->set_rules('manageLName', 'Last Name', 'required');


// 			//Run validation
// 			if($this->form_validation->run() === FALSE)
// 			{
				
// 				$data['feedback'] = $this->form_validation->error_string(); //'Field is missing';


// 				$this->load->view('vac-header');
// 				$this->load->view('vaccine/manage-users', $data);
// 				$this->load->view('vac-footer');

// 			} //End if
// 			else
// 			{


// 			} //End else


// 		} //End elseif



// 		//Form validation rules
// 		//$this->form_validation->set_rules();

// //		$data['feedback'] = '';


// //		if($this->form_validation->run() === FALSE)
// //		{
// //			$this->load->view('vac-header');
// //			$this->load->view('Vaccine/manage-users', $data);
// //			$this->load->view('vac-footer');
// //		}
// //		else
// //		{
// 			//Get the type of form submitted
// //			$formType = $this->input->post('formType');

// //			if($formType == 'register')
// //			{
// 				//Get form data


// 				//Register user


// 				//Feedback for user
// //				$data['feedback'] = "The user was registered successfully.";
// //			}
// //			else if ($formType == 'manage')
// //			{
// 				//Get form data
// //				$userID = $this->input->post('manageUserList');

// //				$email = $this->input->post('manageEmail');
// //				$fname = $this->input->post('manageFName');
// //				$lname = $this->input->post('manageLName');
// //				$password = $this->input->post('managePassword');

// //				$data = array(
// //						'first_name' => $fname,
// //						'last_name' => $lname,
// //						'email' => $email,
// //						'password' => $password
// //					);

// //				var_dump($data);

// 				//Update user (see Ion_Auth documentation for "update()" function: http://benedmunds.com/ion_auth/  (link valid as of 11/18/2015))
// 		//		$this->ion_auth->update($userID, $data);

// 				//User feedback
// //				$data['feedback'] = "The user was updated successfully.";
// //			}

// 			//Display success page
// //			$this->load->view('vac-header');
// //			$this->load->view('Vaccine/manage-users-success', $data);
// //			$this->load->view('vac-footer');
// //		}




//	} //End ManageUsers()

//===========================
//===========================
// End Original ManageUsers()
//===========================
//===========================



	// //Processes user registration
	// private function RegisterUsers()
	// {
	// 	//validation rules
	// 	$this->form_validation->set_rules('registerUsername', 'Username', 'required'); //Unique patient identifier
	// 	$this->form_validation->set_rules('registerPassword', 'User Password', 'required');	//Password
	// 	$this->form_validation->set_rules('registerEmail', 'User Email', 'required'); //User email

	// 	$this->form_validation->set_rules('registerFName', 'First Name', 'required'); //First name
	// 	$this->form_validation->set_rules('registerLName', 'Last Name', 'required'); //Last name
	// 	$this->form_validation->set_rules('registerUserRole', 'User Role', 'required'); //System role (admin, general user, etc.)

		

	// 	if($this->form_validation->run() === FALSE)
	// 	{
	// 		$this->load->view('vac-header');
	// 		$this->load->view('vaccine/manage-users');
	// 		$this->load->view('vac-footer');
	// 	} //End validation if
	// 	else
	// 	{
	// 		//Get form data 
	// 		//register method signature looks like the following: register($identity, $password, $email, $additional_data = array(), $group_ids = array())
	// 		//(see Ion_auth.php file in 'portal/application/libraries/' directory)
	// 		$username = $this->input->post('registerUsername');
	// 		$password = $this->input->post('registerPassword');
	// 		$email = $this->input->post('registerEmail');
	// 		$addtional_data = array(
	// 				'first_name' => $this->input->post('registerFName'),
	// 				'last_name' => $this->input->post('registerLName'),

	// 			);
	// 		$group = array("$this->input->post('registerUserRole')");


	// 		//Register user
	// 		$registeredReturnVal = $this->ion_auth->register($username, $password, $email, $additional_data, $group);

	// 		if($registeredReturnVal != FALSE)
	// 		{
	// 			//Feedback for user
	// 			$data['feedback'] = "The user was registered successfully.";
	// 			$data['returnedValue'] = $registeredReturnVal;

	// 			$this->load->view('vac-header');
	// 			$this->load->view('vaccine/manage-users-success', $data);
	// 			$this->load->view('vac-footer');
	// 		} //End if
	// 		else
	// 		{
	// 			//Feedback for user
	// 			$data['feedback'] = "Registration was unsuccessful.";
	// 			$data['returnedValue'] = $registeredReturnVal;

	// 			$this->load->view('vac-header');
	// 			$this->load->view('vaccine/manage-users', $data);
	// 			$this->load->view('vac-footer');
	// 		} //End else
	// 	} //End validation else

	// } //End RegisterUsers()

	// //Processes updating user information
	// private function UpdateUsers()
	// {
	// 	//validation rules


	// 	if($this->form_validation->run() === FALSE)
	// 	{
	// 		$this->load->view('vac-header');
	// 		$this->load->view('vaccine/manage-users');
	// 		$this->load->view('vac-footer');
	// 	}
	// 	else
	// 	{
	// 		//Get form data
	// 		$userID = $this->input->post('manageUserList');

	// 		$email = $this->input->post('manageEmail');
	// 		$fname = $this->input->post('manageFName');
	// 		$lname = $this->input->post('manageLName');
	// 		$password = $this->input->post('managePassword');

	// 		$data = array(
	// 				'first_name' => $fname,
	// 				'last_name' => $lname,
	// 				'email' => $email,
	// 				'password' => $password
	// 			);

	// 		var_dump($data);

	// 		//Update user (see Ion_Auth documentation for "update()" function: http://benedmunds.com/ion_auth/  (link valid as of 11/18/2015))
	// //		$this->ion_auth->update($userID, $data);

	// 		//User feedback
	// 		$data['feedback'] = "The user was updated successfully.";
		

	// 		//Display success page
	// 		$this->load->view('vac-header');
	// 		$this->load->view('Vaccine/manage-users-success', $data);
	// 		$this->load->view('vac-footer');

	// 	}


	// } //End UpdateUsers()


/*

public function ScanBarcode()
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
		$this->form_validation->set_rules('vaccine-action', 'Vaccine Action', 'required');

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
			$this->session->vaccineaction = $this->input->post('vaccine-action');

			//Parse Barcode & Get Vaccine
			if ($this->session->vaccineaction == 'administer') 
			{
				//Parse Barcode
				$barcodeArray = $vaccine->ParseBarcode($this->session->barcode, FALSE);

				//Get vaccine
				$vaccineArray = $vaccine->GetVaccine($barcodeArray['ndc10'], TRUE);
			}
			else
			{
				$barcodeArray = $vaccine->ParseBarcode($this->session->barcode, TRUE);
				$vaccineArray = $vaccine->GetVaccine($barcodeArray['ndc10'], FALSE);

				if(count($vaccineArray) < 1)
				{
					//If $vaccineArray's count = 0, it means the user scanned a "Use" rather than "Sale" barcode & thus the incorrect database query was used
					//Display error message to user
					$this->session->error = "Please Scan a Box/Carton Barcode Rather than a Vial Barcode For Loans and Invoices";

					//Reload scan-barcode page
					redirect('Inventory/ScanBarcode', 'refresh');
				}
			}

			//Determine whether multiple vaccines share the same ndc or whether it's one vaccine
			//If multiple vaccines, go to the "selectvaccine" method
			if(count($vaccineArray) > 1)
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
				switch($this->session->vaccineaction)
				{
					case "invoice":
						redirect('Inventory/Invoice');
						break;

					case "administer":
						redirect('Inventory/Administer');
						break;

					case "loanout":
						redirect('Inventory/LoanOut');
						break;

					case "loanreturn":
						redirect('Inventory/LoanReturn');
						break;

					default:
						echo "Default...";
						break;
				} //End switch
			} //End else
		} //End else
	} //End ScanBarcode()

	public function SelectVacFromList()
	{
		//Load validation helpers
		$this->load->helper('form');
		$this->load->library('form_validation');

		//Data to pass to form
		$data['vacList'] = $this->session->vaccineArray;
		$data['ndc10'] = $this->session->barcodeArray['ndc10'];

		//Form validation
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
			$arrayIndex = $this->input->post('vaccineList');
			$vaccineArray = $this->session->vaccineArray;
			$barcodeArray = $this->session->barcodeArray;

			//Store variables for views in session variables
			$barcodeArray['drugID'] = $vaccineArray[$arrayIndex]->DrugID;
			$barcodeArray['clinicCost'] = $vaccineArray[$arrayIndex]->Drug_Cost;
			$barcodeArray['trvlPrice'] = $vaccineArray[$arrayIndex]->Trvl_Chrg;
			$barcodeArray['refugeePrice'] = $vaccineArray[$arrayIndex]->Refugee_Chrg;

			$this->session->barcodeArray = $barcodeArray;


			switch ($this->session->vaccineaction)
			{
				case 'invoice':
					redirect('Inventory/Invoice');
					break;

				case 'administer':
					redirect('Inventory/Administer');
					break;

				case 'loanout':
					redirect('Inventory/LoanOut');
					break;

				case 'loanreturn':
					redirect('Inventory/LoanReturn');
					break;

				default:
					echo "Default";
					break;
			} //End switch
		} //End else
	 } //End SelectVacFromList


/*
===============================================================
===============================================================
===============================================================
===============================================================
===============================================================
===============================================================
*/


/************************/
/* Begin AJAX Functions */

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
	//echo json_encode($resultArray);

	
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
	//$aCartonCode = $this->input->post('carton');
	//$aVialCode = $this->input->post('vial');

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

	// echo json_encode($ajaxResult);
	// //echo json_encode($resultArray);


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

	//http_response_code(500);

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

	//echo $sql;

	//Begin transaction
	$this->db->trans_begin();
	$this->db->query($sql);

	//echo json_encode("hi");

	if($this->db->trans_status() === FALSE) //Transaction Failed
	{
		$this->db->trans_rollback();
		http_response_code(500);
		return null;

//		echo json_encode(FALSE); //Feedback for user
	}
	else //Transaction Successful
	{
		$this->db->trans_commit();
	//	http_response_code(200);
		echo json_encode("success");
//		echo json_encode(TRUE); //Feedback for user
	}
	//End transaction


	//$this->db->trans_complete();
	//$this->db->trans_off(); //Turn off transactions

	
	// echo json_encode("Update Successful");
	// echo json_encode("Update Failed");

} //End ChangePriceCost()


//Used by manage-user view
function RegisterUser()
{
	//echo json_encode("Hi");

	//Get data passed from AJAX
	$username = $this->input->post('Username');
	$email = $this->input->post('Email');
	$password = $this->input->post('Password');

//	var_dump($minPassLength);

	// $this->config->loan('ion_auth', TRUE);
	// $minPassLength = $this->config->item('min_password_length', 'ion_auth');

	// if(strlen($password) < $minPassLength)
	// {
	// 	return;
	// }

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


//	echo json_encode("Hi");

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

	//echo json_encode("Hi");
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

//	echo $minPassLength;

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

	//var_dump($array);

	$action = $array['action'];
	$id = $array['id'];
	$name = $array['name'];
	$contact = $array['contact'];
	$phone = $array['phone'];
	$email = $array['email'];

	$sql = null;

	//Declare the added or edit borrower's id as a session variable
	$this->session->borrowerID = null;

	//var_dump($object);

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
//	var_dump($transType);
//	var_dump($returnType);

//	$isJSONReturnType = isset($returnType);
//	var_dump($isJSONReturnType);
	$filteredResults = null; //declare variable so scope is higher than if statement 

//	if(($transType != 'all') || ($transType != 'invoice') || ($transType != 'administer') || ($transType != 'loanout') || ($transType != 'loanreturn') || ($transType != 'outstandingloan')) //if($transType == null) //If there is no post data for 'transType', then the user has just navigated to the page & has not made a selection. In this case, all transactions should be selected
//	{
//	 	$transType = "all";

		//	var_dump($transType);
			//var_dump($theReport->TransactionsByType($transType));

//		$filterResults = $theReport->TransactionsByType($transType);


		//	var_dump($filterResults);

			//var_dump($filterResults);

			// if($isJSONReturnType)
			// {
			// 	echo json_encode($filterResults);	
			// }
			// else
			// {
			// 	return $filterResults;
			// }
			

		//	return $filterResults; //echo json_encode //return
			//var_dump($filterResults);

//	}

//	else
//	{
		$filterResults = $theReport->TransactionsByType($transType);

			//var_dump($filterResults);


			// echo json_encode($filterResults);
//	}


	// if($isJSONReturnType == TRUE)
	// {
		echo json_encode($filterResults);
		//echo json_encode($filterResults);	//Original
//	}
	// else
	// {
	// 	return $filterResults;
	// 	//echo json_encode($filterResults);
	// 	//return json_encode($filterResults); //Original
	// }

		//echo json_encode("Hi");

		//var_dump($transType);

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


	//$str = $aTransID." ".$aLotNum." ".$anExpirationDate." ".$aTransQty." ".$transType;
	//var_dump($str);

	//echo json_encode($str);

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


} //str EditSingleTransaction()


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
	//echo json_encode("hi");

	// //Get id to search for requested transaction
	$transID = $this->input->post('TransID');
	$transType = $this->input->post('TransType');
	$sql = null;

	// echo $transID;
	// echo $transType;

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

	// //var_dump($resultArray);

	// //Return dose result
	echo json_encode($resultArray); //json_encode($resultArray);

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
		//'allLoans' (default value), borrower', 'vacName', 'signer', 'loanDate', 'lotNum', 'expireDate', 'doses'
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
					$qryOrderBy = //"WHERE b.borrowerid = $filterCriteria
								   "ORDER BY b.borrowerid";
					break;
				case "vacName":
					$qryOrderBy = "ORDER BY pr.nonproprietaryname";
								  // "WHERE pr.nonproprietaryname = $filterCriteria";
					break;
				case "signer":
					$qryOrderBy = //"WHERE lo.signer_name = $filterCriteria
								   "ORDER BY lo.signer_name";
					break;
				case "loanDate":
					$qryOrderBy = //"WHERE t.transdate = $filterCriteria
								   "ORDER BY t.transdate";
					break;
				case "lotNum":
					$qryOrderBy = //"WHERE vt.LotNum = $filterCriteria
								   "ORDER BY vt.LotNum";
					break;
				case "expireDate":
					$qryOrderBy = //"WHERE vt.ExpireDate = $filterCriteria
								   "ORDER BY vt.ExpireDate";
					break;
				case "doses":
					$qryOrderBy = //"WHERE lo.Total_Doses = $filterCriteria
								   "ORDER BY lo.Total_Doses";
					break;
			} //End switch

		} //End elseif
		else
		{
			switch($sortCriteria)
			{
				// case "borrower":
				// 	$qryWhere = "WHERE b.borrowerid = $filterCriteria";
				// 	break;
				// case "vacName":
				// 	$qryWhere = "WHERE pr.nonproprietaryname = $filterCriteria";
				// 	break;
				// case "signer":
				// 	$qryWhereClause = "WHERE lo.signer_name = $filterCriteria";
				// 	break;
				// case "loanDate":
				// 	$qryWhereClause = "WHERE t.transdate = $filterCriteria";
				// 	break;
				// case "lotNum":
				// 	$qryWhereClause = "WHERE vt.LotNum = $filterCriteria";
				// 	break;
				// case "expireDate":
				// 	$qryWhereClause = "WHERE vt.ExpireDate = $filterCriteria";
				// 	break;
				// case "doses":
				// 	$qryWhereClause = "WHERE lo.Total_Doses = $filterCriteria";
				// 	break;
				// default:

				// 	break;

				case "borrower":
					$qryOrderBy = "WHERE b.borrowerid = $filterCriteria
								   ORDER BY b.borrowerid";
					break;
				case "vacName":
					$qryOrderBy = "WHERE pr.nonproprietaryname = $filterCriteria
								   ORDER BY pr.nonproprietaryname";
					break;
				case "signer":
					$qryOrderBy = "WHERE lo.signer_name = $filterCriteria
								   ORDER BY lo.signer_name";
					break;
				case "loanDate":
					$qryOrderBy = "WHERE t.transdate = $filterCriteria
								   ORDER BY t.transdate";
					break;
				case "lotNum":
					$qryOrderBy = "WHERE vt.LotNum = $filterCriteria
								   ORDER BY vt.LotNum";
					break;
				case "expireDate":
					$qryOrderBy = "WHERE vt.ExpireDate = $filterCriteria
								   ORDER BY vt.ExpireDate";
					break;
				case "doses":
					$qryOrderBy = "WHERE lo.Total_Doses = $filterCriteria
								   ORDER BY lo.Total_Doses";
					break;


			} //End switch

		} //End else

		
		// //Order By Clause
		// switch($sortCriteria)
		// {
		// 	case "all":
		// 		$qryOrderBy = "ORDER BY lo.loanid";
		// 		break;
		// 	case "borrower":
		// 		$qryOrderBy = //"WHERE b.borrowerid = $filterCriteria
		// 					   "ORDER BY b.borrowerid";
		// 		break;
		// 	case "vacName":
		// 		$qryOrderBy = "ORDER BY pr.nonproprietaryname";
		// 					  // "WHERE pr.nonproprietaryname = $filterCriteria";
		// 		break;
		// 	case "signer":
		// 		$qryOrderBy = //"WHERE lo.signer_name = $filterCriteria
		// 					   "ORDER BY lo.signer_name";
		// 		break;
		// 	case "loanDate":
		// 		$qryOrderBy = //"WHERE t.transdate = $filterCriteria
		// 					   "ORDER BY t.transdate";
		// 		break;
		// 	case "lotNum":
		// 		$qryOrderBy = //"WHERE vt.LotNum = $filterCriteria
		// 					   "ORDER BY vt.LotNum";
		// 		break;
		// 	case "expireDate":
		// 		$qryOrderBy = //"WHERE vt.ExpireDate = $filterCriteria
		// 					   "ORDER BY vt.ExpireDate";
		// 		break;
		// 	case "doses":
		// 		$qryOrderBy = //"WHERE lo.Total_Doses = $filterCriteria
		// 					   "ORDER BY lo.Total_Doses";
		// 		break;

		// } //End switch



		//Query to assemble all currently outstanding loans
		$qry = 
		"SELECT
			lo.loanid as 'Loan ID',
			vt.drugid as 'Drug ID',
	/*		pr.proprietaryname as 'Proprietary Name', */
			pr.nonproprietaryname as 'Non-Proprietary Name',
			b.entityname as 'Borrower',
			b.borrowerid as 'Borrower ID',
			lo.signer_name as 'Loan Signer',
			t.transdate as 'Loan Date',
			vt.LotNum as 'Lot Number',
			vt.ExpireDate as 'Expiration Date',
			lo.Total_Doses as 'Total Doses'
			/* (lo.doses_per_package * lo.packageqty) as 'Total Doses Loaned' */
		FROM
			`fda_product` as pr inner join 
			`fda_drug_package` as pa on pr.productid = pa.productid inner join
			`vaccinetrans` as vt on vt.drugid = pa.drugid inner join 
			`generic_transaction` as t on t.transid = vt.transid inner join
			`loanout` as lo on lo.loanid = vt.transid inner join
			`borrower` as b on b.borrowerid = lo.borrowerid";


		//Add where clause and order by clause to initial $qry variable
		$qryCombined = $qry.$qryWhere.$qryOrderBy;

		var_dump($qryOrderBy);
		var_dump($qryWhere);
		var_dump($qryCombined);


		$qryResult = $this->db->query($qryCombined);

		$resultArray = $qryResult->result();

		//Store table data in variable to pass to view
//		$data['tblSummary'] = $this->table->generate($qryResult);

		//Headings for the data to be returned to AJAX calling function
		//The headings come from the SQL query results' column headings
		$header = array(
			//	'Proprietary Name',
				'Non-Proprietary Name',
				'Borrower',
				'Loan Signer',
				'Loan Date',
				'Lot Number',
				'Expiration Date',
				'Total Doses'
			); //Names of database result columns
		$tableData = $resultArray;
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
	$transTimestamp = date('Y-m-d H:i:s'); //time();

	//Employee conducting the transaction
	$userID = $this->session->userdata('user_id'); //Pulled this from ion_auth_model.php's "user()" function

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
	// echo json_encode($transID);

	// break;

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
			var_dump($dateArray);

			$expireDate = $year."-".$month."-".$day;
			echo $expireDate;

			$sqlType = "INSERT INTO dose_return_type (RETURN_ID, DRUGID, LOTNUM, EXPIREDATE, DOSE_QTY)
						VALUES ($transID, '$drugID', '$lotNum', '$expireDate', $doseQty)";
			break;
		default:
			//An error occurred
			http_response_code(500);
			break;
	}

	//Insert return type transaction
	$this->db->query($sqlType);
	


	// $resultArray = array('Type' => $type, 'UserID' => $userID);

	// echo json_encode($resultArray);

	echo json_encode("Return Success!");

} //End LoanReimbursement()


//Get range of loan filter options (used by loanreimburse.php view to populate the <select> element)
function GetLoanFilterOptions()
{
	$filterCategory = $this->input->post('FilterCategory');

	$filterField = null; //Assigned in the switch statement
	$fieldName = null; //Assigned in the switch statement

	switch($filterCategory)
	{
		case 'vacName':
			$filterField = 'pr.nonproprietaryname';
			break;
		case 'borrower':
			$filterField = 'b.entityname';
			break;
		case 'signer':
			$filterField = 'lo.signer_name';
			break;
		case 'loanDate':
			$filterField = 't.transdate';
			break;
		case 'lotNum':
			$filterField = 'vt.LotNum';
			break;
		case 'expireDate':
			$filterField = 'vt.ExpireDate';
			break;
		case 'doses':
			$filterField = 'lo.Total_Doses';
			break;
		default:
			break;
	} //End switch

	//Query
	$sql = "SELECT $filterField
			FROM generic_transaction t INNER JOIN vaccinetrans vt on t.transid = vt.transid
				 INNER JOIN fda_drug_package pa on pa.drugid = vt.drugid
				 INNER JOIN fda_product pr on pr.productid = pa.productid
				 INNER JOIN loanout lo on lo.loanid = vt.transid
                 INNER JOIN borrower b on b.borrowerid = lo.borrowerid
			GROUP BY $filterField";

	$result = $this->db->query($sql);
	$resultArray = $result->result();



	//Return filter options

} //End GetLoanFilterOptions()


/* End AJAX Functions */





} //End Inventory controller class

?>