<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Detail běhu</title>
    <link rel="stylesheet" href="running.css">
    <style>
      html, body, #map-canvas {
        height: 80%;
        width: 80%;
        margin: 5px;
        padding: 5px
      }
    </style>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
    <script>
    function initialize() {
    var mapOptions = {
    zoom: 12,
    center: new google.maps.LatLng<?php
extract($_GET);
$dbh = mysqli_connect("localhost","tomas","simeck","dbRuns") or die("Error - nelze se pripojit k db " . mysqli_error($dbh));

//switch to correct database

    //Query the user for start and ending location. Store locations in variables
    $query = mysqli_query($dbh,"select Latitude, Longitude FROM tblWaypoints where BehID = $run LIMIT 1;");

$row = mysqli_fetch_assoc($query);
$LAT = $row["Latitude"];
$LON = $row["Longitude"];

echo '('.$LAT.','.$LON.'),';

?>
    mapTypeId: google.maps.MapTypeId.TERRAIN
    };


    var map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);
 var flightPlanCoordinates = [
    <?php

$query = mysqli_query($dbh,"SELECT Latitude, Longitude FROM tblWaypoints WHERE BehID = $run;");
    while($row = mysqli_fetch_assoc($query)){
        $lat = $row['Latitude'];
        $lon = $row['Longitude'];
        echo 'new google.maps.LatLng('.$lat.', '.$lon.'),';
    }
    ?>

    ];

    var flightPath = new google.maps.Polyline({
        path: flightPlanCoordinates,
        geodesic: true,
        strokeColor: '#FF0000',
        strokeOpacity: 1.0,
        strokeWeight:5
    });
    flightPath.setMap(map);
 }
    google.maps.event.addDomListener(window, 'load', initialize);
    </script> 
 </head>

  <body style="background-color: black;" link="#ffff33" alink="#ffff33" vlink="#ffcc00"> 
<?php
$link = mysqli_connect("localhost","tomas","simeck","dbRuns") or die("Error - nelze se pripojit k db " . mysqli_error($link));
mysqli_set_charset($link, "utf8");

# Pridavani bot k behu
if (isset($addShoes)) {
	$ADDSHOESTORUN="UPDATE `tblRuns` SET `Shoes`=$addShoes WHERE  `Primary`=$run;";
	if ($link->query($ADDSHOESTORUN) === FALSE) { 
		echo "Problem s pridavanim bot!"; }
}
# Konec pridavani bot k behu

$QUERY = "select  DATE_FORMAT((convert_tz(Date, 'UTC','CET')), '%d. %m. %Y, %H:%i') AS Datum, ROUND(Distance/1000,2) AS Distance, SEC_TO_TIME(Duration) AS Time, Duration, StartAddress, FinishAddress, AvgHeartRate, Shoes from tblRuns WHERE tblRuns.`Primary` = $run;";

$DOTAZ = $link->query($QUERY);
$RESULT = mysqli_fetch_array($DOTAZ);
#################\
# Vypocet tempa
$PACE = $RESULT["Duration"] / $RESULT["Distance"];
$PM = intval ($PACE/60);
$SC = $PACE / 60;
$SC1 = (($SC-$PM)*60);
$PS = number_format($SC1, 2, ".", ",");
if ($PS < 10) { $PS = "0".$PS; }
$DROBNE = explode(".", $PS);
$DROBNE = substr($DROBNE[0].$DROBNE[1], 0, 2);
# Konec vypoctu tempa
######################


if (empty($_GET)) {$TEXT="Nic k zobrazeni";}
        elseif ((isset($run))) {
        # Zde prikazy pro ISSET ROK, MESIC a ZEME
       	echo "<h1>Výpis běhu z ".$RESULT["Datum"]."</h1>";
	echo "<hr style=\"width: 100%; height: 2pt; color: yellow; margin-left: auto; margin-right: auto;\">";
	echo "<table style=\"width:80%\"><tbody>";
	echo "<col width=\"30%\"><col width=\"70%\">";
	echo "<tr><th class=\"hlavickatabulky\"><a href=\"index.php\">Zpět na hlavní stranu</a></th><th>";
	echo "Podrobnosti běhu";
	echo "</th></tr><tr><td>";
	echo "Datum a čas</td><td>";
	echo $RESULT["Datum"]."</td></tr>";
	echo "<tr><td>Vzdálenost</td><td>";
	echo $RESULT["Distance"]." km</td></tr>";
	echo "<tr><td>Čas</td><td>";
	echo $RESULT["Time"]."</td></tr>";
	echo "<tr><td>Tempo</td><td>";
        echo $PM.",".$DROBNE." min/km</td></tr>";	
	if ($RESULT["AvgHeartRate"]) {
                echo "<tr><td>Průměrná tepovka</td><td>";
                echo $RESULT["AvgHeartRate"]."</td></tr>";
        } # Konec AvgHeartRate
	echo "<tr><td>Boty</td><td>";
	if ($RESULT["Shoes"]) {
		$BOTA = $RESULT["Shoes"];
		$QUERY2 = "select tblShoes.`Primary` AS ID, Shoes from tblShoes where tblShoes.`Primary` = $BOTA";
		$DOTAZ = $link->query($QUERY2);
		$RESULT2 = mysqli_fetch_array($DOTAZ);			
		echo "<a href=\"list.php?s=".$RESULT2["ID"]."\">".$RESULT2["Shoes"]."</a></td></tr>"; }
	else {
        	echo "<form action=\"rundetail.php\" method=\"GET\">";
		echo "<input type=\"hidden\" name=\"run\" value=\"".$run."\"\>";
		echo "<select name=\"addShoes\">";
		echo "<option value=\"\" selected>...</option>";
		$QUERY2 = "select tblShoes.`Primary` AS ID, Shoes from tblShoes where Active = 1;";	
		$DOTAZ = $link->query($QUERY2);
                
		while ($RESULT2 = mysqli_fetch_array($DOTAZ)) {
			echo "<option value=\"".$RESULT2["ID"]."\">".$RESULT2["Shoes"]."</option>";
		} #End of while
		echo "</select><input type=\"Submit\" value=\"Odeslat\">";
		echo "</form>";
	} # Konec else Shoes
	if ($RESULT["StartAddress"]) {

		echo "<tr><td>Začátek běhu</td><td>";
       		echo $RESULT["StartAddress"]."</td></tr>";
		echo "<tr><td>Konec běhu</td><td>";
        	echo $RESULT["FinishAddress"]."</td></tr>";
	} # Konec IF GPS data
	else {
		echo "<tr><td colspan = 2 >Nejsou k dispozici GPS data.</td></tr>";
	} # End of else
	echo "</tbody></table>";
	} 

?>
<div id="map-canvas"></div>
</body>
</html>
