<?php

class Vaccine extends CI_Model
{
	//Class Variables
	private $drugID; //type int //stores a vaccine's DrugID (from the fda_drug_package table)
	private $saleNDC; //type string //stores the NDC value on a vaccine's containing box/package
	private $unitNDC; //type string //stores the NDC value on a single dose of vaccine
	private $expireDate; //type date (YYYY/MM/DD format) //stores vaccine expiration date 
	private $lotNum; //type string //stores vaccine lot number
	private $labelerName; //type string //stores manufacturer's name
	private $vacFormalName; //type string //stores vaccine's formal (proprietary) name
	private $vacCommonName; //type string //stores vaccine's common name

	//Constructor
	public function __construct(){
		$this->load->database();
		$this->load->library('Ion_auth'); //Loads Ion_auth library which provides user account management
	} //End __construct()


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
		$barcodeArray = array('ndc10' => null,
							  'ndc11' => null,
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
			$theNDCArray = $this->ParseNDC($barcode, $isASaleNDC); //$barcode var has now been stripped to the 10 digit ndc code. Call ParseNDC method on $barcode and store in $theNDC variable.

			$barcodeArray["ndc10"] = $theNDCArray['ndc10']; //Update the barcodeArray variable to include the parsed ndc10 value
			$barcodeArray["ndc11"] = $theNDCArray['ndc11']; //HIPAA NDC 11 value
			return $barcodeArray; //Return the array to the calling method
		}

		//Handles 1D (Linear) Barcode with 13 digits (example: Japanese Encephalitis Vaccine by Novartis (barcode val: 4251500101001))
		else if(strLen($barcode) == 13){
			$barcode = substr($barcode, 0, -3);

			$theNDCArray = $this->ParseNDC($barcode, $isASaleNDC); //$barcode var has now been stripped to the 10 digit ndc code. Call ParseNDC method on $barcode and store in $theNDC variable.

			$barcodeArray["ndc10"] = $theNDCArray['ndc10']; //Update the barcodeArray variable to include the parsed ndc value
			$barcodeArray['ndc11'] = $theNDCArray['ndc11']; //HIPAA NDC 11 value
			return $barcodeArray; //Return the array to the calling method
		}

		//Handles 1D (Linear) Barcode with 16 digits (typically the 1D code on a vaccine vial. Example: Varicella Virus by Merck (1D barcode val: 0100300064827019))
		else if(strLen($barcode) == 16)
		{
			$barcode = substr($barcode, 5); //Strip out the first 5 digits ("01003")

			$theNDCArray = $this->ParseNDC($barcode, $isASaleNDC);

			$barcodeArray["ndc10"] = $theNDCArray['ndc10'];
			$barcodeArray['ndc11'] = $theNDCArray['ndc11'];
			return $barcodeArray;
		}

		//Handles 2D (QR) Barcodes
		else if(strLen($barcode) >= 31)
		{
			//Parse barcode for ndc
			$theNDC = substr($barcode, 5, 10); //segment of barcode string with the NDC
			$theNDCArray = $this->ParseNDC($theNDC, $isASaleNDC);

			//Parse barcode for expiration date
			$expireDate = substr($barcode, 18, 6);//segment of barcode string with the expiration date

			//Format expireDate for input into database (mm-dd-yyyy format)
			$expireDate = substr($expireDate, 2, 2)."-".substr($expireDate, 4, 2)."-20".substr($expireDate, 0, 2);

			//Parse barcode for lot number
			$lotNum = substr($barcode, 26);//segment of barcode string with the lot number
	
			//Store values in barcode array
			$barcodeArray["ndc10"] = $theNDCArray['ndc10'];
			$barcodeArray['ndc11'] = $theNDCArray['ndc11'];
			$barcodeArray["expireDate"] = $expireDate;
			$barcodeArray["lotNum"] = $lotNum;
			return $barcodeArray;

		}

		//Handles all barcode strings that fall outside the previous rules
		//Returns an error to the user
		else
		{
			$errorStr = "The Barcode was an Unrecognized Length";
			return $errorStr;
		}

	} //End ParseBarcode()

	//Accepts an NDC Code and parses out the 10 digit NDC code
	//Returns the parsed code (i.e. in 5-3-2, 5-4-1, or 4-4-2 format)
	public function ParseNDC($anNDC_Val, $isASaleNDC)
	{
		//Declare and initialize method variables
		$ndcArray = array(
						  'ndc10' => null,
						  'ndc11' => null
						 );

		$theNDC10 = $anNDC_Val; //Stores the full 10 digit NDC code (not parsed into 3 segements)
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

		$prod532 = $labelNum5."-".$prodNum53."-".$packageNum2;
		$prod541 = $labelNum5."-".$prodNum54."-".$packageNum1;
		$prod442 = $labelNum4."-".$prodNum44."-".$packageNum2;


		$vaccineExists = $this->FindNDCFormat($prod532, $isASaleNDC);

		if($vaccineExists)
		{
			$ndcArray['ndc10'] = $prod532;
			$ndcArray['ndc11'] = "$labelNum5-0$prodNum53-$packageNum2";
 		}
		else
		{
			$vaccineExists = $this->FindNDCFormat($prod541, $isASaleNDC);

			if($vaccineExists)
			{
				$ndcArray['ndc10'] = $prod541;
				$ndcArray['ndc11'] = "$labelNum5-$prodNum54-0$packageNum1";
			}
			else
			{
				$ndcArray['ndc10'] = $prod442;
				$ndcArray['ndc11'] = "0$labelNum4-$prodNum44-$packageNum2";
			}
		}

		return $ndcArray;

	} //End ParseNDC()


	//Searches the database for an NDC code or partial code & returns a bool value indicating whether or not a vaccine was found
	public function FindNDCFormat($anNDC, $isASaleNDC)//$aProdNDC_Code){
	{
		//Declare & initialize method variables
		$qry; //Stores a database query string
		$result; //Stores a database query result set
		$vaccineFound; //Type bool. Stores True if qry returns a result set. Stores False if qry returns an empty set.

		if($isASaleNDC == TRUE)
		{
			$qry = 
				"SELECT DrugID 
				FROM `fda_drug_package` 
				WHERE SaleNDC10 = '$anNDC'";
		}
		else
		{
			$qry = 
				"SELECT DrugID 
				FROM `fda_drug_package`
				WHERE USENDC10 = '$anNDC'";
		}

		$result = $this->db->query($qry);

		if($result->num_rows() > 0) 
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
		if($vacAdministered) //Means the vaccine vial was scanned
		{
			$qry = 
				"SELECT 
					Package.DrugID as 'Drug ID',
					Package.SaleNDC10 as 'Carton NDC10',
					Package.UseNDC10 as 'Dose NDC10', 
					Package.PackageDescrip as 'Package Description', 
					Product.ProprietaryName as 'Proprietary Name', 
					Product.NonProprietaryName as 'Non-Proprietary Name', 
					Product.LabelerName as 'Labeler Name', 
					Package.Drug_Cost as 'Clinic Cost', 
					Package.Trvl_Chrg as 'Travel Patient Chrg', 
					Package.Refugee_Chrg as 'Refugee Patient Chrg',
					Package.NumDosesPackage as 'Number Doses Package'
				FROM 
					`fda_drug_package` Package inner join
					`fda_product` Product on Package.ProductID = Product.ProductID 
				WHERE 
					Package.UseNDC10 = '$anNDC10_Code'";
		}
		else //Means the vaccine box was scanned
		{
			$qry = 
				"SELECT 
					Package.DrugID as 'Drug ID', 
					Package.SaleNDC10 as 'Carton NDC10',
					Package.UseNDC10 as 'Dose NDC10', 
					Package.PackageDescrip as 'Package Description', 
					Product.ProprietaryName as 'Proprietary Name', 
					Product.NonProprietaryName as 'Non-Proprietary Name', 
					Product.LabelerName as 'Labeler Name',
					Package.Drug_Cost as 'Clinic Cost',
					Package.Trvl_Chrg as 'Travel Patient Chrg',
					Package.Refugee_Chrg as 'Refugee Patient Chrg',
					Package.NumDosesPackage as 'Number Doses Package'
				FROM 
					`fda_drug_package` Package inner join
					`fda_product` Product on Package.ProductID = Product.ProductID 
				WHERE 
					Package.SaleNDC10 = '$anNDC10_Code'";
		}
		
		//Store query result set
		$qryResult = $this->db->query($qry);
		$qryArray = $qryResult->result();


		return $qryArray; //Recall that the value stored in $qryResult is an array of objects. In this case, query objects (thus they have all the fields from the query)

	} //End GetVaccine()

	public function GetPackageDrugID($aVialDrugID)
	{
		//Select the smallest DrugID value (this is the carton DrugID) for the SaleNDC number that corresponds to the vial DrugID
		$qry =
			"SELECT MIN(DrugID) as PackageDrugID
			 FROM fda_drug_package
			 WHERE SALENDC10 IN
			 	(SELECT SaleNDC10
			 	 FROM fda_drug_package
			 	 WHERE DrugID = $aVialDrugID)";

		$result = $this->db->query($qry);
		$resultArray = $result->result();

		$resultArray = $resultArray[0]->PackageDrugID;

		return $resultArray;

	} //End GetPackageDrugID()


	//Occurs when an order is placed for a vaccine. Increases the quantity of an existing vaccine.
	public function OrderInvoice($aDrugID)
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

			$this->db->insert('order_invoice', $invoiceTrans);

			//Transaction report
			$tblSummary = $this->GetTransaction($transData['TransID'], 'invoice', FALSE);
			$transData['tblSummary'] = $tblSummary;

			//Return $transData array to controller for display by the view
			return $transData;

	} //End OrderInvoice()

	//Occurs when a vaccine is administered to a patient. Decreases the quantity of an existing vaccine.
	public function Administer($aDrugID)
	{
		//Add data to Transaction and VaccineTrans tables
		$transData = $this->TransVacTransInsert($aDrugID);

		//Obtain DrugID for the Package that the individual vial came in
		//Occassionally, the same drug is listed with 2 different drugids (for example: DrugID 746 & 747).
		//In these cases, it appears that the lowest drugid refers to the package & higher drugids (in the query result set) refer to individual doses
		//So in the example case, drugid 746 refers to the carton while 747 refers to an individual dose

		//This is important to know when substracting out the records in the Administer table from the Invoice table to
		//determine Net Inventory
		$qry =
			"SELECT MIN(DrugID) as PackageDrugID 
			FROM `fda_drug_package` 
			WHERE SaleNDC10 IN (
				SELECT SaleNDC10
				FROM `fda_drug_package` 
				WHERE DrugID = $aDrugID)";

		$result = $this->db->query($qry);
		$resultArray = $result->result();

		$packageDrugID = $resultArray[0]->PackageDrugID;


		//Add to Administer table
		$administerTrans = array(
			"AdministerID" => $transData['TransID'],
			"Package_DrugID" => $packageDrugID,
			"Cust_Per_Dose_Chrg" => $this->session->customerChrg,
			"Doses_Given" => $this->session->doseQty
			);

		$this->db->insert('administer', $administerTrans);

		//Transaction report
		$tblSummary = $this->GetTransaction($transData['TransID'], 'administer', FALSE);
		$transData['tblSummary'] = $tblSummary;

		//Update and return $transData array to controller for display by the view
		return $transData;
	} //End Administer()

	//Occurs when a medical group asks the clinic if it can borrow vaccine from the clinic's inventory. Decreases clinic inventory.
	public function LoanOut($aDrugID, $aBorrowerID, $theLoanSigner)
	{
		//Enter transaction into database
		//Add data to Transaction & VaccineTrans tables
		$transData = $this->TransVacTransInsert($aDrugID);

		//Add data to LoanOut table
		$loanData = array(
			"LoanID" => $transData['TransID'],
			"BorrowerID" => $aBorrowerID,
			"Signer_Name" => $theLoanSigner,
			"Total_Doses" => $this->session->DosesPerPackage
		);

		$this->db->insert('loanout', $loanData);

		//Transaction report
		$tblSummary = $this->GetTransaction($transData['TransID'], 'loanout', FALSE);
		$transData['tblSummary'] = $tblSummary;

		//Return transaction data array
		return $transData;
	} //End LoanOut()

	public function GetTransaction($aTransID, $transType, $resultAsArray)
	{
		switch ($transType)
		{
			case 'invoice':

				$qryTransItem =
				"SELECT 
					T.TransDate as 'Transaction Date', 
					Pa.SaleNDC10 as 'Bulk Carton/Package NDC', 
					Pa.UseNDC10 as 'Individual Vial/Dose NDC',
					Pr.ProprietaryName as 'Proprietary Name', 
					Pr.NonProprietaryName as 'Non-Proprietary Name', 
					Pr.LabelerName as 'Labeler Name', 
					Pa.PackageDescrip as 'Description', 
					Vt.ExpireDate as 'Expiration Date (yyyy/mm/dd)', 
					Vt.LotNum as 'Lot Number', 
					Oi.Clinic_Per_Dose_Cost as 'Per Dose Cost', 
					Oi.PackageQty as 'Package Qty', 
					Oi.Doses_Per_Package as 'Doses Per Package'
				FROM 
					`fda_product` Pr inner join 
					`fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
					`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
					`generic_transaction` T on Vt.TransId = T.TransID inner join
					`order_invoice` Oi on Vt.TransID = Oi.InvoiceID
				WHERE 
					Vt.TransID = $aTransID"; //Provides info on the most recently inserted transaction
				
				break;

			case 'administer':

				$qryTransItem =
				"SELECT 
					T.TransDate as 'Transaction Date', 
					Pa.SaleNDC10 as 'Bulk Carton/Package NDC', 
					Pa.UseNDC10 as 'Individual Vial/Dose NDC',
					Pr.ProprietaryName as 'Proprietary Name', 
					Pr.NonProprietaryName as 'Non-Proprietary Name', 
					Pr.LabelerName as 'Labeler Name', 
					Pa.PackageDescrip as 'Description', 
					Vt.ExpireDate as 'Expiration Date (yyyy/mm/dd)', 
					Vt.LotNum as 'Lot Number', 
					A.Cust_Per_Dose_Chrg as 'Customer Charge Per Dose', 
					A.Doses_Given as 'Number of Doses Given'	
				FROM 
					`fda_product` Pr inner join 
					`fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
					`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
					`generic_transaction` T on Vt.TransId = T.TransID inner join
					`administer` A on Vt.TransID = A.AdministerID
				WHERE 
					Vt.TransID = $aTransID"; //Provides info on the most recently inserted transaction
				
				break;

			case 'loanout':

				$qryTransItem = 
				"SELECT 
					T.TransDate as 'Transaction Date', 
					Pa.SaleNDC10 as 'Bulk Carton/Package NDC', 
					Pa.UseNDC10 as 'Individual Vial/Dose NDC',
					B.EntityName as 'Borrower Name',
					Lo.Signer_Name as 'Loan Signer', 
					Pr.ProprietaryName as 'Proprietary Name', 
					Pr.NonProprietaryName as 'Non-Proprietary Name', 
					Pr.LabelerName as 'Labeler Name', 
					Pa.PackageDescrip as 'Description', 
					Vt.ExpireDate as 'Expiration Date (yyyy/mm/dd)', 
					Vt.LotNum as 'Lot Number', 
					LO.Total_Doses as 'Total Doses'
				FROM 
					`fda_product` Pr inner join 
					`fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
					`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
					`generic_transaction` T on Vt.TransId = T.TransID inner join
					`loanout` LO on Vt.TransID = LO.LoanID inner join
					`borrower` B on LO.BorrowerID = B.BorrowerID
				WHERE 
					Vt.TransID = $aTransID"; //Provides info on the most recently inserted transaction
				
				break;

			case 'loanreturn':

				$qryTransItem = 
				"SELECT 
					T.TransDate as 'Transaction Date',
					Pa.SaleNDC10 as 'Bulk Carton/Package NDC',
					Pa.UseNDC10 as 'Individual Vial/Dose NDC',
					B.EntityName as 'Borrower Name', 
					Pr.ProprietaryName as 'Proprietary Name', 
					Pr.NonProprietaryName as 'Non-Proprietary Name', 
					Pr.LabelerName as 'Labeler Name', 
					Pa.PackageDescrip as 'Description', 
					Vt.ExpireDate as 'Expiration Date (yyyy/mm/dd)', 
					Vt.LotNum as 'Lot Number', 
					LR.Total_Doses as 'Total Doses'
				FROM 
					`fda_product` Pr inner join 
					`fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
					`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
					`generic_transaction` T on Vt.TransId = T.TransID inner join
					`loanreturn` LR on Vt.TransID = LR.ReturnID inner join
					`borrower` B on LR.BorrowerID = B.BorrowerID
				WHERE 
					Vt.TransID = $aTransID"; //Provides info on the most recently inserted transaction
				
				break;

			default:
				break;
		}


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
	} //End GetTransaction()

	//Provides all information on a single vaccine
	public function GetSingleVacInventory($aDrugID)
	{
		$sql = 
		"SELECT  
			pr.proprietaryname as 'Proprietary Name',
			pr.nonproprietaryname as 'Non-Proprietary Name', 
			pr.labelername as 'Labeler Name', 
			pa.fulldescrip as 'Description',
			pa.salendc10 as 'Carton NDC10',
			pa.usendc10 'Dose NDC10',  
			pa.drug_cost as 'Clinic Cost', 
			pa.trvl_chrg as 'Travel Patient Chrg', 
			pa.refugee_chrg as 'Refugee Patient Chrg',
			net.drugid as 'Drug ID', 
			net.lotnum as 'Lot Number', 
			net.expiredate as 'Expire Date', 
			sum(net.vacdoses) as 'Net Doses'

		FROM
			(
				/*
					Using 'transid' column in each of the unioned result sets b/c it provides a unique identifier for each column
					A unique id is needed for each column to prevent columns from being removed from the result set.
					Non-unique columns will be removed from result sets b/c UNION, by default, removes duplicate rows.
					UNION ALL includes duplicate rows. Added 'transid' column as an extra precaution.
				*/
				/*Invoice transactions*/
					(SELECT
					 	vt.transid as transid, vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, sum(oi.doses_per_package * oi.packageqty) as vacdoses 
					 FROM 
					 	vaccinetrans vt inner join order_invoice oi on vt.transid = oi.invoiceid 
					 GROUP BY 
					 	vt.drugid, vt.lotnum, vt.expiredate)

				UNION ALL /*Need keyword ALL to prevent 'duplicate rows' from being removed; duplicate row removal is the default action of UNION command) */
				/*Administer transactions*/ 
					(SELECT 
						vt.transid as transid, a.package_drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, SUM( a.doses_given)*-1 AS vacdoses /* Multiplied by '-1' to show a reduction in inventory*/
					FROM 
						vaccinetrans vt INNER JOIN administer a ON vt.transid = a.administerid
					GROUP BY 
						vt.drugid, vt.lotnum, vt.expiredate)

				UNION ALL
				/*LoanOut transactions*/
					(SELECT 
						vt.transid as transid, vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, SUM(lo.total_doses)*-1 AS vacdoses /* Multiplied by '-1' to show a reduction in inventory*/
					FROM
						vaccinetrans vt INNER JOIN loanout lo ON vt.transid = lo.loanid /*lo.borrowerid*/
					GROUP BY
						vt.drugid, vt.lotnum, vt.expiredate)

				UNION ALL
				/*LoanReturn transactions*/
					(SELECT
						vt.transid as transid, vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, SUM(lr.total_doses) as vacdoses
					FROM
						vaccinetrans vt INNER JOIN loanreturn lr on vt.transid = lr.returnid
					GROUP BY
						vt.drugid, vt.lotnum, vt.expiredate
					)

			) net /*Every table has to have it's own alias according to MySQL spec*/

			INNER JOIN

			fda_drug_package pa on net.drugid = pa.drugid INNER JOIN
			fda_product pr on pa.productid = pr.productid

		WHERE 
			net.drugid = '$aDrugID'
		GROUP BY 
			net.drugid, net.lotnum, net.expiredate";

		//End SQL query


		$result = $this->db->query($sql);

		$resultArray = $result->result();

		//Check the $resultArray for Lot Numbers with zero inventory & remove these from the array
		$modifiedResult = null; //Stores the $resultArray modified to not include lot numbers with 0 inventory
		$counter = 0; //Loop control variable used to specify the new index values of the modified array

		foreach($resultArray as $lotNumber)
		{
			if($lotNumber->{'Net Doses'} != 0)
			{
				$modifiedResult[$counter] = $lotNumber;
				$counter++;
			}			
		} //End foreach()

		return $modifiedResult;
	} //End GetSingleVacInventory()


	//Provides all information on a group of vaccines
	public function GetMultiVacInventory($aDrugIDArray)
	{
		$arrayCount = count($aDrugIDArray);

		if ($arrayCount > 1)
		{
			asort($aDrugIDArray); //asort() sorts the array & then returns a boolean value
		}

		//Min DrugID
		$minDrugID = $aDrugIDArray[0]; //First DrugID in the array

		//Max DrugID
		$maxDrugID = $aDrugIDArray[($arrayCount - 1)];


/************/
/************/


		//Inventory query
		$sql = 
		"SELECT  
			pr.proprietaryname as 'Proprietary Name',
			pr.nonproprietaryname as 'Non-Proprietary Name', 
			pr.labelername as 'Labeler Name', 
			pa.packagedescrip as 'Package Description',
			pa.salendc10 as 'Carton NDC10',
			pa.usendc10 'Dose NDC10',  
			pa.drug_cost as 'Clinic Cost', 
			pa.trvl_chrg as 'Travel Patient Chrg', 
			pa.refugee_chrg as 'Refugee Patient Chrg',
			pa.numdosespackage as 'Number Doses Package', 
			net.drugid as 'Drug ID', 
			net.lotnum as 'Lot Number', 
			net.expiredate as 'Expire Date', 
			sum(net.vacdoses) as 'Net Doses'
		FROM
			(
				/*Invoice transactions*/
					(SELECT
					 	vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, sum(oi.doses_per_package * oi.packageqty) as vacdoses 
					 FROM 
					 	vaccinetrans vt inner join order_invoice oi on vt.transid = oi.invoiceid 
					 GROUP BY 
					 	vt.drugid, vt.lotnum, vt.expiredate)

				UNION
				/*Administer transactions*/ 
					(SELECT 
						a.package_drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, SUM( a.doses_given)*-1 AS vacdoses /* Multiplied by '-1' to show a reduction in inventory*/
					FROM 
						vaccinetrans vt INNER JOIN administer a ON vt.transid = a.administerid
					GROUP BY 
						vt.drugid, vt.lotnum, vt.expiredate)

				UNION
				/*LoanOut transactions*/
					(SELECT 
						vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, SUM(lo.total_doses)*-1 AS vacdoses /* Multiplied by '-1' to show a reduction in inventory*/
					FROM
						vaccinetrans vt INNER JOIN loanout lo ON vt.transid = lo.borrowerid
					GROUP BY
						vt.drugid, vt.lotnum, vt.expiredate)

				UNION
				/*LoanReturn transactions*/
					(SELECT
						vt.drugid as drugid, vt.lotnum as lotnum, vt.expiredate as expiredate, sum(lr.total_doses) as vacdoses
					FROM
						vaccinetrans vt INNER JOIN loanreturn lr on vt.transid = lr.returnid
					GROUP BY
						vt.drugid, vt.lotnum, vt.expiredate
					)

			) net /*Every table has to have it's own alias according to MySQL spec*/

			INNER JOIN

			fda_drug_package pa on net.drugid = pa.drugid INNER JOIN
			fda_product pr on pa.productid = pr.productid

		WHERE 
			net.drugid BETWEEN '".$minDrugID."' AND '".$maxDrugID."'
		GROUP BY 
			net.drugid";

/************/
/************/

		//End SQL query

		$result = $this->db->query($sql);

		$resultArray = $result->result();

		//Check query result for rows with net inventory == 0 (& remove those rows from the result returned to the calling method)
		$modifiedResult = null; //array of query result objects which stores the result rows which have inventory > 0
		$counter = 0;

		foreach($resultArray as $vaccine)
		{
			if($vaccine->{'Net Doses'} != 0)
			{
				$modifiedResult[$counter] = $vaccine;
				$counter++;
			}

		}

		return $modifiedResult;
	
	} //End GetMultiVacInventory()


	//Provides a summary of the weighted average cost & number of doses of a single vaccine
	public function GetSingleVacSum($aDrugID)
	{
			$qryVacQty = 
			"SELECT 
				Pr.ProprietaryName as 'Proprietary Name',
				Pr.NonProprietaryName as 'Non-Proprietary Name',
				Pr.LabelerName as 'Labeler Name',
				Pa.PackageDescrip as 'Description',
				SUM(Oi.Clinic_Per_Dose_Cost * Oi.PackageQty * Oi.Doses_Per_Package)/SUM(Oi.PackageQty * Oi.Doses_Per_Package) As 'Weighted Average Cost',
				SUM(Oi.PackageQty) As 'Total Packages', SUM(Oi.PackageQty * Oi.Doses_Per_Package) As 'Total Doses'
			FROM 
				`fda_product` Pr inner join 
				`fda_drug_package` Pa on Pr.ProductID = Pa.ProductID inner join 
				`vaccinetrans` Vt on Pa.DrugId = Vt.DrugId inner join
				`generic_transaction` T on Vt.TransId = T.TransID inner join
				`order_invoice` Oi on Vt.TransID = Oi.InvoiceID
			WHERE 
				Vt.DrugID = $aDrugID
			GROUP BY 
				Vt.DrugID"; //Gives a current weighted average cost per dose & total dose quantity for a vaccine

			$qryResult = $this->db->query($qryVacQty);
			$qryArray = $qryResult->result();

			return $qryArray;
	} //End GetSingleVacSum()

	public function TransVacTransInsert($aDrugID)
	{
			//Insert data into Transaction table
			date_default_timezone_set('UTC');
			$transTimestamp = date('Y-m-d H:i:s'); //time();
			//date_default_timezone_set('America/New_York');
			
			$userID = $this->ion_auth->get_user_id();

			$transData = array(
				'TransDate' => $transTimestamp,
				'Employee_ID' => $userID
			);

			$this->db->insert('generic_transaction', $transData);


			//Insert into VaccineTrans table
			$qry = "SELECT MAX(TransID) as TransID FROM `generic_transaction`";
			$qryResult = $this->db->query($qry);

			$row = $qryResult->result();

			$transID = $row[0]->TransID; //$row is an array of objects. Thus, "$row[0]->TransID" references the array element in index position 0 & fetches the "TransID" property of the object stored in the first index of the array
			$transData['TransID'] = $transID;
			$transData['DrugID'] = $aDrugID;

			$vacTrans = array(
				"TransID" => $transID,
				"DrugID" => $aDrugID,
				"ExpireDate" => $this->session->expireDate,
				"LotNum" => $this->session->lotNum
				);

			$this->db->insert('vaccinetrans', $vacTrans);

			return $transData;
	} //End TransVacTransInsert()

} //End class Vaccine


?> 