<?php

function db_selVerbrauch($vID){
//-> Datenreihe für einen Verbraucher selektieren
	$results = array(
		'id'		=> array(),
		'verbraucher' => array(),
		'ort'		=> array(),
		'wert'		=> array(),
		'einheit'	=> array(),
		'datum'		=> array(),
	);
	@$connection = new mysqli(DB_HOST, DB_USER, DB_PW, DB, DB_PORT);
	mysqli_set_charset($connection, "utf8");
	$sql_string = "
	SELECT verbraucher_id, verbraucher, ort, wert, einheit, datum FROM `Alle`
	WHERE verbraucher_id = ?
	ORDER BY `Alle`.`datum` ASC
	LIMIT 0,25
	";
	$stmt = mysqli_prepare($connection, $sql_string) or die("DB Fehler (db_selVerbrauch)");
	mysqli_stmt_bind_param($stmt, 'd', $vID);
	mysqli_execute($stmt);
	mysqli_stmt_bind_result($stmt, $id, $verbraucher, $ort, $wert, $einheit, $datum);
	while (mysqli_stmt_fetch($stmt)) {
		// Wasserzähler korrigieren
		// TODO: Zählerwechsel berücksichtigen: Issue #2
		if ( $id == 3 and strtotime($datum) > strtotime(WATER_DATE_1)) { $wert += WATER_ADD_1; }
		$results['id'][] = $id;
		$results['verbraucher'][] = $verbraucher;
		$results['ort'][] = $ort;
		$results['wert'][] = $wert;
		$results['einheit'][] = $einheit;
		$results['datum'][] = $datum;
	}
	mysqli_stmt_close($stmt);
	mysqli_close($connection);
	return $results;
}


function db_selVerbraucher(){
//-> mögliche Datenreihen lesbar selektieren
	$results = [];
	@$connection = new mysqli(DB_HOST, DB_USER, DB_PW, DB, DB_PORT) or die(mysqli_connect_error);
	mysqli_set_charset($connection, "utf8");
	$sql_string = "
	SELECT id, verbraucher FROM `verbraucher` ORDER BY lower(verbraucher.verbraucher) ASC
	";
	$stmt = mysqli_prepare($connection, $sql_string) or die(mysqli_connect_error());
	mysqli_execute($stmt);
	mysqli_stmt_bind_result($stmt, $id, $verbraucher);
	while (mysqli_stmt_fetch($stmt)) {
		$results[$id] = array(
			'id'			=> $id,
			'verbraucher'	=> $verbraucher,
		);
	}
	mysqli_stmt_close($stmt);
	mysqli_close($connection);
	return $results;
}

function db_insertZaehler($id, $val, $date){
//-> Zählerstandsablesung eintragen
	@$connection = new mysqli(DB_HOST, DB_USER, DB_PW, DB, DB_PORT) or die(mysqli_connect_error);
	mysqli_set_charset($connection, "utf8");
	$sql_string = "INSERT INTO ablesen(`verbraucher_id`, `wert`, `datum`) VALUES(?,?,?);";
	$stmt = mysqli_prepare($connection, $sql_string) or die(mysqli_connect_error());
	mysqli_stmt_bind_param($stmt, 'sss', $id, $val, $date);
	mysqli_execute($stmt);
	mysqli_stmt_close($stmt);
	mysqli_close($connection);
	return;
}

?>