<?php

class Refugee extends CI_Model
{
	//Variable declaration
	private $refugeeID;
	private $firstName;
	private $lastName;
	private $gender;
	private $languageID;
	private $countryID;
	//Any other data to collect on a refugee?

	//Constructor
	public function __construct($aFirstName = "", $aLastName = "", $aGender = "", $aLanguageID = -1, $aCountryID = -1)
	{
		//Connect to database (to query refugee id values)
		$this->load->database();

		//Initialize variables
		$this->refugeeID = //call the
		$this->firstName = $aFirstName;
		$this->lastName = $aLastName;
		$this->gender = $aGender;
		$this->languageID = $aLanguageID;
		$this->countryID = $aCountryID;

	} //End constructor

	//Properties


	//Custom Methods

	//Add Refugee
	public function AddRefugee()
	{

	} //End AddRefugee

	//Remove Refugee
	public function RemoveRefugee()
	{

	} //End RemoveRefugee

	//Update Information
	public function UpdateRefugee()
	{

	} //End UpdateRefugee


} //End Refugee class
?>