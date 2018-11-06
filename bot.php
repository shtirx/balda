<?php
	include("file.php");
	$numOfGame=$_POST["num"];
	//$numOfGame=$_GET["num"];
	$cycleCoef = $_POST["cycleCoef"];
	//writeFile("nameOfGame.txt","name: ${numOfGame}","w");
	/*if(empty($_POST["num"]))
		exit;*/
	$mysqli = /*mysqli_connect("balda.sknt.ru","root","","balda")*/mysqli_connect("127.0.0.1","root","","balda");
	$nameOfGame = mysqli_query($mysqli,"SELECT name FROM listOfGames WHERE id=$numOfGame");
	$nameOfGame = mysqli_fetch_array($nameOfGame)[0];
	$game = mysqli_query($mysqli,"SELECT field FROM $nameOfGame");
	$ArrGame = mysqli_fetch_all($game,MYSQLI_NUM);
	$indexNum=0;
	while($ArrGame[$indexNum][0]<2)
		$indexNum++;
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
	//$query="SELECT  word FROM dictionary WHERE 0 ";
	/*function getWord($substr)
	{	
		global $mysqli;
		$len = iconv_strlen($substr)+1;
		
		$res = mysqli_query($mysqli, "SELECT id, word FROM dictionary WHERE POSITION('$substr' IN word) AND CHAR_LENGTH(word)= $len LIMIT 1");
		$arrWord = mysqli_fetch_all($res, MYSQLI_NUM);
		//echo "subword $substr ".$arrWord[0][1]."<br>";
		return $arrWord[0][1];
	}*/
	function isExist($word,$type=1)
	{
		global $finalWords;
		for($i=0;$i<count($finalWords)-$type;$i++)
		{
			if(strcmp($word,$finalWords[$i][0]) == 0)
				return true;
		}
		return false;
	}
	function isCrateWord($ArrWord)
	{
		global $arrp,$size;
		$pos = strpos($ArrWord[0], $ArrWord[5]);
		//echo "$ArrWord[0] $ArrWord[5] pos: $pos<br>";
		if($pos>0)
		{	
			$row = (int)$ArrWord[1];
			$col = (int)$ArrWord[2];
			for($i=$row-1; $i<=$row+1;$i++)
			{	
				for($j=$col-1; $j<=$col+1;$j++)
				{	
					//echo "$i $j<br>";
					if(($i!= $row || $j!=$col) && $i>=0 && $j>=0 && $i<$size && $j<$size && $arrp[$i][$j] == "1")
					{
						return array(1,$i,$j);	
					}
				}
			}
		} else if($pos == 0){
			//echo "$ArrWord[3] $ArrWord[4]<br>";
			$row = (int)$ArrWord[3];
			$col = (int)$ArrWord[4];
			for($i=$row-1; $i<=$row+1;$i++)
			{
				for($j=$col-1; $j<=$col+1;$j++)
				{	//echo "ij $i $j ".$arrp[$i][$j]." ";
					if(($i!= $row || $j!=$col) && $i>=0 && $j>=0 && $i<$size && $j<$size && $arrp[$i][$j] == "1")
					{
						
						return array(0,$i,$j);	
					}
				}
			}
		}
		return false;
	}
	function getWord($substr,$coord)
	{	
		global $mysqli,$finalWordsCount,$finalWords;
		$len = iconv_strlen($substr)+1;
		
		$res = mysqli_query($mysqli, "SELECT  word FROM dictionary WHERE POSITION('$substr' IN word) AND CHAR_LENGTH(word)= $len/*ORDER BY CHAR_LENGTH(word) DESCLIMIT 10*/");
		$arrWord = mysqli_fetch_all($res, MYSQLI_NUM);
		//echo "subword $substr ".$arrWord[0][0]."<br>";
		$count = count($arrWord);
		//echo $arrWord[0][0]."<br>";
		for($i=0;$i<$count;$i++)
		{
			if(!isExist($arrWord[$i][0],0))
			{
				$finalWords[$finalWordsCount][0]=$arrWord[$i][0];
				//echo $arrWord[$i][0]."<br>";
				$finalWords[$finalWordsCount][1]=$coord[0];
				$finalWords[$finalWordsCount][2]=$coord[1];
				$finalWords[$finalWordsCount][3]=$coord[2];
				$finalWords[$finalWordsCount][4]=$coord[3];
				$finalWords[$finalWordsCount][5]=$substr;
				$finalWordsCount++;
			}
		}
		
		
	}
	
	function findWord($row, $col, $arrp,$subword="",$coord=array(0,0,0,0))
	{	
		global $finalWordsCount,$finalWords, $size,$recCount,$lenWord,$mysqli,$cycles;
		if($recCount>$cycles)
		{
			return false;
		}
		$recCount++;
			
		for($i=$row-1; $i<=$row+1;$i++)
		{
			for($j=$col-1; $j<=$col+1;$j++)
			{	
				if($arrp[$i][$j] != "2" && ($i!= $row || $j!=$col) && $i>=0 && $j>=0 && $i<$size && $j<$size && $arrp[$i][$j] != "1")
				{	
					if(empty($subword))
					{
						$coord[0]=$i;
						$coord[1]=$j;
					}
					$str = $subword.$arrp[$i][$j];
					if(mysqli_num_rows(mysqli_query($mysqli, "SELECT word FROM dictionary WHERE POSITION('$str' IN word) ORDER BY CHAR_LENGTH(word) DESC LIMIT 1")) > 0)
					{	
						$tmp = $arrp[$row][$col];
						$arrp[$row][$col]="2";
						//echo $arrp[$i][$j]."<br>";
						findWord($i,$j,$arrp,$str,$coord);
						$arrp[$row][$col]=$tmp;

					}
				}	
				else if($arrp[$i][$j] == "1" && iconv_strlen($subword)>=($lenWord-1)){
					//$len = iconv_strlen($subword)+1;
					//echo "$subword $row $col $coord[4]<br>";
					$coord[2]=$row;
					$coord[3]=$col;
					//unset($arrp);
					getWord($subword,$coord);
					/*$finalWords[$finalWordsCount] = getWord($subword);
					if(iconv_strlen($finalWords[$finalWordsCount]) >1 && !isExist($finalWords[$finalWordsCount]))
					{
						$finalWordsCount++;
					}*/
					//$query.="OR (POSITION(\"$subword\" IN word) AND CHAR_LENGTH(word)= $len) ";
				}
			}
		}
	}

	function bot()
	{	global $finalWordsCount,$finalWords,$size,$arrp,$numOfGame,$req;//,$query,$mysqli;
		$ArrChecked;
		/*for($row=0; $row<$size; $row++)
		{
			for($col=0;$col<$size; $col++)
			{
				if($arrp[$row][$col] != "1")
				{	
					//echo "$row $col <br>";
					findWord($row, $col,$arrp);
				}
			}
		}*/
		for($i=0;$i<$size*$size;$i++)
		{	$row;
			$col;
			$temp = rand(0,24);
			$row = (int)($temp/$size);
			$col = $temp%$size;
			while($ArrChecked[$row][$col] == 1)
			{
				$temp = rand(0,24);
				$row = (int)($temp/$size);
				$col = $temp%$size;
				//continue;
			} 
			$ArrChecked[$row][$col]=1;
			if($arrp[$row][$col] != "1")
			{	
					//echo "$row $col <br>";
					findWord($row, $col,$arrp);
			}
		}
		/*$res = mysqli_query($mysqli,$query);
		$arrWord = mysqli_fetch_all($res, MYSQLI_NUM);
		//echo "subword $substr ".$arrWord[0][1]."<br>";
		$count = count($arrWord);
		for($i=0;$i<$count;$i++)
		{
			if(!isExist($arrWord[$i][0],0))
			{
				$finalWords[$finalWordsCount]=$arrWord[$i][0];
				$finalWordsCount++;
			}
		}*/
		for($i=0;$i<count($finalWords);$i++)
		{
			for($j=0;$j<count($finalWords)-$i-1;$j++)
			{
				if(iconv_strlen($finalWords[$j][0]) <iconv_strlen($finalWords[$j+1][0]))
				{
					for($k=0;$k<count($finalWords[$j]);$k++)
					{
						$tmp = $finalWords[$j][$k];
						$finalWords[$j][$k]=$finalWords[$j+1][$k];
						$finalWords[$j+1][$k]=$tmp;
					}
				}
			}
		}
		/*$i=0;
		$res;
		$page;
		//for(;$i<count($finalWords) && (strcmp($finalWords[$i][0],trim($page)) != 0);$i++)
		{
			$i=5;
			//print_r($finalWords[$i]);
			$res=isCrateWord($finalWords[$i]);
			//$res = $finalWords[$i];
			$let;
			$word = iconv('UTF-8','CP1251',$finalWords[$i][0]);
			if($res[0] == 1)
				$let = $word[0];
			else
				$let= $word[iconv_strlen($word)-1];
			$let = iconv('CP1251','UTF-8',$let);
			$req = "http://balda.sknt.ru:96/saveWord.php?num=${numOfGame}&&word=".$finalWords[$i][0]."&&Field".$res[1].";".$res[2]."=".$let;
			$page  = file_get_contents($req);
		}
		*/
		return $finalWords[0][0];//$page;//."  "." ".$res[0]." ".$res[1]." ".$res[2];
	}

	for($i=0;$i<$size;$i++)
	{
		for($j=0;$j<$size;$j++)
		{
			$str = iconv('UTF-8','CP1251',$ArrGame[$i+$indexNum+2][0]);
			$arrp[$i][$j] = iconv('CP1251','UTF-8',$str[$j]);
			if($arrp[$i][$j] == "1")
				$numOne++;
		}
	}
	$cycles = (int)((1-($numOne-1)/($size*$size))*1000*(1+$cycleCoef));
	/*for($i=0;$i<$size;$i++)
	{
		for($j=0;$j<$size;$j++)
		{	
			echo $arrp[$i][$j];
		}
		echo "<br>";
	}
	echo "Поиск<br>";*/
	$startTime = date("r");
	echo bot()."<br>";
	//writeFile("ArrWord.txt","","w");
	writeFile("ArrWord.txt",$numOfGame."\r\n".$startTime." ".date("r")." cycles: ${cycles} recCount: ${recCount}\r\n","a");
	$page="";
	$i=0;
	$count = count($finalWords);
	//$res=isCrateWord($finalWords[$i]);
	echo "<br>".$res[0]." ".$res[1]." ".$res[2];
	$i=0;
	
	for($i=0;$i<$count && ($i == 0 || /*!strpos(trim($page),"3")*/ strcmp("3",trim($page)) != 0);$i++)
	{
		//rint_r($finalWords[$i]);
		//echo "<br>i $i";
		$res=isCrateWord($finalWords[$i]);

		if(!$res)
		{
			//echo  " ".$finalWords[$i][0]." false<br>";
			continue;
		}
			
		$let;
		$word = iconv('UTF-8','CP1251',$finalWords[$i][0]);
		if($res[0] == 1)
			$let = $word[0];
		else
			$let= substr($word, -1);
		//$let = iconv('CP1251','UTF-8',$let);
		$finalWords[$i][0] = $word;//iconv('UTF-8', 'CP1251',$finalWords[$i][0]);
		$req = "http://balda.sknt.ru/saveWord.php?num=${numOfGame}&&word=".$finalWords[$i][0]."&&Field".$res[1].";".$res[2]."=".$let."&&typeAnsw=1";
		//$req = "http://127.0.0.1/saveWord.php?num=${numOfGame}&&word=".$finalWords[$i][0]."&&Field".$res[1].";".$res[2]."=".$let."&&typeAnsw=1";
		$page = file_get_contents($req);
		writeFile("ArrWord.txt",$req." ans ".$page."\r\n","a");
		echo $req." ans ".$page." len: "."<br>";
	}
	echo "<br>".$recCount;
	//echo "<br> $i ".$finalWords[$i-1][0];
	if($i < $count)
		mysqli_query($mysqli,"DELETE FROM `bot` WHERE num = $numOfGame");
	else {
		mysqli_query($mysqli,"UPDATE `bot` SET `type`=`type`+1 WHERE num = $numOfGame");
		$res = mysqli_query($mysqli,"SELECT type FROM `bot` WHERE num = $numOfGame");
		$type = mysqli_fetch_array($res)[0];
		if($type > 3)
		{
			$res = mysqli_query($mysqli,"SELECT name FROM listOfGames WHERE id = ${numOfGame}");
			$nameOfGame = mysqli_fetch_array($res)[0];
			if(mail('shtirx@gmail.com', 'balda', translit("Превышено колличество попыток хода для $nameOfGame")))
				mysqli_query($mysqli,"UPDATE `bot` SET `type`=-1 WHERE num = $numOfGame");
		}
	}
		
	mysqli_query($mysqli,"UPDATE `bot` SET `type`=0 WHERE id = 1");
?>
