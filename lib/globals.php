<?php
/*
Layout
	- Ein Diagramm pro Verbraucher
	- Eine Datenreihe je Jahr (Jan-Dez)
	- Zuschaltbar / Farblich angepasst ?
*/


// Datenbank Variablen
define("DB", "Verbrauch");
define("DB_USER", "skript");
define("DB_PW", "MySecurePass-Change-It!");
define("DB_HOST", "localhost");
define("DB_PORT", 3306);

// Zählerkorrekturen (Einbau neuer Zähler)
define(WATER_ADD_1, 518);
define(WATER_DATE_1, "2018-03-01");


// Fake Eintragungen
/*
	-- Wasser
	> 2018-02-16
	> 2018-04-16
	> 2018-05-16

	-- Strom
	> 2018-02-16
	> 2018-04-16
	> 2018-05-16
	
	-- Gas
	> 2018-02-16
	> 2018-04-16
	> 2018-05-16
*/
?>