<?php
include("passwordLibrary.php");
	//if (!empty($_GET["num"]))
	{
		//echo $_GET["num"];
		$mysqli = new mysqli("127.0.0.1", "root","", "balda");
		$id = trim($_GET["id"]);
		$curTime = time();
		mysqli_query($mysqli, "UPDATE users SET time = $curTime WHERE id=$id");
		//updateStatus();
		if(!empty($_GET["num"]))
		{
			//echo rNumFile("balda/game".$_GET["num"]."/curPlayer.txt");
			$mysqli = new mysqli("127.0.0.1", "root","", "balda");
			$num = $_GET["num"];
			$nameOfGame = mysqli_query($mysqli,"SELECT name FROM listOfGames WHERE id=$num");
			$nameOfGame = mysqli_fetch_array($nameOfGame)[0];
			$game = mysqli_query($mysqli,"SELECT field FROM $nameOfGame");
			$ArrGame = mysqli_fetch_all($game,MYSQLI_NUM);
			echo $ArrGame[count($ArrGame)-1][0];
		}
			
		//echo "/balda/game".$_GET["num"]."/curPlayer.txt";
		if(!empty($_GET["type"]))
		{
			$type=$_GET["type"];
			if($type == 1)
			{
				$status = getStatus();
				$count = count($status);
				for($i=0;$i<$count;$i++)
				{
					if(time() - $status[$i][0] > 61)
						echo "offline since ".date("d M D H:i ", $status[$i][0]).",";
					else
						echo "online,";
				echo $status[$i][1].";";
				}
			}
		}
	}

?>