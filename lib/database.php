<?php

function db_selVerbrauch($vID, $from=null, $to=null){
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
    /*
    # Fehlende Werte mit dem Durchschnitt der letzten und nächsten NOT_NULL Werte errechnen.
    */
    $select_union_values = db_helper_daterange($from, $to);
    $sql_string = "
        SELECT Stamps.ts, t2.verbraucher_id, t2.verbraucher, t2.ort, t2.wert, t2.einheit, t2.datum FROM (
            ${select_union_values}
        ) as Stamps
        LEFT JOIN (SELECT Alle.*
            FROM `Alle`
            WHERE `Alle`.`verbraucher_id` = ?
            ) as t2
        ON t2.datum = Stamps.ts;";
    $stmt = mysqli_prepare($connection, $sql_string) or die("DB Fehler (db_selVerbrauch)");
    mysqli_stmt_bind_param($stmt, 'd', $vID);
    mysqli_execute($stmt);
    mysqli_stmt_bind_result($stmt, $given_timestamp, $id, $verbraucher, $ort, $wert, $einheit, $datum);
    while (mysqli_stmt_fetch($stmt)) {
        // Wasserzähler korrigieren
        // TODO: Zählerwechsel berücksichtigen: Issue #2
        #if ( $id == 3 and strtotime($datum) > strtotime(WATER_DATE_1)) { $wert += WATER_ADD_1; }
        $results['ts'][] = $given_timestamp;
        $results['id'][] = $id;
        $results['verbraucher'][] = $verbraucher;
        $results['ort'][] = $ort;
        $results['wert'][] = ($wert) ? round($wert) : null;
        $results['einheit'][] = $einheit;
        $results['datum'][] = $datum;
    }
    mysqli_stmt_close($stmt);

    // Calculate missing Values
    $sql_string = "
        SELECT verbraucher_id, verbraucher, wert, einheit, ort, DATEDIFF(DATE(?), datum) as tsdiff FROM (
            (SELECT verbraucher_id, verbraucher, wert, einheit, ort, datum FROM `Alle`
                WHERE 
                    `Alle`.`verbraucher_id` = ? AND
                    datum >= DATE(?)
                ORDER BY datum ASC
                LIMIT 1)
            UNION ALL
                (SELECT verbraucher_id, verbraucher, wert, einheit, ort, datum FROM `Alle`
                WHERE
                    `Alle`.`verbraucher_id` = ? AND
                    datum < DATE(?)
                ORDER BY datum DESC
                LIMIT 1)
        ) AS t1 ORDER BY datum ASC;";
    $stmt = mysqli_prepare($connection, $sql_string) or die("DB Fehler (db_selVerbrauch)");
    mysqli_stmt_bind_param($stmt, 'sdsds', $calc_date, $verbraucher_id,
                                        $calc_date, $verbraucher_id, $calc_date);

    foreach ($results['wert'] as $idx => $wert) {
        if (isset($wert)) {continue;}

        // Ableselücke vorhanden
        $verbraucher_id = $vID;
        $calc_date = $results['ts'][$idx];
        $results['id'][$idx] = $vID;
        $results['datum'][$idx] = $calc_date;

        mysqli_execute($stmt);
        $r = mysqli_stmt_get_result($stmt);
        $r = mysqli_fetch_all($r, MYSQLI_BOTH);
        foreach ($r as $row => $result) {
            $date_rows[$row] = $result;
        }
        $results['verbraucher'][$idx] = $date_rows[0]['verbraucher'];
        $results['ort'][$idx] = $date_rows[0]['ort'];
        $results['einheit'][$idx] = $date_rows[0]['einheit'];

        // -- Calculate 'wert'
        $range_verbrauch = abs($date_rows[0]['wert'] - $date_rows[1]['wert']);
        $range_days = abs($date_rows[0]['tsdiff']) + abs($date_rows[1]['tsdiff']);
        $range_verbrauch_pro_tag = $range_verbrauch / $range_days;
        $results['wert'][$idx] = $range_verbrauch_pro_tag * $date_rows[0]['tsdiff'] + $date_rows[0]['wert'];
        $results['wert'][$idx] = round($results['wert'][$idx], 1);

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

function db_helper_daterange($from, $to){
//-> Erstellt eine Reihe von SQL VALUE Anweisungen
//  für eine Datumsreihe
    // End-Datum:
    if (!isset($to)){
        $to = date('Y-m-15', strtotime('now'));
    }
    // Start-Datum:
    if (!isset($from)){
        $from = strtotime($to);
        $from = date('Y-m-15', strtotime('-12 month', $from));
    }
    if ($to <= $from) {
        return false;
    }
    $step_date = $from;
    $to = strtotime($to);
    $stmt = "SELECT '${step_date}' as ts UNION VALUES
    ";
    while ($step_date < date('Y-m', $to)) {
        $stmt .= "('${step_date}'),";
        $step_date = strtotime($step_date);
        $step_date = date('Y-m-d', strtotime('+1 month', $step_date));
    }
    $stmt .= "('".date('Y-m-d', $to)."')";
    return $stmt;
}

?>