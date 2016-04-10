<?php

/*
 * Jer's yet undetermined license goes here
 */

class Vector2 {
	public $X = false;
	public $Y = false;

	public function __construct($x = false, $y = false) {
		$this->X = $x;
		$this->Y = $y;
	}

	public function IsInt() {
		return trueInt($this->X) && trueInt($this->Y);
	}

	public function IsFloat() {
		return trueFloat($this->X) && trueFloat($this->Y);
	}

	public function Distance($source = false) {
		if(gettype($source) != 'object' || get_class($source) != 'Vector2') $source = new Vector2(0, 0);
		if($this->IsFloat() && $source->IsFloat()) {
			$x = $source->X - $this->X;
			$y = $source->Y - $this->Y;
			$distance = sqrt(($x ^ 2) + ($y ^ 2));
			return $distance;
		} else return false;
	}

	public function Normalize() {
		if($this->IsFloat()) {
			$distance = $this->Distance();
			return new Vector2($this->X / $distance, $this->Y / $distance);
		}
		else return false;
	}
}