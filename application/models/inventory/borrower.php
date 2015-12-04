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

	public function CreateBorrower($aBorrowerName)
	{
		$sql = "INSERT INTO BORRROWER (ENTITYNAME)
				VALUES $aBorrowerName";

		$this->db->trans_begin(); //Run query to insert new borrower as a transaction that can be rolled back if it fails

		$this->db->query($sql);

		if($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
		}
		else
		{
			$this->db->trans_commit();
		}
	}


}

?>

