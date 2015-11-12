<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Seznam běhů dle kritérií</title>
    <link rel="stylesheet" href="running.css">
  </head>

  <body> 
<?php
extract($_GET);
$link = mysqli_connect("localhost","tomas","simeck","dbRuns") or die("Error - nelze se pripojit k db " . mysqli_error($link));
mysqli_set_charset($link, "utf8");

if (empty($_GET)) {$TEXT="Nic k zobrazeni";}
        elseif ((isset($y)) && (isset($m)) && (isset($c))) {
        # Zde prikazy pro ISSET ROK, MESIC a ZEME
		if ($c == "HOME") { # Pokud resime domaci behy
                      	$QUERY = "select tblRuns.`Primary` AS Beh, DATE_FORMAT(convert_tz(tblRuns.Date, 'UTC','CET'), '%d. %m. %Y, %H:%i') AS Datum, ROUND (Distance/1000, 1) AS Distance from tblRuns where YEAR(Date) = $y AND MONTH(Date) = $m and Home = 1;";  
			$DOTAZ = $link->query($QUERY);
                        echo "<h1>Výpis domácích běhů z ".$m." / ".$y."</h1>";
                } # Konec domacich behu
                else {
		$PREQUERY = "select Country from tblCountries where tblCountries.`Primary` = $c;";
		$DOTAZ = $link->query($PREQUERY);
		$RESULT = mysqli_fetch_array($DOTAZ);
		$QUERY = "select tblRuns.`Primary` AS Beh, DATE_FORMAT(convert_tz(tblRuns.Date, 'UTC','CET'), '%d. %m. %Y, %H:%i') AS Datum,  DATE_FORMAT(convert_tz(tblRuns.Date, 'UTC','CET'), '%Y%m%d%H%i') AS DSORT, ROUND (Distance/1000, 1) AS Distance from tblRuns, tblCountries where YEAR(Date) = $y AND MONTH(Date) = $m and tblCountries.Country = tblRuns.StartCountry and tblCountries.`Primary` = $c ORDER BY DSORT;";
		$DOTAZ = $link->query($QUERY);
		echo "<h1>Výpis běhů za ".$m." / ".$y." v lokalitě ".$RESULT["Country"]." </h1>";	
		} #End of ELSE (jine nez domaci behy

	} # Konec pro ISSET ROK, Mesic a ZEME

	elseif ((isset($y)) && (isset($c))) {
        # Zde prikazy pro ISSET ROK a ZEME
        
	} # Konec pro ISSET ROK a ZEME
	elseif ((isset($y)) && (isset($m))) {
        # Zde prikazy pro ISSET ROK a ZEME
         echo "<h1>Výpis běhů za ".$m." / ".$y." </h1>";
	$QUERY = "select tblRuns.`Primary` AS Beh, DATE_FORMAT(convert_tz(Date, 'UTC','CET'), '%d. %m. %Y, %H:%i') AS Datum, DATE_FORMAT(convert_tz(tblRuns.Date, 'UTC','CET'), '%Y%m%d%H%i') AS DSORT, ROUND (Distance/1000, 1) AS Distance from tblRuns where YEAR(Date) = $y AND MONTH(Date) = $m ORDER BY DSORT;";
	$DOTAZ = $link->query($QUERY);
        } # Konec pro ISSET ROK a MESIC
        elseif (isset($y)) {
        # Zde bude kod pro ISSET ROK
	echo "<h1>Výpis běhů za rok ".$y." </h1>";
	$PREQUERY = "SET lc_time_names = 'cs_CZ';";
	$QUERY = "select MONTHNAME(Date) AS JMesic, MONTH(Date) AS Mesic, COUNT(Date) AS Mnozstvi from tblRuns where YEAR (Date)= $y group by Mesic;";
	$link->query($PREQUERY);
	$DOTAZ = $link->query($QUERY);	
	} # Konec pro ISSET ROK
 	elseif ((isset($t)) && (isset($t))) {
	 # Zde bude kod pro ISSET MESTO
	if ($t == "X") { 
		$t = "0"; 
		$PREQUERY = "select Country AS City from tblCountries where tblCountries.`Primary` = $c;";}
	else {  
		$PREQUERY = "select City from tblCities where tblCities.`Primary` = $t;";
        }
	$DOTAZ = $link->query($PREQUERY);
        $RESULT = mysqli_fetch_array($DOTAZ);
        echo "<h1>Výpis běhů v lokalitě ".$RESULT["City"]." </h1>";
	if ($t == "0") {
		$QUERY = "select tblRuns.`Primary` AS Beh, convert_tz(tblRuns.Date, 'UTC','CET') AS Datum, ROUND (Distance/1000, 1) AS Distance, tblRuns.StartCity from tblRuns, tblCountries where StartCity=\"0\" and StartCountry = tblCountries.Country and tblCountries.`Primary` = $c;";
	}
	else {
		$QUERY = "select tblRuns.`Primary` AS Beh, convert_tz(tblRuns.Date, 'UTC','CET') AS Datum, DATE_FORMAT(convert_tz(tblRuns.Date, 'UTC','CET'), '%Y%m%d%H%i') AS DSORT, ROUND (Distance/1000, 1) AS Distance, tblRuns.StartCity from tblRuns, tblCities where tblRuns.StartCity = tblCities.City AND tblCities.`Primary` = $t ORDER BY DSORT;"; }
		$DOTAZ = $link->query($QUERY);	
		} # Konec kodu pro ISSET Mesto
	elseif (isset($c)) {
        # Zde bude kod pro ISSET ZEME
		if ($c == "HOME") { # Pokud resime domaci behy
                        $QUERY = "select MONTH(tblRuns.Date) AS Mesic, YEAR(tblRuns.Date) AS Rok, DATE_FORMAT(tblRuns.Date, '%m / %Y') AS Datum, COUNT(Date) AS Mnozstvi from tblRuns where Home = 1 group by Datum order by Rok, Mesic;";
                        $DOTAZ = $link->query($QUERY);
                        echo "<h1>Výpis domácích běhů </h1>";
                } # Konec domacich behu
                else {
        		$PREQUERY = "select Country from tblCountries where tblCountries.`Primary` = $c;";
       	 		$DOTAZ = $link->query($PREQUERY);
        		$RESULT = mysqli_fetch_array($DOTAZ);
        		echo "<h1>Výpis běhů v lokalitě ".$RESULT["Country"]." </h1>";
       	 		$QUERY = "select MONTH(tblRuns.Date) AS Mesic, YEAR(tblRuns.Date) AS Rok, DATE_FORMAT(tblRuns.Date, '%m / %Y') AS Datum, COUNT(Date) AS Mnozstvi, tblCountries.Country AS Country from tblRuns, tblCountries where tblCountries.`Primary` = $c AND tblCountries.Country = tblRuns.StartCountry group by Datum order by Rok, Mesic;";
        		$QUERY2 = "select distinct tblRuns.StartCity AS StartCity, tblCities.`Primary` AS Id, Count(Date) as Pocet from tblRuns, tblCountries, tblCities where tblRuns.StartCountry = tblCountries.Country and tblCountries.`Primary` = $c AND tblCities.City = tblRuns.StartCity group by StartCity;";
        		$DOTAZ = $link->query($QUERY);
		} # end of else
        	} # Konec kodu pro ISSET ZEME
	elseif (isset($s))	{
		if ($s == "ALL") {
			echo "<h1>Shoe tracker </h1>";
			$PREQUERY = "select tblShoes.Shoes AS Bota, ROUND(SUM(tblRuns.Distance/1000)+tblShoes.Mileage, 1) AS Nabehano, tblRuns.Shoes AS Id from tblShoes, tblRuns where tblRuns.Shoes = tblShoes.`Primary` AND DATE(tblRuns.Date) > '2015-02-22' AND tblShoes.Active = 1 group by tblShoes.Shoes order by Nabehano DESC;";
			$DOTAZ = $link->query($PREQUERY);
		} # End of ALL SHOES
		else { 
			$PREQUERY = "select Shoes from tblShoes where tblShoes.`Primary` = $s;";
			$DOTAZ = $link->query($PREQUERY);
                	$RESULT = mysqli_fetch_array($DOTAZ);
			echo "<h1>Výpis běhů v botách  ".$RESULT["Shoes"]." </h1>";
			$QUERY2 = "select tblRuns.`Primary` AS Beh, DATE_FORMAT(convert_tz(Date, 'UTC','CET'), '%d. %m. %Y, %H:%i') AS Datum, ROUND (Distance/1000, 1) AS Distance from tblRuns where Shoes = $s;";
			$DOTAZ = $link->query($QUERY2);
} #End of else
} #Konec ELSEIF SHOES


echo "<hr style=\"width: 100%; height: 2pt; color: yellow; margin-left: auto; margin-right: auto;\">";

if (empty($_GET)) {$TEXT="Nic k zobrazeni";}
       	elseif ((isset($y)) && (isset($c)) && (isset($m))) {
       	# Zde prikazy pro ISSET ROK a ZEME
		echo "<div class=\"centered\">";
		echo "<table class=\"tabulkavypis\"><tbody> ";
                echo "<tr><th>Běhy dle data</th></tr>";
		while ($RESULT = mysqli_fetch_array($DOTAZ)) {
                        echo "<tr><td><a href=\"rundetail.php?run=".$RESULT["Beh"]."\"> ".$RESULT["Datum"]."</a>, ".$RESULT["Distance"]." km</td></tr>";   
		} # End of while
       	} # Konec pro ISSET ROK a ZEME
       	elseif ((isset($y)) && (isset($m)) || (isset($t))) {
        # Zde prikazy pro ISSET ROK a MESIC
		echo "<div class=\"centered\">";
		echo "<table class=\"tabulkavypis\"><tbody> ";
		echo "<tr><th>Detailní výpis</th></tr>";
		while ($RESULT = mysqli_fetch_array($DOTAZ)) {
			echo "<tr><td><a href=\"rundetail.php?run=".$RESULT["Beh"]."\"> ".$RESULT["Datum"]."</a>, ".$RESULT["Distance"]." km</td></tr>";
		} # end of while
        } # Konec pro ISSET ROK a MESIC

	elseif (isset($y)) {
       	# Zde bude kod pro ISSET ROK
		echo "<div class=\"centered\">";
		echo "<table class=\"tabulkavypis\"><tbody> ";
		echo "<tr><th>Běhy dle měsíců</th></tr>";
		while ($RESULT = mysqli_fetch_array($DOTAZ)) {       	
			
			echo "<tr><td><a href=\"list.php?y=".$y."&m=".$RESULT["Mesic"]."\"> ".$RESULT["JMesic"].":</a> (".$RESULT["Mnozstvi"].")</td></tr>";
		} # End of WHILE
		echo "</tbody></table>";
	} # Konec pro ISSET ROK
       	elseif (isset($c)) {
       	# Zde bude kod pro ISSET ZEME
		echo "<div class=\"centered\">";
		echo "<table class=\"tabulkavypis\"><tbody> ";
		echo "<tr><th>Běhy dle data</th></tr>";
                while ($RESULT = mysqli_fetch_array($DOTAZ)) {
			echo "<tr><td><a href=\"list.php?y=".$RESULT["Rok"]."&m=".$RESULT["Mesic"]."&c=".$c."\"> ".$RESULT["Datum"]."</a> (".$RESULT["Mnozstvi"].")</td></tr>";
		} #end of while	
		echo "</tbody></table> </div>";	
		if ($c != "HOME") {
			$DOTAZ = $link->query($QUERY2);
			echo "<div class=\"centered\">";
			echo "<table class=\"tabulkavypis\"><tbody> ";
			echo "<tr><th>Běhy dle místa</th></tr>";
			while ($RESULT = mysqli_fetch_array($DOTAZ)) {
				echo "<tr><td><a href=\"list.php?t=".$RESULT["Id"]."\"> ".$RESULT["StartCity"]."</a> (".$RESULT["Pocet"].")</td></tr>";		
		} # end of while no. 2
		$QUERY3 = "select count(Date) as Pocet from tblRuns, tblCountries where tblRuns.StartCity = \"0\" AND tblCountries.`Primary` = $c and tblCountries.Country = tblRuns.StartCountry";
		$DOTAZ = $link->query($QUERY3);
		while ($RESULT = mysqli_fetch_array($DOTAZ)) {
			if ($RESULT[0] <> "0") {
				echo "<tr><td><a href=\"list.php?c=".$c."&t=X\">Jiné</a> (".$RESULT["Pocet"].") </td></tr>";
			} # End of if

		} # End of while na vypis "Jine lokality"
		echo "</tbody></table> </div>";
		} # End of if (pokud nevypisujeme domaci behy
       	} # Konec kodu pro ISSET ZEME
	elseif (isset($s)) {
		if ($s == "ALL") {
			echo "<div class=\"centered\">";
                        echo "<table class=\"tabulkavypis\"><tbody> ";
                        echo "<tr><th colspan=2>Kilometráž bot</th></tr>";
			while ($RESULT = mysqli_fetch_array($DOTAZ)) {
				echo "<tr><td><a href=\"list.php?s=".$RESULT["Id"]."\">".$RESULT["Bota"]."</a></td><td>".$RESULT["Nabehano"]." km</td></tr>"; 
			} # End of while
		} #End of ALL SHOES
		else {
			echo "<div class=\"centered\">";
                	echo "<table class=\"tabulkavypis\"><tbody> ";
                	echo "<tr><th>Detailní výpis</th></tr>";
			while ($RESULT = mysqli_fetch_array($DOTAZ)) {
				echo "<tr><td><a href=\"rundetail.php?run=".$RESULT["Beh"]."\"> ".$RESULT["Datum"]."</a>, ".$RESULT["Distance"]." km</td></tr>";	
				} #End of while
			echo "</tbody></table> </div>";
			} # End of else
} # Konec kodu pro ISSET SHOES

?>
</body>
</html>
