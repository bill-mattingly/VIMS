<?php

class Vaccine extends CI_Model
{
	//Class Variables
//	private $packageNDC; //type string //stores the NDC ("National Drug Code") value on the vaccine's box
	private $drugID; //type int //stores a vaccine's DrugID (from the fda_drug_package table)
	private $saleNDC; //type string //stores the NDC value on a vaccine's containing box/package
	private $unitNDC; //type string //stores the NDC value on a single dose of vaccine
//	private $linBarcode; //type string //stores linear ("traditional 1D") barcode
//	private $qrBarcode; //type string //stores QR ("2D") barcode
	private $expireDate; //type date (YYYY/MM/DD format) //stores vaccine expiration date 
	private $lotNum; //type string //stores vaccine lot number
	private $labelerName; //type string //stores manufacturer's name
	private $vacFormalName; //type string //stores vaccine's formal (proprietary) name
	private $vacCommonName; //type string //stores vaccine's common name
//	private $transQty; //type int //stores the # of individual vaccine units in a transaction (a transaction could be administering to patient, purchasing vaccine, loaning out vaccine, or receiving payment for a vaccine loan)
//	private $transDate; //type date (YYYY/MM/DD format) //stores the date the transaction took place
//	private $clinicCost; //type float //stores the vaccine's cost to the clinic
//	private $repName; //type string //stores the first and last name of the drug rep who sold vaccine to the clinic	
//	private $customerPrice; //type float //stores price the customer was charged when administed (value could range from "null" to any positive value)

	//Constructor
	public function __construct(){
		$this->load->database();
	}


	//Custom Methods


	//Add Transaction


	//Accepts a Linear or 2D Barcode and parses out the 10 digit NDC code, the expiration date, and lot number
	//Returns 2 possible values: 
	//1) A 1 element array with an NDC code in element[0] if a 1D (Linear) Barcode was passed to the function or
	//2) A 3 element array with the parsed NDC, expiration, and lot number
	public function ParseBarcode($aBarcodeVal, $isASaleNDC){
		//Declare & initialize variables
		$barcode = $aBarcodeVal;//Stores the barcode value passed to the function
		$theNDC = null; //Stores the vaccine 10 digit NDC number
		$theExDate = null; //Stores the vaccine Expiration Date
		$theLotNum = null; //Stores the vaccine Lot Number
		$barcodeArray = array('ndc' => null,
							  'exDate' => null,
							  'lotNum' => null 
							 ); //Stores the ndc code, expiration date, and lot number. This array is returned by the function to the calling method
		
		//Check barcode length
		//Handles 12 Digit 1D (Linear) Barcodes (most 1D barcodes)
		if(strLen($barcode) <= 12){
			//Remove first & last digit
			$barcode = substr($barcode, 1, 10); //Removes the first & last digits			
			echo "Here is the barcode (ParseBarcode): $barcode<br/>";
			echo "Here is the bool val:".(string)$isASaleNDC;
			$theNDC = $this->ParseNDC($barcode, $isASaleNDC); //$barcode var has now been stripped to the 10 digit ndc code. Call ParseNDC method on $barcode and store in $theNDC variable.

			$barcodeArray["ndc"] = $theNDC; //Update the barcodeArray variable to include the parsed ndc value
			return $barcodeArray; //Return the array to the calling method
		}

		//Handles 1D (Linear) Barcode with 13 digits
		else if(strLen($barcode) == 13){
			$barcode = substr($barcode, 0, -3);

			$theNDC = $this->ParseNDC($barcode, $isASaleNDC); //$barcode var has now been stripped to the 10 digit ndc code. Call ParseNDC method on $barcode and store in $theNDC variable.

			$barcodeArray["ndc"] = $theNDC; //Update the barcodeArray variable to include the parsed ndc value
			return $barcodeArray; //Return the array to the calling method
		}

		//Handles 2D (QR) Barcodes
		else if(strLen($barcode) == 31){

		}

		//Handles all barcode strings that fall outside the previous rules
		//Returns an error to the user
		else{
			$errorStr = "The Barcode was an Unrecognized Length";
			return $errorStr;
		}

	} //End ParseBarcode()

	//Accepts an NDC Code and parses out the 10 digit NDC code
	//Returns the parsed code (i.e. in 5-3-2 or 5-4-1 format)
	public function ParseNDC($anNDC_Val, $isASaleNDC)
	{
		//Declare and initialize method variables
		$theNDC10 = $anNDC_Val; //Stores the full 10 digit NDC code
		$prod53; //Stores NDC product code as the first 8 digits of the 10 digit NDC (5-3 format)
		$prod54; //Stores NDC product code as the first 9 digits of the 10 digit NDC (5-4 format)
		$prod44; //Stores NDC product code as the first 8 digits of the 10 digit NDC (4-4 format)

		$aVaccine; //Stores a vaccine object

		//echo $theNDC;

		//Determine whether NDC is 5-3-2, 5-4-1, or 4-4-2 format
		//Split into 3 possible product strings: 5-3, 5-4, or 4-4 format (to search database with)
		//Label Num = First 4 or 5 digits (4-4-2 format or 5-3-2 or 5-4-1 format respectively)
		//Product Num = Middle 3 or 4 digits (5-3 format or 5-4 or 4-4 format respectively)
		//Package Num = Last 1 or 2 digits (5-4-1 format or 5-3-2 or 4-4-2 format respectively)
		$labelNum5 = strval(substr($theNDC10, 0, 5));
		$labelNum4 = strval(substr($theNDC10, 0, 4));
		$prodNum53 = strval(substr($theNDC10, 5, 3));
		$prodNum54 = strval(substr($theNDC10, 5, 4));
		$prodNum44 = strval(substr($theNDC10, 4, 4));
		$packageNum2 = strval(substr($theNDC10, 8, 2));
		$packageNum1 = strval(substr($theNDC10, 9, 1));

		// //Store the 2 possible product strings in variables
		// $prod53 = "$labelNum5-$prodNum53";
		// $prod54 = "$labelNum5-$prodNum54";
		// $prod44 = "$labelNum4-$prodNum44";

		$prod532 = "$labelNum5-$prodNum53-$packageNum2";
		$prod541 = "$labelNum5-$prodNum54-$packageNum1";
		$prod442 = "$labelNum4-$prodNum44-$packageNum2";

		$vaccineExists = $this->FindNDCFormat($prod532, $isASaleNDC);

		if($vaccineExists == TRUE)
		{
			echo "TRUE";
		}
		else
		{
			echo "FALSE";
		}


		if($vaccineExists)
		{
			$theNDC10 = $prod532;
		}
		else
		{
			$vaccineExists = $this->FindNDCFormat($prod541, $isASaleNDC);

			if($vaccineExists)
			{
				$theNDC10 = $prod541;
			}
			else
			{
				$theNDC10 = $prod442;
			}
		}


		// //Call to SearchForNDC method to determine which format (5-3-2 or 5-4-1) the NDC value should be
		// //First search for $prod53 string
		// $vaccineExists = $this->FindNDCFormat($prod53);
		// echo "Here's the value in vaccineExists: $vaccineExists";

		// if($vaccineExists)
		// {
		// 	//NDC is 5-3-2 format if this if branch executes, so format NDC accordingly
		// 	//$theNDC = $prod53 + "-" + substr($theNDC, 7, 2); (doesn't play nice - prefers to do addition on the strings)
		// 	$theNDC10 = "$prod53-$packageNum2";
		// 	//echo $theNDC;
		// }

		// else{
		// 	//NDC is 5-4-1 or 4-4-2 format if the else branch executes. Check if 5-4 format can be found in database.
		// 	$vaccineExists = $this->FindNDCFormat($prod54);

		// 	if($vaccineExists)
		// 	{
		// 		$theNDC10 = "$prod54-$packageNum1";
		// 	}
		// 	else
		// 	{
		// 		$theNDC10 = "$prod44-$packageNum2";
		// 	}
		// }

		//Return NDC number
		//echo $theNDC10;
		return $theNDC10;

	} //End ParseNDC()


	//Searches the database for an NDC code or partial code & returns a bool value indicating whether or not a vaccine was found
	public function FindNDCFormat($anNDC, $isASaleNDC)//$aProdNDC_Code){
	{
		//Declare & initialize method variables
		//$aVaccine = new Vaccine(); //Stores a vaccine object
		$qry; //Stores a database query string
		$result; //Stores a database query result set

		$vaccineFound; //Type bool. Stores True if qry returns a result set. Stores False if qry returns an empty set.

		//Search database for Product 8 and Product 9 number
		//$qry = "SELECT ProductNDC FROM `FDA_Product` WHERE ProductNDC = '$anNDCProd_Segment'";

		if($isASaleNDC)
		{
			$qry = "SELECT DRUGID FROM `fda_drug_package` WHERE SALENDC10 = $anNDC";
		}
		else
		{
			$qry = "SELECT DRUGID FROM `fda_drug_package` WHERE USENDC10 = $anNDC";
		}

		echo $qry;

		$result = $this->db->query($qry);
		echo "Here is the query count: ".count($result);

		if(count($result) > 0)//$result->num_rows() > 0)
		{
			
			$vaccineFound = True;
			return $vaccineFound;

			/*$counter = 0;
			foreach ($result as $row)
			{
				$row = $result->row_array();
				//$row = $result->row();
				//Set the values of the vaccine object according to the database values
				//$aVaccine->saleNDC =
				//echo count($row);
				//echo $row['PROPRIETARYNAME'];
				//echo $row['PRODUCTNDC'];

				$counter += 1;
				if($counter >= 1)
				{
					break;
				}
			} */

		}
		else //executes if no vaccine object was found
		{
			$vaccineFound = False;
			return $vaccineFound;

			//$aVaccine = null; //Set the $aVaccine object = null to indicate no vaccine object was found
		}

		//Return vaccine object
		//return $aVaccine;

	} //End FindNDCFormat()

	//Retrieves a vaccine from the database and returns an array of vaccine objects which share the same NDC10 code (doesn't matter whether its a "SaleNDC" or "UseNDC" code, the system will find the correct vaccine(s) based on the provided NDC code and extract the correct data)
	//$anNDC10_Code accepts a 12 character (10 digit with 2 "-" characters) NDC number
	//$vacAdministered accepts a boolean value indicating whether or not the vaccine was administered to a patient
	public function GetVaccine($anNDC10_Code, $vacAdministered)
	{
		//Method variables
		$vaccinesArray;
		//$aVaccine = new Vaccine(); //Stores a vaccine object
		$qry; //Type string. Stores a SQL query to select a vaccine
		$qryResult; //Stores a SQL query result set

		//Find vaccine based on whether the vaccine is being administered or recorded from an order invoice
		//The SQL query where clause changes based on the value in $vacAdminister
		if($vacAdministered)
		{
			$qry = "SELECT Package.DrugID, Package.SaleNDC10, Package.UseNDC10, Package.PackageDescrip, Product.ProprietaryName, Product.NonProprietaryName, Product.LabelerName FROM fda_drug_package Package inner join fda_product Product on Package.ProductID = Product.ProductID Where Package.UseNDC10 = '$anNDC10_Code'";
		}
		else
		{
			$qry = "SELECT Package.DrugID, Package.SaleNDC10, Package.UseNDC10, Package.PackageDescrip, Product.ProprietaryName, Product.NonProprietaryName, Product.LabelerName FROM fda_drug_package Package inner join fda_product Product on Package.ProductID = Product.ProductID Where Package.SaleNDC10 = '$anNDC10_Code'";
		}
		
		//Store query result set
		$qryResult = $this->db->query($qry);
		$qryArray = $qryResult->result();


		return $qryArray; //Recall that the value stored in $qryResult is an array of objects. In this case, query objects (thus they have all the fields from the query)

		/*
		//Add logic to print out a list of records if an NDCSale value returns multiple records (allow the user to select the appropriate vaccine (example where the user needs to select from multiple values: NDC value 58160-815-52 (2 Hep A/B values come up)))
		foreach ()


		$qryResultArray = $qryResult->row(0); //$qryResult->result_array(); 
		//var_dump($qryResultArray);

		//Assign query values to Vaccine object		
		//When referencing keys in the resultarray, you do not need to reference the table alias used in the Select part of the sql query.
		//Instead, reference column name as it appears in the table generated by the query
		$aVaccine->drugID = $qryResultArray->DrugID;
		$aVaccine->saleNDC = $qryResultArray->SaleNDC10;
		$aVaccine->useNDC = $qryResultArray->UseNDC10;
		$aVaccine->vacFormalName = $qryResultArray->ProprietaryName;
		$aVaccine->vacCommonName = $qryResultArray->NonProprietaryName;
		$aVaccine->labelerName = $qryResultArray->LabelerName;

		//Return vaccine object
		return $aVaccine;

		*/

	} //End GetVaccine()


	



	//Custom Methods
	//Occurs when an order is placed for a vaccine. Increases the quantity of an existing vaccine.
	public function Order($aDrugID){//$aBarcode)
		//Method variables
		//$selectedVaccine = new Vaccine();

		//Parse barcode
		//$barcodeArray = $this->ParseBarcode($aBarcode);

		//Identify selected vaccine
		//$selectedVaccine = $this->GetVaccine($barcodeArray['ndc'], FALSE);



		//Assign a lot number and expiration date (from the view) to the vaccine object
//		$selectedVaccine->expireDate = $this->input->post('expireDate');
//		$selectedVaccine->lotNum = $this->input->post('lotNum');

		//Record transaction in the Transaction, Vaccine_Trans, and Order_Invoice tables
		//$this->db->insert_batch('');

		//$timestamp = new DateTime();

		/*if(count($selectedVaccine) > 1)
		{
			//Return a list of the vaccines to the user for them to select the correct one
			return $selectedVaccine;
		}
		else
		{*/
			//Insert the selected vaccine into the database




			// //Insert data into database tables:
			// //Insert data into Transaction table
			// date_default_timezone_set('UTC');
			// $transTimestamp = date('Y-m-d H:i:s'); //time();
			// //date_default_timezone_set('America/New_York');
			
			// $transData = array(
			// 	'TransDate' => $transTimestamp//$timestamp->getTimestamp()
			// );

			// $this->db->insert('transaction', $transData);


			// //Insert into VaccineTrans table
			// $qry = "SELECT MAX(TransID) as TransID FROM `Transaction`";
			// $qryResult = $this->db->query($qry);

			// $row = $qryResult->result();

			// $transID = $row[0]->TransID; //$row is an array of objects. Thus, "$row[0]->TransID" references the array element in index position 0 & fetches the "TransID" property of the object stored in the first index of the array
			// $transData['TransID'] = $transID;
			// $transData['DrugID'] = $aDrugID;

			// $vacTrans = array(
			// 	"TransID" => $transID,
			// 	"DrugID" => $aDrugID,
			// 	//"SaleNDC10" => $barcodeArray['ndc'],
			// 	//"TransQty" => $this->input->post('transQty'),
			// 	"ExpireDate" => $this->session->expireDate,//$this->input->post('expireDate'),
			// 	"LotNum" => $this->session->lotNum//$this->input->post('lotNum')
			// 	);

			// $this->db->insert('VaccineTrans', $vacTrans);

			//Insert into Transaction & VaccineTrans tables
			$transData = $this->TransVacTransInsert($aDrugID);


			//Insert transaction into Order_Invoice
			$invoiceTrans = array(
				"OrderID" => $transData['TransID'],
				"Clinic_Per_Dose_Cost" => $this->session->clinicCost,//$this->input->post('clinicCost'),
				"PackageQty" => $this->session->packageQty,//$this->input->post('packageQty'),
				"Doses_Per_Package" => $this->session->dosesPerPackage//$this->input->post('dosesPerPackage')
				);

			$this->db->insert('Order_Invoice', $invoiceTrans);


			//Update and return $transData array to controller for display by the view
			

			return $transData;

		//} //End else

	} //End Order method

	//Occurs when a vaccine is administered to a patient. Decreases the quantity of an existing vaccine.
	public function Administer($aDrugID){ // ($aBarcode){
		//Method variables
		//$selectedVaccine = new Vaccine();

		// echo "<p>Here is the drug id: $aDrugID</p>";
		// echo "A vaccine was administered";



		//Parse barcode to find administered vaccine
//		$barcodeArray = $this->ParseBarcode($aBarcode);

//		echo "The parsed ndc is: ".$barcodeArray['ndc'];
	

		//After finding correct vaccine, record a transaction in the transaction & administered tables
		//First, add data to Transaction table
		

//		$this->db->insert();


		//Add data to Transaction and VaccineTrans tables
		$transData = $this->TransVacTransInsert($aDrugID);


		//Add to Administer table

		//Insert transaction into Order_Invoice
		$administerTrans = array(
			"AdministerID" => $transData['TransID'],
			//"PID" => $this->session->clinicCost,//$this->input->post('clinicCost'),
			"Cust_Per_Dose_Chrg" => $this->session->customerChrg,//$this->input->post('packageQty'),
			"Doses_Given" => $this->session->doseQty//$this->input->post('dosesPerPackage')
			);

		$this->db->insert('Administer', $administerTrans);


		//Update and return $transData array to controller for display by the view
		return $transData;
	}

	//Occurs when a medical group asks the clinic if it can borrow vaccine from the clinic's inventory. Decreases clinic inventory.
	public function LoanOut($aDrugID)
	{
		//Enter transaction into database
		

		//Add data to Transaction & VaccineTrans tables

		//Add data to LoanOut table


	}

	//Occurs when a medical group returns vaccine to the clinic to return borrowed vaccine. Increases vaccine inventory
	public function LoanReturn(){

	}

	//Adds data to the Transaction table
	public function AddTransaction()
	{
		//Method variables
		$saleNDC10; //Stores a vaccine's SaleNDC number (10 digit number)
		$productNum8; //Stores a vaccine's product number in 5-3 (8 digit) format. Used to search FDA drug database
		$productNum9; //Stores a vaccine's product number in 5-4 (9 digit) format. Used to search FDA drug database
		$prodFound; //Bool value to determine if a product num was found

		//Process SaleNDC10 number from scanned barcode
		//$val = $this->input->post('linBarcode');
		//echo "Here is what's in the form: " + $this->input->post('linBarcode');

		//$saleNDC10 = $this->ParseBarcode($this->input->post('linBarcode'));//$val);
		//echo $saleNDC10['ndc'];

		/*$data = array(
			'SaleNDC10' => $saleNDC10['ndc']
			);
		*/

		//return $this->db->insert('transaction', $data);


		/*
		//Add data to database
		$transTbl = array(
			'SaleNDC10' => $saleNDC10;
			'TransDate' => $this->input->post('expireDate');
			'LotNum' => $this->input->post('lotNum');
			'TransQty' => $this->input->post('transQty');
		);
		$purchaseOrdreTbl = array(
			'Clinic_Cost' => $this->input->post('clinicCost');
		);
		*/

	} //End AddTransaction()


	// //Adds a new vaccine to the vaccine table (does not increase/decrease the quantity of an existing vaccine)
	// public function AddNewVaccine($aPackageNDC, $aUnitNDC, $aLinBarcode, $anExpireDate, $aLotNum, $aMakerName, $aVacFormalName, $aVacCommonName, $aRepName)
	// {

	// }

	public function GetMostRecentTrans($aTransID)
	{
			$qryTransItem = "
			SELECT Pr.ProprietaryName as 'Proprietary Name', Pr.NonProprietaryName as 'Non-Proprietary Name', 
			Pr.LabelerName as 'Labeler Name', Pa.PackageDescrip as 'Description', T.TransDate as 'Transaction Date', 
			Vt.ExpireDate as 'Expiration Date', Vt.LotNum as 'Lot Number', Oi.Clinic_Per_Dose_Cost as 'Per Dose Cost', 
			Oi.PackageQty as 'Package Qty', Oi.Doses_Per_Package as 'Doses Per Package'
			FROM `fda_product` Pr inner join `fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
			`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
			`transaction` T on Vt.TransId = T.TransID inner join
			`order_invoice` Oi on Vt.TransID = Oi.OrderID
			WHERE Vt.TransID = $aTransID"; //Provides info on the most recently inserted transaction

			$qryResult = $this->db->query($qryTransItem);
			$qryArray = $qryResult->result();

			return $qryArray;
	}

	public function GetSingleVacSum($aDrugID)
	{
			$qryVacQty = "
			SELECT Pr.ProprietaryName as 'Proprietary Name', Pr.NonProprietaryName as 'Non-Proprietary Name', 
			Pr.LabelerName as 'Labeler Name', Pa.PackageDescrip as 'Description', SUM(Oi.Clinic_Per_Dose_Cost * Oi.PackageQty * Oi.Doses_Per_Package)/SUM(Oi.PackageQty * Oi.Doses_Per_Package) As 'Weighted Average Cost', SUM(Oi.PackageQty) As 'Total Packages', SUM(Oi.PackageQty * Oi.Doses_Per_Package) As 'Total Doses'
			FROM `fda_product` Pr inner join `fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
			`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
			`transaction` T on Vt.TransId = T.TransID inner join
			`order_invoice` Oi on Vt.TransID = Oi.OrderID
			WHERE Vt.DrugID = $aDrugID
			Group By Vt.DrugID"; //Gives a current weighted average cost per dose & total dose quantity for a vaccine

			$qryResult = $this->db->query($qryVacQty);
			$qryArray = $qryResult->result();

			return $qryArray;
	}

	public function TransVacTransInsert($aDrugID)
	{
			//Insert data into Transaction table
			date_default_timezone_set('UTC');
			$transTimestamp = date('Y-m-d H:i:s'); //time();
			//date_default_timezone_set('America/New_York');
			
			$transData = array(
				'TransDate' => $transTimestamp//$timestamp->getTimestamp()
			);

			$this->db->insert('transaction', $transData);


			//Insert into VaccineTrans table
			$qry = "SELECT MAX(TransID) as TransID FROM `Transaction`";
			$qryResult = $this->db->query($qry);

			$row = $qryResult->result();

			$transID = $row[0]->TransID; //$row is an array of objects. Thus, "$row[0]->TransID" references the array element in index position 0 & fetches the "TransID" property of the object stored in the first index of the array
			$transData['TransID'] = $transID;
			$transData['DrugID'] = $aDrugID;

			$vacTrans = array(
				"TransID" => $transID,
				"DrugID" => $aDrugID,
				//"SaleNDC10" => $barcodeArray['ndc'],
				//"TransQty" => $this->input->post('transQty'),
				"ExpireDate" => $this->session->expireDate,//$this->input->post('expireDate'),
				"LotNum" => $this->session->lotNum//$this->input->post('lotNum')
				);

			$this->db->insert('VaccineTrans', $vacTrans);

			return $transData;
	}


}


?> 