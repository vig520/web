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

# Pripojeni k databazi

$link = mysqli_connect("localhost","tomas","simeck","dbRuns") or die("Error - nelze se pripojit k db " . mysqli_error($link));
mysqli_set_charset($link, "utf8");
$PREQUERY = "SET lc_time_names = 'cs_CZ';";
$link->query($PREQUERY);

# Zjisteni celkove vzdalenosti

$QUERY = "select ROUND(SUM(Distance)/1000, 2) AS TotalDistance, COUNT(tblRuns.`Primary`) AS Behu, ROUND(SUM(Duration)) AS Doba, MAX(tblRuns.`Primary`) AS Top from tblRuns;";
$DOTAZ = $link->query($QUERY);
$RESULT = mysqli_fetch_array($DOTAZ); 

$DISTANCE = $RESULT["TotalDistance"];
$RUNCOUNT = $RESULT["Behu"];
$RUNTIME = $RESULT["Doba"];
$TOPRUN = $RESULT["Top"];

# Vysledek je $RUNTIME[0]
$DNU = $RUNTIME / 86400;
$VTERINPODNECH = $RUNTIME % 86400;
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

	$TEMPO = Pace($RUNTIME,$DISTANCE);
	$SECS = number_format($RUNTIME, 0, '.', ' ');
	?>
	<span style="color: #ffff33;"><b>Statistiky:</b> <?php echo 'Počet běhů: <font color="red">'.$RUNCOUNT.'</font>, celkem naběháno <font color="red">'.number_format($DISTANCE, 0, '.', ' ').' </font>kilometrů, celkem na trati<font color="red"> '.$SECS.' </font>vteřin, což je <font color="red">'.floor($DNU) .' </font>dní, <font color="red">'.floor($HODIN).' </font>hodin, <font color="red">'. floor($MINUT).' </font>minut, <font color="red">'.$VTERIN.' </font>vteřin, průměrné tempo: <font color="red">'.$TEMPO.'</font> min/km'; 
	?>

	     </span>
	    <hr style="width: 100%; height: 2pt; color: yellow; margin-left: auto; margin-right: auto;">
	</div> 
	<div id="obsah">
	<?php
	$YEARSQ = "select distinct YEAR(convert_tz(Date, 'UTC','CET')) AS Datum, COUNT(*) AS Pocet from tblRuns group by 1;";
	$YEARS1 = $link->query($YEARSQ);

	$PLACESQ = "select tblCountries.`Primary` AS Cislo, tblCountries.Country from tblCountries;";
	$PLACES1 = $link->query($PLACESQ);
	?>
 <table class="hornitabulka">
              <tbody>
          <tr>
                <th colspan=3> Různé odkazy
	        </th>
		<tr> 
		<td> <a href="list.php?s=ALL">Shoe tracker</a></td>
<?php
echo "		<td> <a href=\"rundetail.php?run=".$TOPRUN."\">Poslední běh </a></td> ";
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
	while ($ROKY = mysqli_fetch_array($YEARS1)) {
	   echo "       <tr>";
	   echo "<td><a href=\"list.php?y=".$ROKY["Datum"]."\">". $ROKY["Datum"]. "</a> (".$ROKY["Pocet"].")<br>";
	   echo "      </td>";
	   echo "    </tr>";
	}
	?>
	      </tbody>
	    </table>
	<?php

	$query = "select ROUND(SUM(Distance/1000),2) AS ThisMonthDistance, Monthname(CURDATE()) AS Mesic from tblRuns where Year(convert_tz(Date, 'UTC','CET')) = Year(CURDATE()) and Month(convert_tz(Date, 'UTC','CET')) = Month(CURDATE());"; # Data za tento mesic 
	$DOTAZ1 = $link->query($query);
	$DOTAZ1 = mysqli_fetch_array($DOTAZ1);


	$query = "select ROUND(SUM(Distance/1000),2) AS LastMonthDistance, MONTHNAME(DATE_ADD(CURDATE(), INTERVAL -1 MONTH)) AS MinulyMesic from tblRuns where Year(convert_tz(Date, 'UTC','CET')) = Year(DATE_ADD(CURDATE(), INTERVAL -1 MONTH)) and MONTHNAME(convert_tz(Date, 'UTC','CET')) = MONTHNAME(DATE_ADD(CURDATE(), INTERVAL -1 MONTH));"; #Data za minuly mesic 
	$DOTAZ2 = $link->query($query);
	$DOTAZ2 = mysqli_fetch_array($DOTAZ2);

	$STREAKQ1 = "SET @nextDate = CURRENT_DATE;";
	$STREAKQ2 = "SET @RowNum = 1;"; 
	$STREAKQ3 = "SELECT  @RowNum := IF(@NextDate = Date(tblRuns.Date), @RowNum + 1, 1) AS RowNumber, DATE_FORMAT((tblRuns.Date), '%d. %m. %Y') AS Datum, tblRuns.`Primary`, DATE_ADD(Date(tblRuns.Date), INTERVAL (@RowNum*(-1))+1 DAY) AS StartDate, @NextDate := DATE_ADD(Date(tblRuns.Date), INTERVAL 1 DAY) AS NextDate FROM tblRuns ORDER BY RowNumber DESC LIMIT 1;";

	$STREAK1 = $link->query($STREAKQ1);
	$STREAK2 = $link->query($STREAKQ2);
	$STREAK3 = $link->query($STREAKQ3);
	$STREAK3 = mysqli_fetch_array($STREAK3);

	$STREAKQ4 = "select tblRuns.`Primary`, DATE_FORMAT((tblRuns.Date), '%d. %m. %Y') from tblRuns where Date(tblRuns.Date) = '$STREAK3[3]';";
	$STREAK4 = $link->query($STREAKQ4);
	$STREAK4 = mysqli_fetch_array($STREAK4);
	$PERCENTQ = "select IFNULL(Home, 2) AS Status, COUNT(IFNULL(Home, 2)) AS Pocet from tblRuns where IFNULL(StartAddress, 2) <> 2 group by Home order by Status;";
	$PERCENTR = $link->query($PERCENTQ);
	$i = 0;
	while ($PERCENT = mysqli_fetch_array($PERCENTR))	 {
		$j = $PERCENT["Pocet"];
		$i = $i + $j;
}
	$POUT = ROUND($j / ($i / 100), 2); 
	$query = "select DATE_FORMAT(convert_tz(Date, 'UTC','CET'), '%m/%Y') AS Mesic, SUM(Distance/1000) AS Vzdalenost from tblRuns group by Mesic order by Vzdalenost DESC LIMIT 1;";
	$DOTAZ = $link->query($query);
	$DOTAZ = mysqli_fetch_array($DOTAZ);
	?>

	<table class="tabulkyvedlesebe">
	      <tbody>
	  <tr>
		<th>Statistiky
		  </th>
		</tr>
	<?php
		echo "<tr><td>";
		echo "Za tento měsíc (".$DOTAZ1[1].") naběháno ".$DOTAZ1[0]."km";
		echo "</td></tr>";
		echo "<tr><td>";
		echo "Za minulý měsíc (".$DOTAZ2[1].") naběháno ".$DOTAZ2[0]."km";
		echo "</td></tr>";
		echo "<tr><td>";
		echo "Nejvíce běhacích dnů po sobě: ".$STREAK3[0];
		echo "</td></tr>";
		echo "<tr><td>";
		echo "od <a href=\"rundetail.php?run=".$STREAK4[0]."\">".$STREAK4[1]."</a> do <a href=\"rundetail.php?run=".$STREAK3[2]."\">".$STREAK3[1]."</a>";
        	echo "</td></tr>";
		echo "<tr><td>";
                echo 100-$POUT."% běhů doma, ".$POUT."% běhů jinde";
        	echo "</td></tr>";
		echo "<tr><td>";
                echo "Nejvíc měsíčně: ".ROUND($DOTAZ["Vzdalenost"], 1)." km za ".$DOTAZ["Mesic"];
                echo "</td></tr>";




$STATSQ = "select distinct YEAR(convert_tz(Date, 'UTC','CET')) AS Rok, ROUND(SUM(Distance/1000), 2) AS Distance, ROUND(SUM(Distance/1000)/COUNT(Distance), 2) AS AvgDistance, ROUND(SUM(Duration/3600), 2) AS Hours, SUM(Duration) AS SecForPace from tblRuns group by Rok;";
$STATS1 = $link->query($STATSQ);

$STATSQ2010 = "select distinct YEAR(convert_tz(Date, 'UTC','CET')) AS Rok, ROUND(SUM(Distance/1000), 2) AS Distance, SUM(Duration) AS SecForPace from tblRuns where YEAR(Date) = 2010 AND StartAddress IS NOT NULL;";
$STATS2010 = $link->query($STATSQ2010);
$DOTAZ = mysqli_fetch_array($STATS2010);
?>
 </tbody>
    </table>
<table class="tabulkyvedlesebe" style="width:50%">
<tbody>
<tr> <th colspan="8"> Statistiky dle let </th> </tr>

<?php
$DATA = array();
#$STATS = mysqli_fetch_array($STATS1);
while ($ROW = $STATS1->fetch_assoc())
{
	$DATA[] = $ROW;
}
$colNames = array_keys(reset($DATA));
echo "<tr><td style=\"width:30%\"></td>";
foreach ($DATA as $ROK) 
{
	echo "<td style=\"width:10%\"> ".$ROK["Rok"]."</td>";
}
echo "</tr><tr><td style=\"width:30%\">Celkem kilometrů</td>";
foreach ($DATA as $KM)
{
        echo "<td style=\"width:10%\"> ".$KM["Distance"]."</td>";
}
echo "</tr><tr><td style=\"width:30%\">Celkem hodin</td>";
foreach ($DATA as $HODIN)
{
        echo "<td style=\"width:10%\"> ".$HODIN["Hours"]."</td>";
}
echo "</tr><tr><td style=\"width:30%\">Průměrná délka běhu</td>";
foreach ($DATA as $DELKA)
{
        echo "<td style=\"width:10%\"> ".$DELKA["AvgDistance"]."</td>";
}
echo "</tr><tr><td style=\"width:30%\">Průměrné tempo</td>";
foreach ($DATA as $SEC)
{
        $TEMPO = Pace($SEC["SecForPace"],$SEC["Distance"]);
	if ($SEC["Rok"] == "2010") {
		$TEMPO = Pace($DOTAZ["SecForPace"], $DOTAZ["Distance"]);			
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
while ($PLACES = mysqli_fetch_array($PLACES1)) {
	$COUNTQ = "select count(StartCountry) AS Cislo from tblRuns where tblRuns.StartCountry like \"".$PLACES["Country"]."\";";
$COUNT1 = $link->query($COUNTQ);
	$HOMEQ = "select count(Home) as Home from tblRuns where Home = 1;";
	$HOMEQ = $link->query($HOMEQ);
	$HOME = mysqli_fetch_array($HOMEQ);
	while ($COUNT = mysqli_fetch_array($COUNT1)) {	
   echo "       <tr>";
   
	if ($PLACES["Cislo"] == "1") { # DOMA section 
		echo "<td><a href=\"list.php?c=".$PLACES["Cislo"]."\">". $PLACES["Country"]."</a> (".$COUNT["Cislo"]."), z toho <a href=\"list.php?c=HOME\">doma</a> ".$HOME["Home"];
  		 echo "</td>";
  		 echo "</tr>";
} # end of DOMA section 
else {
	echo "<td><a href=\"list.php?c=".$PLACES["Cislo"]."\">". $PLACES["Country"]."</a> (".$COUNT["Cislo"].")";
   echo "      </td>";
   echo "    </tr>";
} # end of else
}}
?>

     </tbody>
    </table>
</div> </div>
 </body>
</html>
