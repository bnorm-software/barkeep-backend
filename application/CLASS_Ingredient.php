<?php

class Ingredient {

	/** @var int */
	public $ID;

	/** @var Ingredient */
	public $BaseIngredient = false;

	/** @var string */
	public $Type = 'Private';

	/** @var string */
	public $Title;
	/** @var string */
	public $Description;
	/** @var float */
	public $CreateStamp;
	/** @var float */
	public $ModifyStamp;

	/** @var Session */
	public $Session;

	/** @var MySQLDatabase */
	public $DB;

	/** @var bool */
	public $Valid = false;

	public function __construct($session, $ingredientData) {
		$this->Session = $session;
		$this->DB = $session->DB;
		if(isset($ingredientData['id'])) {
			$this->SetData($ingredientData);
			$this->Valid = true;
		}
		else { //Creating a new ingredient
			$this->SetData($ingredientData);
			$this->UpdateDatabase();

		}
	}
	
	public function Process($server, $path, $headers) {
		$method = (isset($server['REQUEST_METHOD'])) ? $server['REQUEST_METHOD'] : false;
		if (empty($path)) {
			switch ($method) {
				case 'POST':
					break;

				case 'GET':
					APIResponse(RESPONSE_200, $this->ToArray());
					break;

				case 'PUT':
					unset($_POST['id']); //Don't update id.  That's dumb.
					$this->SetData($_POST);
					$this->UpdateDatabase();
					APIResponse(RESPONSE_200, $this->ToArray());
					break;

				case 'DELETE':
					APIResponse(RESPONSE_500, "TODO");
					break;

				default:

					break;
			}
		}
	}

	/** @param string[] $ingredientData */
	public function SetData($ingredientData) {
		if (isset($ingredientData['baseIngredientID'])) {
			$baseIngredient = $this->Session->IngredientByID($ingredientData['baseIngredientID']);
			if(!$baseIngredient) APIResponse(RESPONSE_400, "Update Ingredient with bad base ingredient ID.");
			else $this->BaseIngredient = $baseIngredient;
		}
		if (isset($ingredientData['id'])) $this->ID = (int)$ingredientData['id'];
		if (isset($ingredientData['title']))$this->Title = $ingredientData['title'];
		if (isset($ingredientData['type']))$this->Type = $ingredientData['type'];
		if (isset($ingredientData['description']))$this->Description = $ingredientData['description'];
	}

	/** @return bool */
	public function UpdateDatabase() {
		$queryString = "
            UPDATE tblIngredients
            SET
                `title` = ".$this->DB->Quote($this->Title)."
                , `description` = ".$this->DB->Quote($this->Description)."
                , `type` = ".$this->DB->Quote($this->Type)."
                , `modifyStamp` = ".microtime(true)."
            WHERE id = ".(int)$this->ID." AND userID = ".(int)$this->Session->ID."
        ";
		return (bool)$this->DB->Query($queryString);
	}

	public function AddToDatabase() {

		//TODO:  You know what?  Let's do a prepared statement!;
		/*
		$type = (isset($this->Type)) ? $this->DB->Quote($this->Type) ? 'Private';

		$queryString = "
			INSERT INTO tblIngredients
				(userID, type, title, description, baseIngredientID, createStamp, modifyStamp)
				VALUES (
					".(int)$this->Session->ID."
					, ".."
				)
		";
		*/
	}

	/**
	 * @return string[]
	 * @param bool $sendBase
	 */
	public function ToArray($sendBase = true) {
		return ($sendBase && $this->BaseIngredient)
			? array(
				'id'      => (int)$this->ID
				, 'title' => $this->Title
				, 'base'  => $this->BaseIngredient->ToArray(false)
			)
			: array(
				'id'      => (int)$this->ID
				, 'title' => $this->Title
			);
	}

}