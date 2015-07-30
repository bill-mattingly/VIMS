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

	//Accepts a Linear or 2D Barcode and parses out the 10 digit NDC code, the expiration date, and lot number
	//Returns 2 possible values: 
	//1) A 1 element array with an NDC code in element[0] if a 1D (Linear) Barcode was passed to the function or
	//2) A 3 element array with the parsed NDC, expiration, and lot number
	public function ParseBarcode($aBarcodeVal, $isASaleNDC){
		//Declare & initialize variables
		$barcode = $aBarcodeVal;//Stores the barcode value passed to the function
		$theNDC = null; //Stores the vaccine 10 digit NDC number
		$expireDate = null; //Stores the vaccine Expiration Date
		$lotNum = null; //Stores the vaccine Lot Number
		$barcodeArray = array('ndc' => null,
							  'expireDate' => null,
							  'lotNum' => null 
							 ); //Stores the ndc code, expiration date, and lot number. This array is returned by the function to the calling method
		
		//Check barcode length
		//Handles 12 Digit 1D (Linear) Barcodes (most 1D barcodes)
		if(strLen($barcode) <= 12){
			//Remove first & last digit
			$barcode = substr($barcode, 1, 10); //Removes the first & last digits			
			//echo "Here is the barcode (ParseBarcode): $barcode<br/>";
			//echo "Here is the bool val:".(string)$isASaleNDC;
			$theNDC = $this->ParseNDC($barcode, $isASaleNDC); //$barcode var has now been stripped to the 10 digit ndc code. Call ParseNDC method on $barcode and store in $theNDC variable.

			$barcodeArray["ndc"] = $theNDC; //Update the barcodeArray variable to include the parsed ndc value
			return $barcodeArray; //Return the array to the calling method
		}

		//Handles 1D (Linear) Barcode with 13 digits (example: Japanese Encephalitis Vaccine by Novartis (barcode val: 4251500101001))
		else if(strLen($barcode) == 13){
			$barcode = substr($barcode, 0, -3);

			$theNDC = $this->ParseNDC($barcode, $isASaleNDC); //$barcode var has now been stripped to the 10 digit ndc code. Call ParseNDC method on $barcode and store in $theNDC variable.

			$barcodeArray["ndc"] = $theNDC; //Update the barcodeArray variable to include the parsed ndc value
			return $barcodeArray; //Return the array to the calling method
		}

		//Handles 1D (Linear) Barcode with 16 digits (typically the 1D code on a vaccine vial. Example: Varicella Virus by Merck (1D barcode val: 0100300064827019))
		else if(strLen($barcode) == 16)
		{
			$barcode = substr($barcode, 5); //Strip out the first 5 digits ("01003")

			$theNDC = $this->ParseNDC($barcode, $isASaleNDC);

			$barcodeArray["ndc"] = $theNDC;
			return $barcodeArray;
		}

		//Handles 2D (QR) Barcodes
		else if(strLen($barcode) >= 31){

			//Parse barcode for ndc
			$theNDC = substr($barcode, 4, 10); //segment of barcode string with the NDC
			$theNDC = $this->ParseBarcode($theNDC, $isASaleNDC);

			//Parse barcode for expiration date
			$expireDate = substr($barcode, 18, 6);//segment of barcode string with the expiration date
			//Format expireDate for input into database
			//echo "expireDate before processing: $expireDate<br/>";
			$expireDate = "20".substr($expireDate, 0, 2)."-".substr($expireDate, 2, 2)."-".substr($expireDate, 4);
			//echo "Here is the expire date: $expireDate<br/>";

			//Parse barcode for lot number
			$lotNum = substr($barcode, 26);//segment of barcode string with the lot number
			//echo "Here is the lotnum: $lotNum<br/>";

			//Store values in barcode array
			$barcodeArray["ndc"] = $theNDC;
			$barcodeArray["expireDate"] = $expireDate;
			$barcodeArray["lotNum"] = $lotNum;
			return $barcodeArray;

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

		$prod532 = "$labelNum5-$prodNum53-$packageNum2";
		$prod541 = "$labelNum5-$prodNum54-$packageNum1";
		$prod442 = "$labelNum4-$prodNum44-$packageNum2";

		$vaccineExists = $this->FindNDCFormat($prod532, $isASaleNDC);

		//echo $prod532;
		//echo $isASaleNDC;

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

		return $theNDC10;

	} //End ParseNDC()


	//Searches the database for an NDC code or partial code & returns a bool value indicating whether or not a vaccine was found
	public function FindNDCFormat($anNDC, $isASaleNDC)//$aProdNDC_Code){
	{
		//Declare & initialize method variables
		$qry; //Stores a database query string
		$result; //Stores a database query result set
		$vaccineFound; //Type bool. Stores True if qry returns a result set. Stores False if qry returns an empty set.

		if($isASaleNDC)
		{
			//echo "Sale NDC is true";
			$qry = "SELECT DRUGID FROM `fda_drug_package` WHERE SALENDC10 = '$anNDC'";
		}
		else
		{
			//echo "Sale NDC is false";
			$qry = "SELECT DRUGID FROM `fda_drug_package` WHERE USENDC10 = '$anNDC'";
		}

		//echo $qry;

		$result = $this->db->query($qry);
		//echo "Here is the query count: ".count($result);
		//echo $result->num_rows();

		if($result->num_rows() > 0) //result()) > 0)//$result->num_rows() > 0)
		{
			$vaccineFound = True;
			return $vaccineFound;
		}
		else //executes if no vaccine object was found
		{
			$vaccineFound = False;
			return $vaccineFound;
		}

	} //End FindNDCFormat()

	//Retrieves a vaccine from the database and returns an array of vaccine objects which share the same NDC10 code (doesn't matter whether its a "SaleNDC" or "UseNDC" code, the system will find the correct vaccine(s) based on the provided NDC code and extract the correct data)
	//$anNDC10_Code accepts a 12 character (10 digit with 2 "-" characters) NDC number
	//$vacAdministered accepts a boolean value indicating whether or not the vaccine was administered to a patient
	public function GetVaccine($anNDC10_Code, $vacAdministered)
	{
		//Method variables
		$vaccinesArray;
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

	} //End GetVaccine()


	//Occurs when an order is placed for a vaccine. Increases the quantity of an existing vaccine.
	public function Order($aDrugID)
	{
			//Insert into Transaction & VaccineTrans tables
			$transData = $this->TransVacTransInsert($aDrugID);


			//Insert transaction into Order_Invoice
			$invoiceTrans = array(
				"InvoiceID" => $transData['TransID'],
				"Clinic_Per_Dose_Cost" => $this->session->clinicCost,//$this->input->post('clinicCost'),
				"PackageQty" => $this->session->packageQty,//$this->input->post('packageQty'),
				"Doses_Per_Package" => $this->session->dosesPerPackage//$this->input->post('dosesPerPackage')
				);

			$this->db->insert('Order_Invoice', $invoiceTrans);


			//Update and return $transData array to controller for display by the view
			return $transData;

	} //End Order method

	//Occurs when a vaccine is administered to a patient. Decreases the quantity of an existing vaccine.
	public function Administer($aDrugID)
	{
		//Add data to Transaction and VaccineTrans tables
		$transData = $this->TransVacTransInsert($aDrugID);

		//Obtain DrugID for the Package that the individual vial came in
		$qry = "SELECT MIN(DrugID) as PackageDrugID FROM `FDA_DRUG_PACKAGE` WHERE SALENDC10 IN (SELECT SALENDC10 FROM `FDA_DRUG_PACKAGE` WHERE DRUGID = $aDrugID)";
		$result = $this->db->query($qry);
		$resultArray = $result->result();

		//echo "Count of qry: ".count($resultArray);
		//var_dump($resultArray);
		$packageDrugID = $resultArray[0]->PackageDrugID;
		//echo "Here is the package drugid: ";
		//echo "$packageDrugID";


		//Add to Administer table
		$administerTrans = array(
			"AdministerID" => $transData['TransID'],
			"Package_DrugID" => $packageDrugID,
			//"PID" => $this->session->clinicCost,//$this->input->post('clinicCost'),
			"Cust_Per_Dose_Chrg" => $this->session->customerChrg,//$this->input->post('packageQty'),
			"Doses_Given" => $this->session->doseQty//$this->input->post('dosesPerPackage')
			);

		$this->db->insert('Administer', $administerTrans);

		//Update and return $transData array to controller for display by the view
		return $transData;
	}

	//Occurs when a medical group asks the clinic if it can borrow vaccine from the clinic's inventory. Decreases clinic inventory.
	public function LoanOut($aDrugID, $aBorrowerID)
	{
		//Enter transaction into database
		//Add data to Transaction & VaccineTrans tables
		$transData = $this->TransVacTransInsert($aDrugID);

		//Add data to LoanOut table
		$loanData = array(
			"LoanID" => $transData['TransID'],
			"BorrowerID" => $aBorrowerID,
			"PackageQty" => $this->session->PackageQty,
			"Doses_Per_Package" => $this->session->DosesPerPackage
		);

		$this->db->insert('loanout', $loanData);

		//Gather summary of the latest transaction (for display in a table)
		// $qry = "SELECT MAX(LoanID) as TransID FROM `LoanOut`";
		// $result = $this->db->query($qry);
		// $resultArray = $result->result();
		
		// $tblResult = $this->GetTransaction($resultArray[0]->TransID, FALSE);
		$tblSummary = $this->GetTransaction($transData['TransID'], 'loanout', FALSE);

		// echo "Here's the transID val:[<br/>";
		// echo $transData['TransID'];
		// echo "<br/>]";

		// echo "Here is what is in the tblsummary var:<br/>[<br/>";
		// var_dump($tblSummary);
		// echo "<br/>]";



		$transData['tblSummary'] = $tblSummary;

		//Return transaction data array
		return $transData;
	}

	//Occurs when a medical group returns vaccine to the clinic to return borrowed vaccine. Increases vaccine inventory
	public function LoanReturn($aDrugID, $aBorrowerID)
	{
		//Insert into Transaction & VaccineTrans
		$transData = $this->TransVacTransInsert($aDrugID);

		//Insert into LoanReturn
		$loanReturn = array(
			"ReturnID" => $transData['TransID'],
			"BorrowerID" => $aBorrowerID,
			"PackageQty" => $this->session->PackageQty,
			"Doses_Per_Package" => $this->session->DosesPerPackage
		);

		$this->db->insert('loanreturn', $loanReturn);

		//Provide summary of transaction
		// $qry = "SELECT MAX(ReturnID) as 'TransID' FROM `loanreturn`";
		// $qryResult = $this->db->query($qry);
		// $resultArray = $qryResult->result();
		// $tblSummary = $this->GetTransaction($resultArray[0]->TransID, FALSE);
		$tblSummary = $this->GetTransaction($transData['TransID'], 'loanreturn', FALSE);
		// echo "Here is the transID val for the query...:<br/>";
		// echo $transData['TransID'];
		// echo "<br/>";
		//echo "Here is what is in the tblsummary var:<br/>[<br/>$tblSummary<br/>]";

		$transData['tblSummary'] = $tblSummary;

		//Return $transData array
		return $transData;

	}

	public function GetTransaction($aTransID, $transType, $resultAsArray)
	{
			switch ($transType)
			{
				case 'invoice':

					$qryTransItem = "
					SELECT T.TransDate as 'Transaction Date', Pa.SaleNDC10 as 'Bulk Carton/Package NDC', Pa.UseNDC10 as 'Individual Vial/Dose NDC',
					Pr.ProprietaryName as 'Proprietary Name', Pr.NonProprietaryName as 'Non-Proprietary Name', 
					Pr.LabelerName as 'Labeler Name', Pa.PackageDescrip as 'Description', Vt.ExpireDate as 'Expiration Date (Y/M/D)', Vt.LotNum as 'Lot Number', Oi.Clinic_Per_Dose_Cost as 'Per Dose Cost', 
					Oi.PackageQty as 'Package Qty', Oi.Doses_Per_Package as 'Doses Per Package'
					FROM `fda_product` Pr inner join `fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
					`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
					`transaction` T on Vt.TransId = T.TransID inner join
					`order_invoice` Oi on Vt.TransID = Oi.InvoiceID
					WHERE Vt.TransID = $aTransID"; //Provides info on the most recently inserted transaction
					break;

				case 'administer':

					$qryTransItem = "
					SELECT T.TransDate as 'Transaction Date', Pa.SaleNDC10 as 'Bulk Carton/Package NDC', Pa.UseNDC10 as 'Individual Vial/Dose NDC',
					Pr.ProprietaryName as 'Proprietary Name', Pr.NonProprietaryName as 'Non-Proprietary Name', 
					Pr.LabelerName as 'Labeler Name', Pa.PackageDescrip as 'Description', 
					Vt.ExpireDate as 'Expiration Date (Y/M/D)', Vt.LotNum as 'Lot Number', 
					A.Cust_Per_Dose_Chrg as 'Customer Charge Per Dose', A.Doses_Given as 'Number of Doses Given'	
					FROM `fda_product` Pr inner join `fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
					`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
					`transaction` T on Vt.TransId = T.TransID inner join
					`administer` A on Vt.TransID = A.AdministerID
					WHERE Vt.TransID = $aTransID"; //Provides info on the most recently inserted transaction
					break;

				case 'loanout':

					$qryTransItem = "
					SELECT T.TransDate as 'Transaction Date', Pa.SaleNDC10 as 'Bulk Carton/Package NDC', Pa.UseNDC10 as 'Individual Vial/Dose NDC',
					B.EntityName as 'Borrower Name', Pr.ProprietaryName as 'Proprietary Name', Pr.NonProprietaryName as 'Non-Proprietary Name', 
					Pr.LabelerName as 'Labeler Name', Pa.PackageDescrip as 'Description', 
					Vt.ExpireDate as 'Expiration Date (Y/M/D)', Vt.LotNum as 'Lot Number', 
					LO.PackageQty as 'Number of Packages Loaned', LO.Doses_Per_Package as 'Doses Per Package'
					FROM `fda_product` Pr inner join `fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
					`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
					`transaction` T on Vt.TransId = T.TransID inner join
					`loanout` LO on Vt.TransID = LO.LoanID inner join
					`borrower` B on LO.BorrowerID = B.BorrowerID
					WHERE Vt.TransID = $aTransID"; //Provides info on the most recently inserted transaction
					break;

				case 'loanreturn':

					$qryTransItem = "
					SELECT T.TransDate as 'Transaction Date', Pa.SaleNDC10 as 'Bulk Carton/Package NDC', Pa.UseNDC10 as 'Individual Vial/Dose NDC',
					B.EntityName as 'Borrower Name', Pr.ProprietaryName as 'Proprietary Name', Pr.NonProprietaryName as 'Non-Proprietary Name', 
					Pr.LabelerName as 'Labeler Name', Pa.PackageDescrip as 'Description', 
					Vt.ExpireDate as 'Expiration Date (Y/M/D)', Vt.LotNum as 'Lot Number', 
					LR.PackageQty as 'Number of Packages Loaned', LR.Doses_Per_Package as 'Doses Per Package'
					FROM `fda_product` Pr inner join `fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
					`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
					`transaction` T on Vt.TransId = T.TransID inner join
					`loanreturn` LR on Vt.TransID = LR.ReturnID inner join
					`borrower` B on LR.BorrowerID = B.BorrowerID
					WHERE Vt.TransID = $aTransID"; //Provides info on the most recently inserted transaction
					break;

				default:
					break;
			}

			
			// echo "<br/>Here's the qry...<br/>[<br/>";
			// echo "$qryTransItem";
			// echo "<br/>]<br/>";

			$qryResult = $this->db->query($qryTransItem);

			if($resultAsArray)
			{
				$result = $qryResult->result();
			}
			else
			{
				$result = $qryResult; //returns a query as an "object" rather than an as an "array of objects"
			}

			return $result;
	}

	public function GetSingleVacSum($aDrugID)
	{
			$qryVacQty = "
			SELECT Pr.ProprietaryName as 'Proprietary Name', Pr.NonProprietaryName as 'Non-Proprietary Name', 
			Pr.LabelerName as 'Labeler Name', Pa.PackageDescrip as 'Description', SUM(Oi.Clinic_Per_Dose_Cost * Oi.PackageQty * Oi.Doses_Per_Package)/SUM(Oi.PackageQty * Oi.Doses_Per_Package) As 'Weighted Average Cost', SUM(Oi.PackageQty) As 'Total Packages', SUM(Oi.PackageQty * Oi.Doses_Per_Package) As 'Total Doses'
			FROM `fda_product` Pr inner join `fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
			`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
			`transaction` T on Vt.TransId = T.TransID inner join
			`order_invoice` Oi on Vt.TransID = Oi.InvoiceID
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