<?php 
include("file.php");
include("forecast.php");
date_default_timezone_set('Europe/Minsk');
session_set_cookie_params(10000);
//setcookie("TestCookie", '1998nikita', time()+3600*24*7);
session_start();
if((!isset($_SESSION['access']) || $_SESSION['access']!=true) && $_COOKIE["TestCookie"] != '1998nikita'){
header("location:index.php");
}
else{
//echo $_COOKIE["TestCookie"]; 
?>
<html>
	<head>
	</head>
<body>
<?php
	function getSslPage($url) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($ch, CURLOPT_HEADER, false);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_REFERER, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $result = curl_exec($ch);
	    curl_close($ch);
	    return $result;
	}
	$url = "https://thepiratebay.cr/search/timeless.S01E12";
	$content = getSslPage($url);
	//echo $content1;
	$NameSeries=strpos($content, "Timeless");
	//echo $NameSeries."<br>";
	//echo substr($content, $NameSeries, 100)."<br>";
	$posMagnet= strpos($content, "/torrent",$NameSeries-50);
	//echo $posMagnet."<br>";
	$posMagnetfin= strpos($content, "class",$NameSeries)-2;
	$link=substr($content, $posMagnet, $posMagnetfin-$posMagnet);
	$content = getSslPage("https://thepiratebay.cr/".$link);
	//echo $content;
	$posMagnet = strpos($content, "Hash")+19;
	$posMagnetfin= strpos($content, "</dl>",$posMagnet);
	$link=substr($content, $posMagnet, $posMagnetfin-$posMagnet);
	$link=mb_convert_case($link, MB_CASE_LOWER, "UTF-8");
	if(!file_exists("magnet.txt"))
		writeFile("magnet.txt", "", 'w');
	writeFile("magnet.txt", "magnet:?xt=urn:btih:".trim($link), 'w');


	$url = 'http://94.19.250.166:8090/transmission/web/';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$content = curl_exec($curl);
	curl_close($curl);
	//$content = file_get_contents("http://94.19.250.166:8090/transmission/web/");
	//writeFile("magnet.txt", $content, 'w');
?>
</body>
</html>
<?php } ?>