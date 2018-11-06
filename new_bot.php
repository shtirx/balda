<?php
	include("file.php");
	$numOfGame=(int)($_GET["num"]);
	$cycleCoef = (int)($_POST["cycleCoef"]);
	//writeFile("nameOfGame.txt","name: ${numOfGame}","w");
	/*if(empty($_POST["num"]))
		exit;*/
	$mysqli = /*mysqli_connect("balda.sknt.ru","root","","balda")*/mysqli_connect("127.0.0.1","root","","balda");
	$que = "SELECT name FROM listOfGames WHERE id=$numOfGame";
	echo $que."<br>";
	$nameOfGame = mysqli_query($mysqli,$que);
	echo count($nameOfGame)."<br>";
	$nameOfGame = mysqli_fetch_array($nameOfGame)[0];
	echo $nameOfGame."<br>";
	$game = mysqli_query($mysqli,"SELECT field FROM $nameOfGame");
	$ArrGame = mysqli_fetch_all($game,MYSQLI_NUM);
	$indexNum=0;
	//while($ArrGame[$indexNum][0]<2)
	//	$indexNum++;
	$size = (int)$ArrGame[$indexNum][0];
	//echo "size $size<br>";
	$arrp;
	$finalWords;
	$finalWordsCount=0;
	$recCount=0;
	$lenWord=3;
	$req;
	$numOne=0;
	$cycles;

?>