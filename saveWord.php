<?php
	include("file.php");
	$num=0;
	$flag = 0;
	if (!empty($_GET["num"]))
	{
		//echo $_GET["word"];
		$num=$_GET["num"];
	}
	$mysqli = new mysqli("127.0.0.1", "root","", "balda");
	$nameOfGame = mysqli_query($mysqli,"SELECT name FROM listOfGames WHERE id=$num");
	$nameOfGame = mysqli_fetch_array($nameOfGame)[0];
	$game = mysqli_query($mysqli,"SELECT field FROM $nameOfGame");
	$ArrGame = mysqli_fetch_all($game,MYSQLI_NUM);
	$indexNum=0;
	while($ArrGame[$indexNum][0]<2)
		$indexNum++;
	$numOfPlayers = $ArrGame[$indexNum+1][0];
	$size = $ArrGame[$indexNum][0];
	$idPlayer=count($ArrGame)-1;
	$player = $ArrGame[$idPlayer][0];
	function isWordExist($word,$nameOfGame,$numOfPlayers,$mysqli,$centralWord)
	{
		for($i=0;$i<$numOfPlayers;$i++)
		{
			//$words=file("balda/game$num/Player$i.txt");
			$temp = mysqli_query($mysqli,"SELECT word FROM ${nameOfGame}Player$i WHERE word='$word' OR word='$word\r\n'");
			/*$words = mysqli_fetch_all($temp,MYSQLI_NUM);
			$count = count($words);
			for($j=1;$j<$count;$j++)
			{
				if(strcmp(trim($words[$j][0]),$word) == 0)
					return true;
			}*/
			if(mysqli_num_rows($temp)>0)
				return true;
		}
		//unset($temp);
		//$temp=file("balda/game$num/field.txt");
		if(strcmp(trim($centralWord),$word) == 0)
			return true;
		return false;
	}
	function findNewWord($word)
	{
		$word = iconv('UTF-8', 'CP1251', $word);
		$newWords = file("dictionary/newWords.txt");
		for($i=0;$i<count($newWords);$i++)
		{
			if(strcmp(trim($newWords[$i]),trim($word)) == 0)
				return true;
		}
		return false;
	}
	//$table=file("balda/game$num/field.txt");
	$word;
	if (!empty($_GET["word"]) && $player != -1)
	{	
		$word = $_GET["word"];
		if(!empty($_GET["typeAnsw"]) && $_GET["typeAnsw"] == 1)
			$word = iconv('CP1251', 'UTF-8', $word);
		$word = mb_strtolower($word);
		if(isWordExist($word,$nameOfGame,$numOfPlayers,$mysqli,$ArrGame[(int)($size/2)+$indexNum+2][0]))
		{
			echo 1;
			$flag = 1;
		} else
		{
			//$word = iconv('CP1251', 'UTF-8',$word);
			echo $word."<br>";
			if(mysqli_num_rows(mysqli_query($mysqli,"SELECT word FROM dictionary WHERE word = '$word'")) == 0)
			{
				echo 0;
				$flag=1;
			}
			else
			{	
				if(!empty($_GET["typeAnsw"]) && $_GET["typeAnsw"] == 1)
				{	
					echo 3;
				} else
					echo $word;
				mysqli_query($mysqli,"INSERT INTO ${nameOfGame}Player${player} VALUES(null, '$word')");
				$res = mysqli_query($mysqli,"SELECT count(1) FROM ${nameOfGame}Player${player}");
				$res = mysqli_fetch_all($res, MYSQLI_NUM);
				$countWord = $res[0][0];
			}
		}
			
	}
	
	if($flag == 0)
	{
		for($i=0;$i<$size;$i++)
		{
			for($j=0;$j<$size;$j++)
			{
				if (!empty($_GET["Field$i;$j"]))
				{
					//$table[$i][$j]=iconv('UTF-8', 'CP1251', mb_strtolower($_GET["Field$i;$j"]));
					//writeFile("balda/game$num/updateField.txt","$i;$j;".$table[$i][$j],"w");
					//$row=$ArrGame[$i+$indexNum+2][0];
					$let = $_GET["Field$i;$j"];
					if(!empty($_GET["typeAnsw"]) && $_GET["typeAnsw"] == 1)
						$let = iconv('CP1251', 'UTF-8', $let);
					$row = iconv('UTF-8','CP1251',$ArrGame[$i+$indexNum+2][0]);
					$row[$j]=iconv('UTF-8','CP1251',mb_strtolower($let));
					$row = iconv('CP1251','UTF-8',$row);
					$id=$i+1+$indexNum+2;
					mysqli_query($mysqli,"UPDATE ${nameOfGame} SET field = '$row' WHERE id=$id");
				} 
			}
		}
		/*clearFile("balda/game$num/field.txt");
		for($i=0;$i<count($table);$i++)
		{
			writeFile("balda/game$num/field.txt", $table[$i],"a");
		}*/

		/*$table = file("balda/game$num/Player".($player+1).".txt");
		if(strcmp($word,iconv('CP1251', 'UTF-8',$table[count($table)-1])) != 0)
		{
			echo 3;
			return;
		}*/

		//writeFile("balda/game".$num."/playAudio.txt",1,"w");
		$idP=$idPlayer;
		mysqli_query($mysqli,"UPDATE $nameOfGame SET field='1' WHERE id=$idP");
		$player++;
		if($player == $numOfPlayers)
			$player=0;
		//writeFile("balda/game$num/curPlayer.txt", $player,"w");
		$idPlayer++;
		if(($countWord-1 == (int)($size*($size-1)/$numOfPlayers)) && $player == 0)
			$player = -2;
		mysqli_query($mysqli,"UPDATE ${nameOfGame} SET field = '$player' WHERE id=$idPlayer");
		
		if($player >= 0)
		{
			$res = mysqli_query($mysqli,"SELECT id FROM ${nameOfGame}Player${player} WHERE word='Никита' OR word='Никита\r\n'");
			if(mysqli_num_rows($res) != 0)
				mail('shtirx@gmail.com', 'balda', translit("Ваш ход в ${nameOfGame}"));

			$bot = mysqli_query($mysqli,"SELECT word FROM ${nameOfGame}Player${player} WHERE word='bot' AND id=1");
			if(mysqli_num_rows($bot)>0)
			{	
				/*$req = "http://127.0.0.1:8080/bot.php?num=${num}";
				$page = file_get_contents($req);*/
				mysqli_query($mysqli,"INSERT INTO bot VALUES (null, $num, 0)");
			}
		}
	}
?>