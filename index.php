<?php

require 'lib/globals.php';
require 'lib/database.php';
require 'lib/functions.php';

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
		<h1>Verbraucher im Haus</h1>
	</header>

	<div class="w3-container" id="content">

	<?php
	$alleV = db_selVerbraucher();
	$alleW = [];


	foreach ($alleV as $id => $arr_V) {
		echo "<!-- LineChart: ".$arr_V['verbraucher']." -->";
		$alleW[$id] = db_selVerbrauch($id);
		$alleW[$id]['Steigung'] = calc_Steigerung($alleW[$id]['wert'], False);
		$alleW[$id]['datum_M'] = getMonth($alleW[$id]['datum']);

		/*
		preOut($alleW[$id]['Steigung']);
		preOut($alleW[$id]['datum']);
		preOut($alleW[$id]['datum_M']);
		*/
		// alle aufrücken wegen Chart
		$alleW[$id]['Steigung'] = rutschAuf($alleW[$id]['Steigung']);
		array_shift($alleW[$id]['wert']);
		array_shift($alleW[$id]['datum_M']);
		
	?>	
		<div class="w3-card-2 w3-section w3-margin">
			<div class="w3-container w3-padding w3-black"><?php echo $arr_V['verbraucher']." in ".$alleW[$id]['einheit'][0];?></div>
			<div class="w3-container">
				<canvas width="300" height="150" id="LineChart<?php echo $id;?>"></canvas>
			</div>
		</div>
	
	<?php
	}
	?>

	</div>

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