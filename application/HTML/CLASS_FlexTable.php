<?php

class FlexTable {

	public $UniqueID;

	public $TableID;

	public $Width = false; //Only used if there are no ColumnTitles and no RowTitles

	public $Fields = array();
	public $FieldClasses = array();

	public $ColumnTitles = array();
	public $ColumnTitleClasses = array();
	public $ColumnClasses = array();
	public $ColumnRotate = array();

	public $RowTitles = array();
	public $RowTitleClasses = array();
	public $RowClasses = array();
	public $RowRotate = array();

	public $LastRowFoot = false;
	public $LastColumnFoot = false;

	public $Omnibox = false;

	public function __construct($id, $width = false) {
		$this->TableID = $id;
		$this->Width = $width;
		$this->UniqueID = uniqid("FlexTable");
	}

	//TODO: Add rotateMask
	public function SetColumns($titles, $rotateMask = array(), $titleClasses = array(), $columnClasses = array()) {
		$this->ColumnTitles = array();
		$this->ColumnTitleClasses = array();
		$this->ColumnClasses = array();
		$this->ColumnRotate = array();
		$maskCounter = 0;
		foreach($titles as $titleKey => $title) {
			$this->ColumnTitles[$titleKey] = $title;
			$this->ColumnTitleClasses[$titleKey] = (array_key_exists($titleKey, $titleClasses)) ? $titleClasses[$titleKey] : false;
			$this->ColumnClasses[$titleKey] = (array_key_exists($titleKey, $columnClasses)) ? $columnClasses[$titleKey] : false;
			$this->ColumnRotate[$titleKey] = (is_array($rotateMask) && array_key_exists($titleKey, $columnClasses))
				? $rotateMask[$titleKey]
				: substr($rotateMask, $maskCounter, 1);
			$maskCounter++;
		}
	}

	public function AddColumn($titleKey, $title, $rotate = true, $titleClasses = false, $columnClasses = false) {
		$this->ColumnTitles[$titleKey] = $title;
		$this->ColumnTitleClasses[$titleKey] = $titleClasses;
		$this->ColumnClasses[$titleKey] = $columnClasses;
		$this->ColumnRotate[$titleKey] = (bool)$rotate;
	}

	public function SetRows($titles, $rotateMask = array(), $titleClasses = array(), $rowClasses = array()) {
		$this->RowTitles = array();
		$this->RowTitleClasses = array();
		$this->RowClasses = array();
		$maskCounter = 0;
		foreach($titles as $titleKey => $title) {
			$this->RowTitles[$titleKey] = $title;
			$this->RowTitleClasses[$titleKey] = (array_key_exists($titleKey, $titleClasses)) ? $titleClasses[$titleKey] : false;
			$this->RowClasses[$titleKey] = (array_key_exists($titleKey, $rowClasses)) ? $rowClasses[$titleKey] : false;
			$this->RowRotate[$titleKey] = (is_array($rotateMask) && array_key_exists($titleKey, $rowClasses))
				? $rotateMask[$titleKey]
				: substr($rotateMask, $maskCounter, 1);
			$maskCounter++;
		}
	}

	public function AddRow($titleKey, $title, $rotate = true, $titleClasses = false, $rowClasses = false) {
		$this->RowTitles[$titleKey] = $title;
		$this->RowTitleClasses[$titleKey] = $titleClasses;
		$this->RowClasses[$titleKey] = $rowClasses;
		$this->RowRotate[$titleKey] = (bool)$rotate;
	}

	public function AddRowFields($rowKey = false, $data, $classes = array()) {
		if(!$this->Width || count($data) == $this->Width()) {
			$this->Width = count($data);
			if(count($this->ColumnTitles)) {
				if($rowKey === false) {
					$this->RowTitles[] = array();
					$rowKey = LastArrayKey($this->RowTitles);
					$this->RowTitleClasses[$rowKey] = false;
					$this->RowClasses[$rowKey] = false;
				}
				foreach($this->ColumnTitles as $columnKey=>$column) {
					if(array_key_exists($columnKey, $data)) {
						$this->Fields[$columnKey][$rowKey] = $data[$columnKey];
						$this->FieldClasses[$columnKey][$rowKey] = (array_key_exists($columnKey, $classes)) ? $classes[$columnKey] : false;
						unset($data[$columnKey]);
					}
				}
			}
			else {
				$columnKey = 0;
				foreach($data as $datumKey=>$datum) {
					$this->Fields[$columnKey][$rowKey] = $datum;
					$this->FieldClasses[$columnKey][$rowKey] = (array_key_exists($columnKey, $classes)) ? $classes[$columnKey] : false;
					unset($data[$datumKey]);
					$columnKey++;
				}
			}
			return $data; //Return the unmatched fields
		}
		else return false;
	}

	public function SetField($columnKey, $rowKey, $datum, $classes = false) {
		//if(array_key_exists($columnKey, $this->Fields) && array_key_exists($rowKey, $this->Fields[$columnKey])) {
		$this->Fields[$columnKey][$rowKey] = $datum;
		$this->FieldClasses[$columnKey][$rowKey] = $classes;
		return true;
		//}
		//else return false;
	}

	public function Width() {
		$columnCount = count($this->ColumnTitles);
		$rowCount = count($this->RowTitles);
		$dataCount = count($this->Fields);
		if($columnCount) return $columnCount;
		else if($rowCount && $dataCount) return $dataCount / $rowCount;
		else if($this->Width) return $this->Width;
		else return false;
	}

	public function Height() {
		$columnCount = count($this->ColumnTitles);
		$rowCount = count($this->RowTitles);
		$dataCount = count($this->Fields);
		if($rowCount) return $rowCount;
		else if($columnCount && $dataCount) return $dataCount / $columnCount;
		else if($this->Width && $dataCount) return $dataCount / $this->Width;
		else return false;
	}

	public function GetCoordinateKey($x, $y = false) {

		//Checks to see if it's a Vector2
		if(gettype($x) == 'object' && get_class($x) == 'Vector2') {
			$y = $x->Y;
			$x = $x->X;
		}

		if(($x === false || $x < $this->Width()) && ($y === false || $y < $this->Height())) {
			$result = array('x' => false, 'y' => false);
			if($x !== false) {
				$counter = 0;
				foreach($this->ColumnTitles as $columnKey => $column) {
					if($counter == $x) {
						$result['x'] = $columnKey;
						break;
					}
					$counter++;
				}
			}
			if($y !== false) {
				$counter = 0;
				foreach($this->RowTitles as $rowKey => $row) {
					if($counter == $y) {
						$result['y'] = $rowKey;
						break;
					}
					$counter++;
				}
			}
			return new Vector2($result['x'], $result['y']);
		} else return false;
	}

	public function GetRowKey($y) {
		$key = $this->GetCoordinateKey(false, $y);
		if($key) return $key->Y;
		else return false;
	}

	public function GetColumnKey($x) {
		$key = $this->GetCoordinateKey($x, false);
		if($key) return $key->X;
		else return false;
	}

	private function fillBlanks() {
		$width = $this->Width();
		$height = $this->Height();

		for($x = 0; $x < $width; $x++) {
			for($y = 0; $y < $height; $y++){
				$key = $this->GetCoordinateKey($x, $y);
				if(!array_key_exists($key->X, $this->Fields)) { $this->Fields[$key->X] = array(); $this->FieldClasses[$key->X] = array(); }
				if(!array_key_exists($key->Y, $this->Fields[$key->X])) { $this->Fields[$key->X][$key->Y] = false; $this->FieldClasses[$key->X][$key->Y] = false; }
			}
		}
	}

	public function Draw($rotate = false) {
		$this->fillBlanks();

		if(!$rotate) {
			$rowTitles = $this->RowTitles;
			$rowTitleClasses = $this->RowTitleClasses;
			$rowClasses = $this->RowClasses;
			//$rowRotate = $this->RowRotate;

			$columnTitles = $this->ColumnTitles;
			$columnTitleClasses = $this->ColumnTitleClasses;
			$columnClasses = $this->ColumnClasses;
			$columnRotate = $this->ColumnRotate;

			$lastRowFoot = $this->LastRowFoot;

			$width = $this->Width();
			$height = $this->Height();
		}
		else {
			$rowTitles = $this->ColumnTitles;
			$rowTitleClasses = $this->ColumnTitleClasses;
			$rowClasses = $this->ColumnClasses;
			//$rowRotate = $this->ColumnRotate;

			$columnTitles = $this->RowTitles;
			$columnTitleClasses = $this->RowTitleClasses;
			$columnClasses = $this->RowClasses;
			$columnRotate = $this->RowRotate;

			$lastRowFoot = $this->LastColumnFoot;

			$width = $this->Height();
			$height = $this->Width();
		}

		$hasRowTitles = (bool)count($rowTitles);
		$hasColumnTitles = (bool)count($columnTitles);

		echo "<!-- Begin $this->UniqueID -->";
		echo indent(1)."<div id='$this->UniqueID' class='FlexTable'>\r\n";

		echo indent(2)."<div id='$this->UniqueID-inner'>\r\n";

		echo indent(3)."<table id='$this->UniqueID-table'>\r\n";

		if($hasColumnTitles) {
			echo indent(4)."<thead id='".$this->UniqueID."-head'>\r\n".indent(5)."<tr>\r\n";
			$classBase = "header column rotate";
			$columnCounter = 0;
			if($hasColumnTitles) echo indent(6)."<th>".$this->Omnibox."</th>\r\n";
			foreach($columnTitles as $columnID => $columnTitle) {
				$columnTitleClass = (array_key_exists($columnID, $columnTitleClasses)) ? $columnTitleClasses[$columnID] : false;
				$columnClass = (array_key_exists($columnID, $columnClasses)) ? $columnClasses[$columnID] : false;
				//echo "ColumnClass: ".print_r($this->ColumnClasses, true);
				$class = ($columnRotate[$columnID]) ? $classBase.' rotate' : $classBase;

				$field = $columnTitle; //Assoc because shift
				$hoverClass = (is_array($field) && count($field) > 1) ? 'hoverItem' : false;

				echo indent(6)."<td class='$class column$columnCounter $columnClass $hoverClass '>\r\n";
				echo indent(7)."<div class='$columnTitleClass'>\r\n";
				if(is_array($field)) {
					if(!empty($field)) {
						if(count($field) > 1) {
							echo indent(8)."<span class='hoverHide'>".array_shift($field)."</span>\r\n";
							echo indent(8)."<span class='hoverShow'>".array_shift($field)."</span>\r\n";
						}
						else echo indent(8)."<span>".array_shift($field)."</span>\r\n";
					}
				}
				else echo indent(8)."<span>".$columnTitle."</span>\r\n";
				echo indent(7)."</div>\r\n";
				echo indent(6)."</td>\r\n";
				$columnCounter++;
			}
			echo indent(5)."</tr>\r\n".indent(4)."</thead>\r\n";
		}
		echo indent(4)."<tbody>\r\n";

		for($y = 0; $y < $height; $y++) {

			if ($lastRowFoot && $y == $height - 1) echo indent(4)."</tbody>\r\n".indent(4)."<tfoot>\r\n";

			$rowKey = ($rotate) ? $this->GetColumnKey($y) : $this->GetRowKey($y);

			echo indent(5)."<tr>\r\n";

			if($hasRowTitles) echo indent(6)."<th class='".$rowTitleClasses[$rowKey]."'>".$rowTitles[$rowKey]."</th>";
			for($x = 0; $x < $width; $x++) {

				$fieldKey = ($rotate) ? $this->GetCoordinateKey($y, $x) : $this->GetCoordinateKey($x, $y);
				$relativeFieldKey = ($fieldKey !== false && $rotate) ? new Vector2($fieldKey->Y, $fieldKey->X) : $fieldKey;

				if($fieldKey) {
					$field = $this->Fields[$fieldKey->X][$fieldKey->Y]; //Assoc because shift
					$hoverClass = (is_array($field) && count($field) > 1) ? 'hoverItem' : false;
					$columnClass = (array_key_exists($relativeFieldKey->X, $columnClasses)) ? $columnClasses[$relativeFieldKey->X] : false;
					echo indent(6).
						"<td class='column column$x $hoverClass ".
						$columnTitleClasses[$relativeFieldKey->X]." ".
						$rowClasses[$relativeFieldKey->Y]." ".
						$this->FieldClasses[$fieldKey->X][$fieldKey->Y].
						" $columnClass'>\r\n";
					echo indent(7)."<div>\r\n";
					if(is_array($field)) {
						if(!empty($field)) {
							if(count($field) > 1) {
								echo indent(8)."<span class='hoverHide'>".array_shift($field)."</span>\r\n";
								echo indent(8)."<span class='hoverShow'>".array_shift($field)."</span>\r\n";
							}
							else echo indent(8)."<span>".array_shift($field)."</span>\r\n";
						}
					}
					else echo indent(8)."<span>".$this->Fields[$fieldKey->X][$fieldKey->Y]."</span>\r\n";
					echo indent(7)."</div>\r\n";
					echo indent(6)."</td>\r\n";
				}
				else echo indent(6)."<td class='column column$y'></td>";
			}
			echo indent(5)."</tr>\r\n";
		}
		echo ($lastRowFoot) ? indent(4)."</tfoot>\r\n" : indent(4)."</tbody>\r\n";

		echo indent(3)."</table>\r\n";

		echo indent(2)."</div>\r\n";

		echo indent(1)."</div>\r\n";

		?>
		<script type='text/javascript'>

			(function() {

				<?php for($gridID = 0; $gridID < $width; $gridID++) { //TODO: This isn't compatible with $rotate == true ?>

				$('#<?= $this->UniqueID ?>').delegate(".column<?=$gridID?>", "mouseover mouseout", function(e) {
					if(e.type == 'mouseover') {
						$("#<?= $this->UniqueID ?> .column<?=$gridID?>").removeClass("rotate").addClass('highlight');
					}
					else {
						$("#<?= $this->UniqueID ?> .column<?=$gridID?>").addClass("rotate").removeClass('highlight');
					}
				});

				<?php } ?>

				var $outer = $("#<?= $this->UniqueID ?>"),
					$inner = $outer.find("#<?= $this->UniqueID ?>-inner"),
					$table = $inner.find('#<?= $this->UniqueID ?>-table'),
					$head = $table.find('#<?=$this->UniqueID?>-head')
					;

				$outer.mousemove(function(e) {

					var outerWidth = $outer.width();
					var innerWidth = $head.width();

					if(innerWidth > outerWidth) {
						var offset = $outer.offset();
						var scrollFactor = (e.pageX - offset.left - (outerWidth * .2)) / (outerWidth * .6);
						scrollFactor = Math.min(Math.max(0, scrollFactor), 1); //Clamp to float factor 0 - 1
						$inner.css({marginLeft: -($head.width() - outerWidth) * scrollFactor});
					}
					else $inner.css({marginLeft: 0});
				}).hover(false, function() {
					//Uncomment to put it back the way you found it when mouseout.
					//$inner.css({marginLeft: 0});
				});

			})();

		</script>
		<?php

		echo "<!-- End $this->UniqueID -->";
	}

	public function PrintData($first = true, $indent = '') {
		if($first) echo "<pre>\r\n";

		echo $indent."UniqueID: $this->UniqueID\r\n";
		echo $indent."TableID: $this->TableID\r\n";
		echo $indent."Dimensions: ".(int)$this->Width()."x".(int)$this->Height()."\r\n";

		echo $indent."Rows:\r\n";
		foreach($this->RowTitles as $rowTitleID=>$rowTitle) {
			echo $indent."\t$rowTitleID - $rowTitle\r\n";
			echo $indent."\t\tRotate: ".($this->RowRotate[$rowTitleID] ? "true" : "false")."\r\n";
			echo $indent."\t\tTitleClasses: $this->RowTitleClasses[$rowTitleID]\r\n";
			echo $indent."\t\tClasses: $this->RowClasses[$rowTitleID]\r\n";
		}

		echo $indent."Columns:\r\n";
		foreach($this->ColumnTitles as $columnTitleID=>$columnTitle) {
			echo $indent."\t$columnTitleID - $columnTitle\r\n";
			echo $indent."\t\tRotate: ".($this->ColumnRotate[$columnTitleID] ? "true" : "false")."\r\n";
			echo $indent."\t\tTitleClasses: $this->ColumnTitleClasses[$columnTitleID]\r\n";
			echo $indent."\t\tClasses: $this->ColumnClasses[$columnTitleID]\r\n";
		}

		echo $indent."Fields:\r\n";
		echo print_r($this->Fields, true);
		foreach($this->Fields as $fieldID=>$field) {
			echo $indent."\t$field\r\n";
			echo $indent."\t\tClasses: $this->FieldClasses[$fieldID]\r\n";
		}

		if($first) echo "</pre>\r\n";
	}
}