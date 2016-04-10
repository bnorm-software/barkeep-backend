<?php

class Book {

	public $Valid = false;

	public $ID;
	public $Title;

	public $Type = 'Private';
	public $Path;
	public $Description = false;
	public $CreateStamp;
	public $ModifyStamp;

	public $Recipes = array();

	public $Session;

	public $DB;

	public function __construct($session, $bookData) { //This is a comment
		$this->Session = $session;
		$this->DB = $session->DB;
		if (empty(array_diff_key(ValidBookArray(), $bookData))) { //Loading an existing book
			$this->Refresh($bookData);
			$this->Valid = true;
		} else if (empty(array_diff_key(NewBookArray(), $bookData))) { //Creating a new book

			$this->Type = $bookData['type'];
			$this->Title = $bookData['title'];
			$this->Description = $bookData['description'];

			$this->CreateStamp = $this->ModifyStamp = (float)microtime(true);
			$id = (int)$this->DB->Query("
                INSERT INTO tblBooks
                (userID, type, title, description, createStamp, modifyStamp)
                VALUES(
                    " . (int)$this->Session->ID . "
                    , " . $this->DB->Quote($this->Type) . "
                    , " . $this->DB->Quote($this->Title) . "
                    , " . $this->DB->Quote($this->Description) . "
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

	public function Refresh($bookData) {
		$this->ID = (int)$bookData['id'];
		$this->Type = $bookData['type'];
		$this->Title = $bookData['title'];
		$this->Path = $bookData['path'];
		$this->Description = $bookData['description'];
		$this->CreateStamp = (float)$bookData['createStamp'];
		$this->ModifyStamp = (float)$bookData['modifyStamp'];
		$this->Valid = (bool)$bookData['active'];
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
					if (isset($_POST['description'])) $this->Description = $_POST['description'];

					$this->UpdateDatabase();
					APIResponse(RESPONSE_200, $this->ToArray());
					break;

				case 'DELETE':
					$query = $this->DB->Query("
                        UPDATE tblBooks
                        SET active = 0
                        WHERE userID = " . (int)$this->Session->ID . "
                            AND id = " . (int)$this->ID . " LIMIT 1;
                    ");
					if ($query) {
						$this->Session->RefreshBooks();
						APIResponse(RESPONSE_200);
					} else {
						APIResponse(RESPONSE_500);
					}
					break;

				default:

					break;
			}
		}
	}

	public function UpdateDatabase() {
		$queryString = "
            UPDATE tblBooks
            SET
                `type` = " . $this->DB->Quote($this->Type) . "
                , `title` = " . $this->DB->Quote($this->Title) . "
                , `description` = " . $this->DB->Quote($this->Description) . "
                , `modifyStamp` = " . microtime(true) . "
            WHERE id = " . (int)$this->ID . " AND userID = " . (int)$this->Session->ID . "
        ";
		return (bool)$this->DB->Query($queryString);
	}

	public function ToArray() {
		return array(
			'id' => (int)$this->ID
			, 'type' => $this->Type
			, 'title' => $this->Title
			, 'path' => $this->Path
			, 'description' => $this->Description
			//, 'createStamp'=>(float)$this->CreateStamp
			//, 'modifyStamp'=>(float)$this->ModifyStamp
		);
	}
}

function ValidBookArray() {
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

function NewBookArray() {
	return array(
		'userID' => false
		, 'type' => false
		, 'title' => false
		, 'description' => false
	);
}