<?php

function BottomTab($color, $title, $href = false, $class=false) {
	$title = Display($title, true);
	if($href) {
		$title = "<a href='".$href."'>".$title."</a>";
	}
	?>
		<div class='<?=$color?>Tab <?=$class?>'>
			<div class='l'></div>
			<div class='m'><?=$title?></div>
			<div class='r'></div>
		</div>
	<?php
}

const CHECK_TYPE_TRUE = 1;
const CHECK_TYPE_FALSE = 0;
const CHECK_TYPE_PARTIAL = 0.5;

//Be careful not to mix both " and ' in the $js param.
function CheckBoxLink($text, $js = false, $id = false, $class = false, $checked = CHECK_TYPE_FALSE, $showBox = true, $return = false) {
	if(!strstr($js, "'") && strstr($js, '"')) $js = str_replace('"', "'", $js); //Convert " to '
	if(!$id) $id = "checkBoxLink-".uniqid();
	$js = ($js) ? "href=\"javascript:$js\"" : false;

	$checkedClass = false;
	if($checked > CHECK_TYPE_FALSE && $checked < CHECK_TYPE_TRUE) $checkedClass = 'partial';
	else if($checked) $checkedClass = 'checked';

	$class = "class='selector $class $checkedClass'";
	//$text = htmlspecialchars($text, ENT_QUOTES);

	$box = ($showBox) ? "<span class='selectorBox'><span></span></span>" : false;

	$result = "

		<a $js id='$id' $class>
			$box
			<span class='selectorText'>$text</span>
		</a>

		<script type='text/javascript'>
			$('#$id').click(function() { $(this).addClass('busy partial'); });
		</script>

	";

	if($return) return $result;
	else {
		echo $result;
		return $id;
	}
}