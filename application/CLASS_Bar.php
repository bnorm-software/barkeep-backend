<?php

class Bar {
	/** @var bool */
	public $Valid = false;

	/** @var int */
	public $ID;
	/** @var string */
	public $Title;

	/** @var string */
	public $Type = 'Private';
	/** @var string */
	public $Path;
	/** @var string */
	public $Description = false;
	/** @var float */
	public $CreateStamp;
	/** @var float */
	public $ModifyStamp;

	/** @var Session */
	public $Session;
	/** @var MySQLDatabase */
	public $DB;

	/**
	 * Bar constructor.
	 * @param Session $session
	 * @param string[] $barData
	 */
	public function __construct($session, $barData) {
		$this->Session = $session;
		$this->DB = $session->DB;
	}

	/** @return string[] */
	public static function ValidArray() {
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

	/** @return string[] */
	public static function NewArray() {
		return array(
			'userID' => false
			, 'type' => false
			, 'title' => false
			, 'description' => false
		);
	}
}