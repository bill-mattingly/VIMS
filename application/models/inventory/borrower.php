<?php

class Borrower extends CI_Model
{
	//Attributes
	private $borrowerID;
	private $entityName;

	//Constructor
	public function __construct()
	{
		$this->load->database();
	}

	//Custom methods
	public function DisplayBorrowers()
	{
		//Query to retrieve all data from the borrower table
		$qry = "SELECT BorrowerID, EntityName FROM `borrower`";

		$result = $this->db->query($qry);
		$resultArray = $result->result();

		//Return query array
		return $resultArray;
	}


}

?>

