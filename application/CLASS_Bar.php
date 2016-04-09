<?php

class Bar {
	public $Valid = false;

	public $ID;
	public $Title;

	public $Type = 'Private';
	public $Path;
	public $Description = false;
	public $CreateStamp;
	public $ModifyStamp;

	public $Session;
	public $DB;

	public function __construct($session, $barData) {
		$this->Session = $session;
		$this->DB = $session->DB;
	}

}

function ValidBarArray() {
	return array(
		'id' => false
		, 'userID' => false
		, 'type' => false
		, 'title' => false
		, 'path' => false
		, 'description' => false
		, 'createStamp' => false
		, 'modifyStamp' => false
		, 'active' => false
	);
}

function NewBarArray() {
	return array(
		'userID' => false
		, 'type' => false
		, 'title' => false
		, 'description' => false
	);
}