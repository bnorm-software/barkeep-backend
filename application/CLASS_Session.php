<?php

class Session {

	/** @var MySQLDatabase */
	public $DB;

	/** @var int */
	public $ID;

	/** @var string */
	public $Username;
	/** @var string */
	public $DisplayName;

	/** @var Book[] */
	public $Books = array();
	/** @var Book[] */
	public $BooksByTitle = array();
	/** @var Bar[] */
	public $Bars = array();
	/** @var Bar[] */
	public $BarsByTitle = array();
	/** @var Ingredient[] */
	public $Ingredients = array();

	/** @var string|bool */
	private $auth = false;

	/**
	 * Session constructor.
	 * @param MySQLDatabase $db
	 */
	public function __construct($db) {
		$this->DB = $db;
	}

	/**
	 * @param string[] $server
	 * @param string $path
	 * @param Perflog|bool $perfLog
	 */
	public function Process($server, $path, $perfLog = false) {
		if ($perfLog) {
			$log = "Session Started\r\n";
			$log .= "ID:            ".session_id()."\r\n";
			$log .= "IP Address:    ".$_SERVER['REMOTE_ADDR']."\r\n";
			$perfLog->Log($log);
		}

		$headers = getallheaders();
		$method = (isset($server['REQUEST_METHOD'])) ? $server['REQUEST_METHOD'] : false;
		if ($this->LoggedIn()) {
			if (!empty($path)) {
				$item = array_shift($path);
				switch ($item) {
					case 'books':
						if (empty($path)) {
							switch ($method) {
								case "GET":
								case "POST":
									$handler = $method."_Books";
									if (method_exists($this, $handler)) $this->$handler();
									else APIResponse(RESPONSE_404, "Could not find books handler $handler.");
									break;

								default:
									APIResponse(RESPONSE_400, "Bad book request method.");
									break;
							}
						}
						else {
							$bookID = array_shift($path);
							if (!array_key_exists($bookID, $this->Books)) $this->RefreshBooks();
							if (array_key_exists($bookID, $this->Books)) $this->Books[$bookID]->Process($server, $path, $headers);
							else APIResponse(RESPONSE_404, "Book $bookID not found.");
						}
						break;

					case 'bars':
						if (empty($path)) {
							switch ($method) {
								case "GET":
								case "POST":
									$handler = $method."_Bars";
									if (method_exists($this, $handler)) $this->$handler();
									else APIResponse(RESPONSE_404, "Could not find bars handler $handler.");
									break;

								default:
									APIResponse(RESPONSE_400, "Bad bar request method.");
									break;
							}
						}
						else {
							$barID = array_shift($path);
							if (!array_key_exists($barID, $this->Bars)) $this->RefreshBars();
							if (array_key_exists($barID, $this->Bars)) $this->Bars[$barID]->Process($server, $path, $headers);
							else APIResponse(RESPONSE_404, "Bar $barID not found.");
						}
						break;

					case 'ingredients':
						if (empty($path)) {
							switch ($method) {
								case "GET":
								case "POST":
									$handler = $method."_Ingredients";
									if (method_exists($this, $handler)) $this->$handler();
									else APIResponse(RESPONSE_404, "Could not find ingredients handler $handler.");
									break;

								default:
									APIResponse(RESPONSE_400, "Bad ingredient request method.");
									break;
							}
						}
						else {
							$ingredientID = array_shift($path);
							if (!array_key_exists($ingredientID, $this->Ingredients)) $this->RefreshIngredients();
							if (array_key_exists($ingredientID, $this->Ingredients)) $this->Ingredients[$ingredientID]->Process($server, $path, $headers);
							else APIResponse(RESPONSE_404, "Ingredient $ingredientID not found.");
						}
						break;

					case 'logout':
						$this->auth = false;
						header("Clear-Authorization: true");
						APIResponse(RESPONSE_200);
						//TODO: Destroy the session as well.
						break;

					default:
						break;
				}
			}
			else {
				switch ($method) {
					case "GET":
					case "PUT":
						$handler = $method;
						if (method_exists($this, $handler)) $this->$handler();
						else APIResponse(RESPONSE_404, "Could not find session handler $handler.");
						break;
				}
			}
		}
		else {
			if (!empty($path)) {
				$item = array_shift($path);
				switch ($item) {
					case 'login':
						$username = getParam('username');
						$password = getParam('password');
						if ($username && $password) {
							$username = $this->DB->Quote($username);
							$password = $this->DB->Quote($password);
							$login = $this->DB->GetRow("SELECT * FROM tblUsers WHERE username=$username AND password=$password;");
							if ($login && (int)$login['id']) {
								$this->auth = sha1(uniqid('randomsalt', true));
								$this->ID = (int)$login['id'];
								$this->Username = $login['username'];
								$this->DisplayName = $login['displayName'];

								header("Set-Authorization: $this->auth");

								APIResponse(RESPONSE_200, "Your name is $this->DisplayName");
							}
							else APIResponse(RESPONSE_401, 'Invalid Credentials');
						}
						else APIResponse(RESPONSE_401, 'No user or password given.  '.file_get_contents('php://input'));
						break;

					default:
						APIResponse(RESPONSE_401, 'You are not logged in.');
						break;
				}
			}
			else {
				switch ($method) {
					default:
						APIResponse(RESPONSE_401, 'You are not logged in.');
						break;
				}
			}
		}
		APIResponse(RESPONSE_400);
	}

	/** @param string[] $params */
	private function GET($params = array()) {
		APIResponse(RESPONSE_200, $this->ToArray());
	}

	/** @param string[] $params */
	private function PUT($params = array()) {
		$displayName = getParam('displayname');
		$password = getParam('password');
		if ($password) {
			if (!Session::PasswordEntropy($password)) {
				APIResponse(RESPONSE_400, "Insufficient Password Entropy");
				return;
			}
		}
		$passwordSQL = ($password) ? ", password = ".$this->DB->Quote($password) : false;

		$queryString = "UPDATE tblUsers SET displayName = ".$this->DB->Quote($displayName)."$passwordSQL WHERE id = ".$this->ID." LIMIT 1";
		$success = $this->DB->Query($queryString);
		if ($success) {
			$this->DisplayName = $displayName;
			APIResponse(RESPONSE_200);
		}
		else APIResponse(RESPONSE_500);
	}

	/** @return string[] */
	public function ToArray() {
		return array(
			'username'      => $this->Username
			, 'displayName' => $this->DisplayName
		);
	}

	/** @param string[] $params */
	private function GET_Books($params = array()) {
		APIResponse(RESPONSE_200, $this->bookList());
	}

	/** @return array[] */
	private function bookList() {
		$this->RefreshBooks();
		$result = array();
		foreach ($this->Books as $book) {
			$result[] = $book->ToArray();
		}
		return $result;
	}

	/** @param string[] $params */
	private function POST_Books($params = array()) {
		$title = getParam('title');
		$description = getParam('description');
		if ($title) {
			$id = $this->CreateBook($title, $description);
			if ($id) APIResponse(RESPONSE_200, $this->Books[$id]->ToArray());
			else APIResponse(RESPONSE_500, "Could not create new book.");
		}
		else APIResponse(RESPONSE_400, "Please supply a title.");
	}

	/**
	 * @param string $title
	 * @param string $description
	 * @return int|bool
	 */
	public function CreateBook($title, $description) {
		if ($title) {
			$newBook = new Book($this, array(
				'userID'        => $this->ID
				, 'type'        => 'Private'
				, 'title'       => $title
				, 'description' => $description
			));
			if ($newBook->Valid) {
				$this->Books[$newBook->ID] = $newBook;
				$this->RefreshBooksByTitle();
				return $newBook->ID;
			}
			else return false;
		}
		else return false;
	}

	public function RefreshBooks() {
		$books = $this->DB->Query("SELECT * FROM tblBooks WHERE userID = $this->ID AND active ORDER BY title");
		if ($books) {
			$expiredBooks = arraykeyskeys($this->Books);
			while ($book = $books->Fetch()) {
				if (array_key_exists($book['id'], $expiredBooks)) {
					unset($expiredBooks[$book['id']]);
				}
				if (array_key_exists($book['id'], $this->Books)) {
					$this->Books[$book['id']]->Refresh($book);
				}
				else {
					$newBook = new Book($this, $book);
					if ($newBook->Valid) $this->Books[$book['id']] = $newBook;
				}
			}
			foreach ($expiredBooks as $expiredBooksKey => $book) unset($this->Books[$expiredBooksKey]);
		}
		$this->RefreshBooksByTitle();
	}

	public function RefreshBooksByTitle() {
		$this->BooksByTitle = array();
		foreach ($this->Books as $book) $this->BooksByTitle[$book->Title] = $book;
	}

	/** @param string[] $params */
	private function GET_Bars($params = array()) {
		APIResponse(RESPONSE_200, $this->barList());
	}

	/** @return array[] */
	private function barList() {
		$this->RefreshBars();
		$result = array();
		foreach ($this->Bars as $bar) {
			$result[] = $bar->ToArray();
		}
		return $result;
	}

	/** @param string[] $params */
	private function POST_Bars($params = array()) {
		$title = getParam('title');
		$description = getParam('description');
		if ($title) {
			$id = $this->CreateBar($title, $description);
			if ($id) APIResponse(RESPONSE_200, $this->Bars[$id]->ToArray());
			else APIResponse(RESPONSE_500, "Could not create new bar.");
		}
		else APIResponse(RESPONSE_400, "Please supply a title.");
	}

	/**
	 * @param string $title
	 * @param string $description
	 * @return int|bool
	 */
	public function CreateBar($title, $description) {
		if ($title) {
			$newBar = new Bar($this, array(
				'userID'        => $this->ID
				, 'type'        => 'Private'
				, 'title'       => $title
				, 'description' => $description
			));
			if ($newBar->Valid) {
				$this->Bars[$newBar->ID] = $newBar;
				$this->RefreshBarsByTitle();
				return $newBar->ID;
			}
			else return false;
		}
		else return false;
	}

	public function RefreshBars() {
		$bars = $this->DB->Query("SELECT * FROM tblBars WHERE userID = $this->ID AND active ORDER BY title");
		if ($bars) {
			$expiredBars = arraykeyskeys($this->Bars);
			while ($bar = $bars->Fetch()) {
				if (array_key_exists($bar['id'], $expiredBars)) {
					unset($expiredBars[$bar['id']]);
				}
				if (array_key_exists($bar['id'], $this->Bars)) {
					$this->Bars[$bar['id']]->Refresh($bar);
				}
				else {
					$newBar = new Bar($this, $bar);
					if ($newBar->Valid) $this->Bars[$bar['id']] = $newBar;
				}
			}
			foreach ($expiredBars as $expiredBarsKey => $bar) unset($this->Bars[$expiredBarsKey]);
		}
		$this->RefreshBarsByTitle();
	}

	public function RefreshBarsByTitle() {
		$this->BarsByTitle = array();
		foreach ($this->Bars as $bar) $this->BarsByTitle[$bar->Title] = $bar;
	}

	private function GET_Ingredients($params = array()) {
		$search = getParam('search');

		//TODO: Search ingredient titles, both private and public

		//Crappy Query For Testing
		$searchString = ($search) ?  "AND title LIKE ".$this->DB->Quote("%$search%") : false;
		$queryString = "SELECT * FROM tblIngredients WHERE userID = $this->ID $searchString LIMIT 10";
		$query = $this->DB->Query($queryString);

		$result = array();
		if ($query) {
			while ($row = $query->Fetch()) {
				$ingredient = (array_key_exists($row['id'], $this->Ingredients)) ? $this->Ingredients[$row['id']] : new Ingredient($this, $row);
				$result[$ingredient->ID] = $ingredient->ToArray();
			}
		}
		APIResponse(RESPONSE_200, $result);
	}

	private function POST_Ingredients($params = array()) {
		$title = getParam('title');
		if ($title) {
			//TODO: Does it already exist?  Do we care?
			$id = $this->CreateIngredient($title);
			if ($id) APIResponse(RESPONSE_200, $this->Ingredients[$id]->ToArray());
			else APIResponse(RESPONSE_500, "Could not create new ingredient.");
		}
		else APIResponse(RESPONSE_400, "Please supply a title.");
	}

	public function CreateIngredient($title) {
		if ($title) {
			$newIngredient = new Ingredient($this, array(
				'userID'  => $this->ID
				, 'title' => $title
			));
			if ($newIngredient->Valid) {
				$this->Ingredients[$newIngredient->ID] = $newIngredient;
				return $newIngredient->ID;
			}
			else return false;
		}
		else return false;
	}

	/**
	 * @param bool|int $id
	 */
	public function RefreshIngredients($id = false) {
		$result = array();
		$condition = ($id) ? "AND tblIngredients.id = ".(int)$id : false;
		$queryString = "SELECT * FROM tblIngredients WHERE userID = $this->ID $condition";
		$query = $this->DB->Query($queryString);
		if($query) {
			while ($row = $query->Fetch()) {
				$ingredient = (array_key_exists($row['id'], $this->Ingredients)) ? $this->Ingredients[$row['id']] : new Ingredient($this, $row);
				$result[$ingredient->ID] = $ingredient;
			}
		}
		$this->Ingredients = $result;
	}

	/** @return bool */
	public function LoggedIn() {
		if ($this->auth) {
			$headers = getallheaders();
			if ($headers && isset($headers['Authorization']) && $headers['Authorization'] == $this->auth)
				return (bool)$this->auth;
			else {
				$this->auth = false;
				APIResponse(RESPONSE_401);
				exit;
			}
		}
		else return false;
	}

	/** @return int|bool */
	public static function PasswordEntropy($password) {
		//TODO: check password entropy here, duh.
		return true;
	}
}