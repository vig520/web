<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Běžecké statistiky</title>
    <link rel="stylesheet" href="running.css">
  </head>
  <body>  

<div id="kontejner">
<div id="hlavicka">
<?php
setlocale(LC_TIME, "cs_CZ.UTF-8");
# Pripojeni k databazi

$link = mysqli_connect("localhost","tomas","simeck","dbRuns") or die("Error - nelze se pripojit k db " . mysqli_error($link));
mysqli_set_charset($link, "utf8");
$PREQUERY = "SET lc_time_names = 'cs_CZ';";
$link->query($PREQUERY);

# Nacteni statistickych dat
$STATRESULT = $link->query("SELECT Nazev, Hodnota1 FROM tblStats");
while ($row = mysqli_fetch_array($STATRESULT))
{
	if ($row[0] == "TotalDistance") {$TotalDistance = $row[1]; continue;}
	if ($row[0] == "PocetBehu") {$PocetBehu = $row[1]; continue;}
	if ($row[0] == "DobaInSec") {$DobaInSec = $row[1]; continue;}
	if ($row[0] == "LastRun") {$LastRun = $row[1]; continue;}
	if ($row[0] == "Home") {$Home = $row[1]; continue;}
	if ($row[0] == "ThisMonthMileage") {$ThisMonthMileage = $row[1]; continue;}
	if ($row[0] == "LastMonthMileage") {$LastMonthMileage = $row[1]; continue;}
	if ($row[0] == "DniPoSobe") {$DniPoSobe = $row[1]; continue;}
	if ($row[0] == "SerieKonci") {$SerieKonci = $row[1]; continue;}
	if ($row[0] == "SerieZacina") {$SerieZacina = $row[1]; continue;}
	if ($row[0] == "PrvniBehSerie") {$PrvniBehSerie = $row[1]; continue;}
	if ($row[0] == "PosledniBehSerie") {$PosledniBehSerie = $row[1]; continue;}
	if ($row[0] == "BehuDoma") {$BehuDoma = $row[1]; continue;}
	if ($row[0] == "BehuJinde") {$BehuJinde = $row[1]; continue;}
	if ($row[0] == "TopMesic") {$TopMesic = $row[1]; continue;}
	if ($row[0] == "TopMesicKM") {$TopMesicKM = $row[1]; continue;}
	if ($row[0] == "ThisMonthName") {$ThisMonthName = $row[1]; continue;}	
	if ($row[0] == "LastMonthName") {$LastMonthName = $row[1]; continue;}
} 

$DNU = $DobaInSec / 86400;
$VTERINPODNECH = $DobaInSec % 86400;
$HODIN = $VTERINPODNECH / 3600;
$VTERINPOHODINACH = $VTERINPODNECH % 3600;
$MINUT = $VTERINPOHODINACH / 60;
$VTERIN = $VTERINPOHODINACH % 60;

	#################\
	# Vypocet tempa
	function Pace($RUNTIME, $DISTANCE)
	{
	$PACE = $RUNTIME / $DISTANCE;
	$PM = intval ($PACE/60);
	$SC = $PACE / 60;
	$SC1 = (($SC-$PM)*60);
	$PS = number_format($SC1, 2, ".", ",");
	if ($PS < 10) { $PS = "0".$PS; }
	$DROBNE = explode(".", $PS);
	$DROBNE = substr($DROBNE[0].$DROBNE[1], 0, 2);
	$VYSLEDNETEMPO = $PM.",".$DROBNE;
	return $VYSLEDNETEMPO;
	}

	# Konec vypoctu tempa
	######################

	$TEMPO = Pace($DobaInSec,$TotalDistance);
	?>
	<span style="color: #ffff33;"><b>Statistiky:</b> <?php echo 'Počet běhů: <font color="red">'.number_format($PocetBehu, 0, '.', ' ').'</font>, celkem naběháno <font color="red">'.number_format($TotalDistance, 0, '.', ' ').' </font>kilometrů, celkem na trati<font color="red"> '.number_format($DobaInSec, 0, '.', ' ').' </font>vteřin, což je <font color="red">'.floor($DNU) .' </font>dní, <font color="red">'.floor($HODIN).' </font>hodin, <font color="red">'. floor($MINUT).' </font>minut, <font color="red">'.$VTERIN.' </font>vteřin, průměrné tempo: <font color="red">'.$TEMPO.'</font> min/km'; 
	?>

	     </span>
	    <hr style="width: 100%; height: 2pt; color: yellow; margin-left: auto; margin-right: auto;">
	</div> 
	<div id="obsah">
 <table class="hornitabulka">
              <tbody>
          <tr>
                <th colspan=3> Různé odkazy
	        </th>
		<tr> 
		<td> <a href="list.php?s=ALL">Shoe tracker</a></td>
<?php
echo "		<td> <a href=\"rundetail.php?run=".number_format($LastRun, 0, '.', '')."\">Poslední běh </a></td> ";
?>
		<td> Poslední měsíc </td>
	</tr>
             </tbody>
            </table>
	<table class="tabulkyvedlesebe">
	      <tbody>
	  <tr>
		<th>Běhy dle roku
		  </th>
		</tr>
	<?php

mysqli_data_seek($STATRESULT, 0);
$ROKY=array();
while ($row = mysqli_fetch_array($STATRESULT))
{
		if (strpos($row[0], 'BehuRoku') !== false) {
		$ROK = substr($row[0], -4, 4);
		array_push($ROKY,$ROK);
	   	echo "       <tr>";
	   	echo "<td><a href=\"list.php?y=".$ROK."\">". $ROK. "</a> (".$row[1].")<br>";
	   	echo "      </td>";
	   	echo "    </tr>"; 
		}

	}
	echo "      </tbody>";
	echo "    </table>";
	$POUT = ROUND($BehuJinde / (($BehuDoma + $BehuJinde) / 100), 2); 
	?>

	<table class="tabulkyvedlesebe">
	      <tbody>
	  <tr>
		<th>Statistiky
		  </th>
		</tr>
	<?php
		echo "<tr><td>";
		echo "Za tento měsíc (".strftime('%B', mktime(0,0,0,number_format($ThisMonthName, 0, '.', ''))).") naběháno ".$ThisMonthMileage."km";
		echo "</td></tr>";
		echo "<tr><td>";
		echo "Za minulý měsíc (".strftime('%B', mktime(0,0,0,number_format($LastMonthName, 0, '.', ''))).") naběháno ".$LastMonthMileage."km";
		echo "</td></tr>";
		echo "<tr><td>";
		echo "Nejvíce běhacích dnů po sobě: ".number_format($DniPoSobe, 0, '.', '');
		echo "</td></tr>";
		echo "<tr><td>";
		echo "od <a href=\"rundetail.php?run=".$PrvniBehSerie."\">".$SerieZacina."</a> do <a href=\"rundetail.php?run=".$PosledniBehSerie."\">".$SerieKonci."</a>";
        	echo "</td></tr>";
		echo "<tr><td>";
                echo 100-$POUT."% běhů doma, ".$POUT."% běhů jinde";
        	echo "</td></tr>";
		echo "<tr><td>";
                echo "Nejvíc měsíčně: ".ROUND($TopMesicKM, 1)." km za ".$TopMesic;
                echo "</td></tr>";

?>
 </tbody>
    </table>
<table class="tabulkyvedlesebe" style="width:50%">
<tbody>
<tr> <th colspan="9"> Statistiky dle let </th> </tr>

<?php

echo "<tr><td style=\"width:20%\"></td>";


mysqli_data_seek($STATRESULT, 0);
while ($row = mysqli_fetch_array($STATRESULT)) {
	if (strpos($row[0], 'BehuRoku') !== false) {
                $ROK = substr($row[0], -4, 4);
		echo "<td style=\"width:10%\"> ".$ROK."</td>";
}
}

echo "</tr><tr><td style=\"width:20%\">Celkem kilometrů</td>";

mysqli_data_seek($STATRESULT, 0);
while ($row = mysqli_fetch_array($STATRESULT)) {
        if (strpos($row[0], '.Distance') !== false) {
                $ROK = substr($row[0], 0, 4);
		echo "<td style=\"width:10%\"> ".$row[1]."</td>";
	}
}

echo "</tr><tr><td style=\"width:20%\">Celkem hodin</td>";

mysqli_data_seek($STATRESULT, 0);
while ($row = mysqli_fetch_array($STATRESULT)) {
        if (strpos($row[0], '.Hours') !== false) {
                $ROK = substr($row[0], 0, 4);
                echo "<td style=\"width:10%\"> ".$row[1]."</td>";
        }
}

echo "</tr><tr><td style=\"width:20%\">Průměrná délka běhu</td>";

mysqli_data_seek($STATRESULT, 0);
while ($row = mysqli_fetch_array($STATRESULT)) {
        if (strpos($row[0], '.AvgDistance') !== false) {
                $ROK = substr($row[0], 0, 4);
                echo "<td style=\"width:10%\"> ".$row[1]."</td>";
        }
}

echo "</tr><tr><td style=\"width:20%\">Průměrné tempo</td>";

for ($x=0; $x < count($ROKY); $x++) {
	mysqli_data_seek($STATRESULT, 0);
	while ($row = mysqli_fetch_array($STATRESULT)) {
		if ($row[0] == $ROKY[$x].".Distance") $DistanceZaRok = $row[1];
		if ($row[0] == $ROKY[$x].".SecForPace")  {
			$TEMPO = Pace($row[1], $DistanceZaRok);
#  Cast behu roku 2010 byla bez GPS dat, cili manualni korekce"
			if ($ROKY[$x] == "2010") $TEMPO = "5,25";
#  Konec manualni korekce
			}

}
echo "<td style=\"width:10%\"> ".$TEMPO."</td>";
}


echo "</tr>";

?>
</tbody> </table>

<table class="tabulkadole">
      <tbody>
        <tr>  
	<th>Běhy dle místa
          </th>
        </tr>
<?php
mysqli_data_seek($STATRESULT, 0);
while ($row = mysqli_fetch_array($STATRESULT)) {
	if (strpos($row[0], 'Zeme.') !== false)	 {
		$DruhaTecka = strpos($row[0], '.', 5);
		$Zeme = substr($row[0], 5, $DruhaTecka -5);
		$CountryID = substr($row[0], $DruhaTecka+1);
		echo "       <tr>";
		if ($CountryID == "1") { # DOMA section
			echo "<td><a href=\"list.php?c=".$CountryID."\">". $Zeme."</a> (".$row[1]."), z toho <a href=\"list.php?c=HOME\">doma</a> ".$Home;
			echo "</td>";
			echo "</tr>";		
		} 
else {
			echo "<td><a href=\"list.php?c=".$CountryID."\">". $Zeme."</a> (".$row[1].")";
   echo "      </td>";
   echo "    </tr>";
		
		}
		
	}	

}

?>

     </tbody>
    </table>
</div> </div>
 </body>
</html>
