<?php

require 'lib/globals.php';
require 'lib/database.php';
require 'lib/functions.php';

if (isset($_POST['eingeben'])) {
	if (count($_POST['ids']) and isset($_POST['datum'])) {
		$ids = $_POST['ids'];
		for ($i=0; $i < count($ids); $i++) { 
			if (isset($_POST["id".$ids[$i]]) and $_POST["id".$ids[$i]] != "" and $_POST["id".$ids[$i]] != 0) {
				db_insertZaehler($ids[$i], $_POST["id".$ids[$i]], $_POST['datum']);
			}
		}
	}
	header("Location: ".$_SERVER['PHP_SELF']."?msg=gespeichert");
}

?>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Verbraucher</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<link rel="stylesheet" href="./style/style.css">
</head>

<body>
	<header class="w3-container w3-light.gray w3-padding">
		<h1>Verbraucher im Haus
			<a href="index.php" class="w3-button w3-right">&#127968;</a>
			<a href="javascript:toggleModal('query');" class="w3-button w3-right">&#128269;</a>
		</h1>
	</header>

	<div class="w3-container w3-margin-bottom" id="content">

	<?php

	$alleV = db_selVerbraucher();

	if (isset($_POST['query'])){	// Abfrage an die Daten ---
		$MODE = "query";

		if ($_POST['query_type'] == "jahr") {
			$from = $_POST['jahr'] . "-01-01";
			$to = $_POST['jahr'] . "-12-31";
		}else{
			$from = $_POST['start_punkt'];
			$to = $_POST['end_punkt'];
		}
		$results = db_selVerbrauch($_POST['verbraucher'], $from, $to);
		$results['Steigung'] = calc_Steigerung($results['wert'], False);
		$results['datum_M'] = getMonth($results['datum']);

		// alle aufrücken wegen Chart
		//array_unshift($results['Steigung'], null);
		$results['Steigung'] = rutschAuf($results['Steigung']);

		// Summe des Verbrauchs
		$gesamtVerbrauch = $results['wert'][count($results['wert'])-1] - $results['wert'][0];
		// Anzahl der zu erwartenden Datenpunkte (1/Monat)
		$expectedPoints = diffDates($from, $to)['M'];
		?>

		<div class="w3-card-2 w3-section w3-margin">
			<header class="w3-container w3-padding w3-black">
				<?php echo $results['verbraucher'][0]." in ".$results['einheit'][0];?>
			</header>
			<div class="w3-container">
				<canvas width="300" height="150" id="LineChart0"></canvas>
			</div>
			<footer class="w3-container w3-padding w3-sand">
				<?php echo "
				<p>
					Der Gesamtverbrauch betrug in der Zeit vom " . $from . " bis " . $to . " insgesamt <b>". $gesamtVerbrauch ." ". $results['einheit'][0] ."</b> .
				</p>
				<p>
					Es wurden " . count($results['wert']) . " (von " . $expectedPoints . ") Datenpunkte eingetragen.
				</p>
				"; ?>
			</footer>
		</div>
		
		<?php
		$alleW[0] = $results;
	
	}else{	// Normale Anzeige ---
	
	$MODE = "home";
	
	$alleW = [];
	foreach ($alleV as $id => $arr_V) {
		echo "<!-- LineChart: ".$arr_V['verbraucher']." -->";
		$alleW[$id] = db_selVerbrauch($id);
		$alleW[$id]['Steigung'] = calc_Steigerung($alleW[$id]['wert'], False);
		$alleW[$id]['datum_M'] = getMonth($alleW[$id]['datum']);

		// alle aufrücken wegen Chart
		//array_unshift($alleW[$id]['Steigung'], null)
		$alleW[$id]['Steigung'] = rutschAuf($alleW[$id]['Steigung']);
		//array_shift($alleW[$id]['wert']);
		//array_shift($alleW[$id]['datum_M']);
		
	?>	
		<div class="w3-card-2 w3-section w3-margin">
			<header class="w3-container w3-padding w3-black">
				<?php echo $arr_V['verbraucher']." in ".$alleW[$id]['einheit'][0];?>
			</header>
			<div class="w3-container">
				<canvas width="300" height="150" id="LineChart<?php echo $id;?>"></canvas>
			</div>
		</div>
	
	<?php
	}
	// Ende 'else'
	}
	?>
	</div>

	<div id="add" class="w3-modal" style="display: none;">
	<div class="w3-modal-content w3-card-2 w3-animate-bottom">
		<header class="w3-container w3-black">
			<span onclick="toggleModal('add', true);" class="w3-button w3-display-topright">&times;</span>
			<h2>Zählerstand ablesen</h2>
		</header>
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<div class="w3-container">
				<p><input type="date" name="datum" class="w3-input"></p>
				<?php
				foreach ($alleV as $id => $verbraucher) {
					echo '<p>';
					echo '<label for="id'.$id.'">'.$verbraucher['verbraucher'].'</label>';
					echo '<input type="number" step="0.1" name="id'.$id.'" class="w3-input">';
					echo '<input type="hidden" name="ids[]" value="'.$id.'">';
					echo '</p>';
				}
				?>
			</div>
			<footer class="w3-container">
				<p><input type="submit" name="eingeben" value="speichern" class="w3-input w3-green"></p>
			</footer>
		</form>
	</div>
	</div>

	<div id="query" class="w3-modal" style="display: none;">
	<div class="w3-modal-content w3-card-2 w3-animate-bottom">
		<header class="w3-container w3-black">
			<span onclick="toggleModal('query', true);" class="w3-button w3-display-topright">&times;</span>
			<h2>Verbrauch abfragen</h2>
		</header>
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<div class="w3-container">
				<p>
					<input type="radio" name="query_type" value="jahr" required="required">
					<label>Nach Jahr:</label>
					<input type="number" class="w3-input" name="jahr">
				</p>
				<p>
					<input type="radio" name="query_type" value="zeit" required="required">
					<label>Nach eigener Zeitspanne:</label>
					<input type="date" class="w3-input" name="start_punkt">
					<input type="date" class="w3-input" name="end_punkt">
				
				</p>
				<p>
				<select name="verbraucher" class="w3-input">
					<?php
					foreach ($alleV as $id => $verbraucher) {
						echo '<option value="'. $id .'">' . $verbraucher['verbraucher'] . '</option>';
					}
					?>
				</select>
				</p>
			</div>
			<footer class="w3-container">
				<p><input type="submit" name="query" value="suchen" class="w3-input w3-green"></p>
			</footer>
		</form>
	</div>
	</div>


	<?php if ($MODE == "home") { ?>

	<footer class="w3-margin-top">
		<button class="w3-input w3-blue w3-xlarge" onclick="toggleModal('add')">Zählerstand eintragen</button>
	</footer>

	<?php } ?>


	<!-- Chart.js -->
	<script type="text/javascript" src="lib/functions.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.min.js"></script>

	<script type="text/javascript">
		var ctx, lineKoordinaten, lineKoordinaten2, lineLabels;

<?php 	foreach ($alleW as $id => $arr_W) {		?>
			ctx = document.getElementById("LineChart<?php echo $id;?>").getContext("2d");
			lineKoordinaten = <?php echo json_encode($arr_W['wert']); ?>;
			lineKoordinaten2 = <?php echo json_encode($arr_W['Steigung']); ?>;
			lineLabels = <?php echo json_encode($arr_W['datum_M']); ?>;
			vName = <?php echo json_encode($arr_W['verbraucher'][0]); ?>;
			drawLineChart(ctx, lineKoordinaten, lineKoordinaten2, vName, lineLabels);

<?php 	} ?>
		
		ctx = lineKoordinaten = lineKoordinaten2 = lineLabels = false;

	</script>

</body>