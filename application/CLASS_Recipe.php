<?php

class Recipe {

	/** @var bool */
	public $Valid = false;

	/** @var int */
	public $ID;
	/** @var string */
	public $Title;

	/** @var string */
	public $Type = 'Private';
	/** @var string|bool */
	public $Description = false;
	/** @var float */
	public $CreateStamp;
	/** @var float */
	public $ModifyStamp;

	/** @var Session */
	public $Session;

	/** @var MySQLDatabase */
	public $DB;
	
	public function __construct($session, $data) {
		$this->Session = $session;
		$this->DB = $session->DB;
		
	}
	
}