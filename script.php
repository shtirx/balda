<?php
	include("file.php");
	include("forecast.php");
	
	if(!file_exists("sendEmailflag.txt"))
		writeFile("sendEmailflag.txt", "0\r\n","w");
	$sendEmail = rNumFile("sendEmailflag.txt",1);
	if(false)//(date("G") >= 10 && $sendEmail == 0)
	{
		$BMP085 = file("BMP085/BMP085_".date("n")."_".date("y").".txt");
		$DHT21 = file("DHT21/DHT21_".date("n")."_".date("y").".txt");
		$countBMP085=count($BMP085);
		$countDHT21=count($DHT21);
		$DHT21 = file("DHT21/DHT21_".date("n")."_".date("y").".txt");
		$message = "На текущее время(".date("F d Y H:i").") погода такова: температура равна ".trim($DHT21[$countDHT21-4])." C, влажность ".trim($DHT21[$countDHT21-3])."%, давление ".trim($BMP085[$countBMP085-2])." мм. рт. ст.(".trendPressure($BMP085[$countBMP085-2],timeData($BMP085, time() + 60*60, 3, 2, 2)).")";
		$message = wordwrap($message, 70, "\r\n");
		if(mail('shtirx@gmail.com', 'Weather', translit($message)))
			writeFile("sendEmailflag.txt", "1\r\n","w");
	} else if(date("G") < 10 && $sendEmail == 1) {
		writeFile("sendEmailflag.txt", "0\r\n","w");
	}

	$mysqli = mysqli_connect("127.0.0.1","root","","balda");
	$res = mysqli_query($mysqli, "SELECT type FROM bot WHERE id = 1");
	$status = mysqli_fetch_array($res)[0];
	$res = mysqli_query($mysqli, "SELECT * FROM bot WHERE id <> 1 AND type>=0 ORDER BY type ASC, id ASC");
	$ArrBot = mysqli_fetch_all($res, MYSQLI_NUM);
	//print_r($ArrBot);
	if(mysqli_num_rows($res)>0 && $status == 0 && $ArrBot[0][2] <= 3 && $ArrBot[0][2] >= 0)// && $ArrBot[0][2] == 0)
	{	
		mysqli_query($mysqli,"UPDATE `bot` SET `type`=1 WHERE id = 1");
		$numOfGame = $ArrBot[0][1];
		//thread_include('bot.php?num=${numOfGame}');
		//system("php http://127.0.0.1:8080/bot.php?num=${numOfGame} &");
		$cycleCoef = (float)($ArrBot[0][2]/3);
		exec_script("http://127.0.0.1:8080/bot.php",array('num' => "${numOfGame}",'cycleCoef' => "${cycleCoef}"));
		//$req = "http://127.0.0.1:8080/bot.php?num=${numOfGame}";
		//file_get_contents($req);
	}/* else if($ArrBot[0][2] > 3) {
		$numOfGame = $ArrBot[0][1];
		$res = mysqli_query($mysqli,"SELECT name FROM listOfGames WHERE id = ${numOfGame}");
		$nameOfGame = mysqli_fetch_array($res)[0];
		if(mail('shtirx@gmail.com', 'balda', translit("Превышено колличество попыток хода для $nameOfGame")))
			mysqli_query($mysqli,"UPDATE `bot` SET `type`=-1 WHERE num = $numOfGame");
	}*/
	