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

	/**
	 * Ingredient constructor.
	 * @param Session $session
	 * @param string[] $ingredientData
	 */
	public function __construct($session, $ingredientData) {
		$this->Session = $session;
		$this->DB = $session->DB;
		if (isset($ingredientData['id'])) {
			$this->SetData($ingredientData);
			$this->Valid = true;
		}
		else { //Creating a new ingredient
			$this->SetData($ingredientData);
			$this->CreateStamp = microtime(true);
			$this->AddToDatabase();
		}
	}

	public function Valid() { return (bool)$this->ID; }

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

	/** @param string[] $data */
	public function SetData($data) {
		if (isset($data['baseIngredientID'])) {
			if ($data['baseIngredientID']) {
				$baseIngredient = $this->Session->IngredientByID($data['baseIngredientID']);
				if (!$baseIngredient) APIResponse(RESPONSE_400, "Update Ingredient with bad base ingredient ID.");
				else $this->BaseIngredient = $baseIngredient;
			}
			else $this->BaseIngredient = false;
		}
		if (isset($data['id'])) $this->ID = (int)$data['id'];
		if (isset($data['title'])) $this->Title = $data['title'];
		if (isset($data['type'])) $this->Type = $data['type'];
		if (isset($data['description'])) $this->Description = $data['description'];
	}

	/** @return bool */
	public function UpdateDatabase() {
		$this->DB->Prepare("
            UPDATE tblIngredients
            SET
                `title` = :title
                , `description` = :description
                , `type` = :type
                , `modifyStamp` = :modifyStamp
                , `baseIngredientID` = :baseIngredientID
            WHERE id = :id AND userID = :userID
        ");

		$success = $this->DB->Execute(array(
			":title"              => $this->Title
			, ":description"      => $this->Description
			, ":type"             => $this->Type
			, ":modifyStamp"      => microtime(true)
			, ":baseIngredientID" => Nullable($this->BaseIngredientID())
			, ":id"               => $this->ID
			, ":userID"           => $this->Session->ID
		));

		return $success;
	}

	public function AddToDatabase() {
		$this->DB->Prepare("
			INSERT INTO tblIngredients
				(userID, type, title, description, baseIngredientID, createStamp, modifyStamp)
				VALUES
				(:userID, :type, :title, :description, :baseIngredientID, :createStamp, :modifyStamp)
		");
		$id = $this->DB->Execute(array(
			":userID"             => $this->Session->ID
			, ":type"             => $this->Type
			, ":title"            => $this->Title
			, ":description"      => $this->Description
			, ":baseIngredientID" => Nullable($this->BaseIngredientID())
			, ":createStamp"      => $this->CreateStamp
			, ":modifyStamp"      => microtime(true)
		));

		if ($id) $this->ID = $id;
		singleLog("Created ingredient #$id");
		return $id;
	}

	/**
	 * @return bool|int
	 */
	public function BaseIngredientID() {
		return ($this->BaseIngredient) ? $this->BaseIngredient->ID : false;
	}

	/**
	 * @return string[]
	 * @param bool $sendBase
	 */
	public function ToArray($sendBase = true) {
		$result = array(
			'id'            => (int)$this->ID
			, 'title'       => $this->Title
			, 'description' => $this->Description
			, 'created'     => $this->CreateStamp
			, 'modified'    => $this->ModifyStamp
		);
		if ($sendBase && $this->BaseIngredient) $result += array('base' => $this->BaseIngredient->ToArray(false));
		return $result;
	}

}