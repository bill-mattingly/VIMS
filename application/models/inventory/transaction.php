<?php

abstract class GenTransaction extends CI_Model{
	//Class variables (variable names mirror database table field names in the Transaction, Order_Invoice, Administer, Loan, and LoanReturn tables - all the tables possibly involved in a transaction)
	//Vars for Transaction table
	private salendc10;
	private transdate;
	private expiredate;
	private lotnum;


	//Constructor
	abstract public function __construct(){

	}

	//Custom Methods

	//AddTransaction method (generic in this class & implemented in child classes)
	abstract public function AddTransaction();

}

?>