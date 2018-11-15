<?php
	include("file.php");

	$count=1000;
	$url = "https://www.kinopoisk.ru/film/";
	//while($count<10000)
	{
		$content = getSslPage($url.$count);
		echo $content;
		$posStart=strpos($content, "режиссер");
		$director=substr($content, $posStart1, 100);	
		echo $director."<br>";
	}
?>