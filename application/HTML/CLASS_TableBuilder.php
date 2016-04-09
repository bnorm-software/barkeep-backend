<?php

class TableBuilder { //Not very safe, so watch your inputs
	public $TableID = false;
	public $TableClasses = false;
	public $ColumnTitles = array();
	public $ColumnClasses = array();
	public $GlobalColumnClasses = array();
	public $ColumnClass = false;
	public $RowTitles = array();
	public $RowClasses = array();
	public $Data = array();
	public $DataClasses = array();

	private $width = 0;
	private $height = 0;

	public function __construct($tableID = false, $tableClasses = false) {
		$this->TableID = $tableID;
		$this->TableClasses = $tableClasses;
	}

	public function AddColumns($columns, $classes = array(), $globalClasses = array()) {
		foreach($columns as $id => $column) {
			$title = $column;
			$class = (is_array($classes) && array_key_exists($id, $classes)) ? $classes[$id] : false;
			$globalClass = (is_array($globalClasses) && array_key_exists($id, $globalClasses)) ? $globalClasses[$id] : false;
			$this->AddColumn($title, $class, $globalClass);
		}
	}

	public function AddColumn($column, $classes = false, $globalClasses = false) {
		$this->ColumnTitles[] = $column;
		$this->ColumnClasses[] = $classes;
		$this->GlobalColumnClasses[] = $globalClasses;
		$this->width = count($this->ColumnTitles);
	}

	public function AddRows($rows, $classes = false) {
		foreach($rows as $id => $row) {
			$class = (is_array($classes) && array_key_exists($id, $classes)) ? $classes[$id] : false;
			$this->AddRow($row, $class);
		}
	}

	public function AddRow($row, $classes = false) {
		$this->RowTitles[] = $row;
		$this->RowClasses[] = $classes;
		$this->height = count($this->RowTitles);
	}

	public function AddData($data, $classes = array()) {
		foreach($data as $yID => $y) {
			$rowArray = array();
			$classArray = array();
			foreach($y as $xID => $x) {
				$rowArray[] = $x;
				$classArray[] = (is_array($classes)
					&& array_key_exists($yID, $classes)
					&& array_key_exists($xID, $classes[$yID])) ? $classes[$yID][$xID] : false;
			}
			$this->AddLine($rowArray, $classArray);
		}
	}

	public function AddLine($rowArray, $classArray = array()) {
		$this->Data[] = $rowArray;
		$this->DataClasses[] = $classArray;
		$this->width = max($this->width, count($rowArray));
		$this->height = max($this->height, count($this->Data));
	}

	public function FillBlanks() {
		for($y = 0; $y < $this->height; $y++) {
			if(!array_key_exists($y, $this->Data)) $this->Data[$y] = array();
			for($x = 0; $x < $this->width; $x++) {
				if(!array_key_exists($y, $this->Data) || !array_key_exists($x, $this->Data[$y])) $this->Data[$y][$x] = false;
				if(!array_key_exists($y, $this->DataClasses) || !array_key_exists($x, $this->DataClasses[$y])) $this->DataClasses[$y][$x] = false;
			}
		}
	}

	public function Draw($return = false) {
		$result = '';
		$this->FillBlanks();
		$id = ($this->TableID) ? "id='".$this->TableID."'" : false;
		$classes = ($this->TableClasses) ? "class='".$this->TableClasses."'" : false;
		$result .= "\r\n\r\n<table ".$id." ".$classes.">\r\n";

		if(count($this->ColumnTitles)) {
			$result .= "\t<tr class='".$this->ColumnClass."'>\r\n";
			if(count($this->RowTitles)) $result .= "\t\t<th></th>\r\n";
			foreach($this->ColumnTitles as $columnID => $columnTitle) {
				$result .= "\t\t<th class='".$this->ColumnClasses[$columnID]." ".$this->GlobalColumnClasses[$columnID]."'>".$columnTitle."</th>\r\n";
			}
			$result .= "\t</tr>\r\n";
		}

		foreach($this->Data as $yID => $y) {
			$result .= "\t<tr>\r\n";
			if(count($this->RowTitles)) {
				$class = (array_key_exists($yID, $this->RowClasses)) ? " class='".$this->RowClasses[$yID]."'" : false;
				$result .= (array_key_exists($yID, $this->RowTitles)) ? "\t\t<th".$class.">".$this->RowTitles[$yID]."</th>\r\n" : "<th".$class."></th>\r\n";
			}
			foreach($y as $xID => $datum) {
				//$result .= "\t\t<td class='".$this->DataClasses[$yID][$xID]." ".$this->GlobalColumnClasses[$xID]."'>".$datum."</td>\r\n";
				$globalColumnClasses = (array_key_exists($xID, $this->GlobalColumnClasses)) ? $this->GlobalColumnClasses[$xID] : false;
				$result .= "\t\t<td class='".$this->DataClasses[$yID][$xID]." ".$globalColumnClasses."'>".$datum."</td>\r\n";
			}
			$result .= "\t</tr>\r\n";
		}

		$result .= "</table>\r\n\r\n";

		if($return) return $result;
		else echo $result;
	}
}


