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
		if (empty(array_diff_key(Ingredient::ValidArray(), $ingredientData))) { //Loading an existing ingredient
			$this->Refresh($ingredientData);
			$this->Valid = true;
		}
		else if (empty(array_diff_key(Ingredient::NewArray(), $ingredientData))) { //Creating a new ingredient

			$this->Title = $ingredientData['title'];

			$this->CreateStamp = $this->ModifyStamp = (float)microtime(true);
			$id = (int)$this->DB->Query("
                INSERT INTO tblIngredients
                (userID, type, title, createStamp, modifyStamp)
                VALUES(
                    ".(int)$this->Session->ID."
                    , ".$this->DB->Quote($this->Type)."
                    , ".$this->DB->Quote($this->Title)."
                    , $this->CreateStamp
                    , $this->ModifyStamp
                )
            ", true);
			if ($id) {
				$this->ID = $id;
				$this->Valid = true;
			}
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
					if (isset($_POST['type'])) $this->Description = $_POST['type'];
					if (isset($_POST['title'])) $this->Description = $_POST['title'];

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
	public function Refresh($ingredientData) {
		$this->ID = (int)$ingredientData['id'];
		$this->Title = $ingredientData['title'];
		$this->CreateStamp = (float)$ingredientData['createStamp'];
		$this->ModifyStamp = (float)$ingredientData['modifyStamp'];
	}

	/** @return bool */
	public function UpdateDatabase() {
		$queryString = "
            UPDATE tblIngredients
            SET
                `title` = ".$this->DB->Quote($this->Title)."
                , `modifyStamp` = ".microtime(true)."
            WHERE id = ".(int)$this->ID." AND userID = ".(int)$this->Session->ID."
        ";
		return (bool)$this->DB->Query($queryString);
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

	/** @return string[] */
	public static function ValidArray() {
		return array(
			'id'            => false
			, 'title'       => false
			, 'createStamp' => false
			, 'modifyStamp' => false
		);
	}

	/** @return string[] */
	public static function NewArray() {
		return array(
			'title' => false
		);
	}

}