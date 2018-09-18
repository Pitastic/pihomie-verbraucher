<?php

function calc_Steigerung($arr_Vals, $inProzent=True){
	$arr_Steigerung = [];

	// Steigung berechnen und in Array legen
	for ($idx=0; $idx < count($arr_Vals); $idx++) {
		$next = floatval($arr_Vals[$idx]);
		if ($idx > 0) {
			if (isset($first)) {
				$previous = $first;
				unset($first);
			}else{
				$previous = floatval($arr_Vals[$idx-1]);
			}
			$arr_Steigerung[$idx] = ($inProzent) ? (($next-$previous)/$previous)*100: ($next-$previous);
		}else{
			$first = $next;
		}
	}
	return $arr_Steigerung;
}


function getMonth($arr_datum){
	$monate = array("-", "Jan", "Feb", "Mrz", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez");
	for ($i=0; $i < count($arr_datum); $i++) {
		$temp = explode("-", $arr_datum[$i]);
		$m = intval($temp[1]);
		$arr_datum[$i] = $monate[$m];
	}
	return $arr_datum;
}


function rutschAuf($arr, $kick=false){
	if ($kick) {array_pop($arr);}
	// Alle einen aufrutschen für den Chart
	$sorted = array();
	foreach ($arr as $key => $value) {
		$sorted[$key-1] = $value;
	}
	return $sorted;
}

// DEV Funktionen
function preOut($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
	return;
}

?>