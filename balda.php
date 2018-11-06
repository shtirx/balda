<?php
include("passwordLibrary.php");
include("users.php");
$numOfGame=0;

if(!empty($_GET["num"]))
{
	$numOfGame=$_GET["num"];
	
}
date_default_timezone_set('Europe/Minsk');
session_set_cookie_params(10000);
session_start();
$login=checkPsw($_COOKIE["CookiePsw"]);
user($login);
$mysqli = new mysqli("127.0.0.1", "root","", "balda");
$nameOfGame = mysqli_query($mysqli,"SELECT name FROM listOfGames WHERE id=$numOfGame");
$nameOfGame = mysqli_fetch_array($nameOfGame)[0];
$game = mysqli_query($mysqli,"SELECT field FROM $nameOfGame");
$ArrGame = mysqli_fetch_all($game,MYSQLI_NUM);
$indexNum=0;
while($ArrGame[$indexNum][0]<2)
	$indexNum++;
$numOfPlayers = $ArrGame[$indexNum+1][0];
if(((!isset($_SESSION['access']) || $_SESSION['access']!=true) && strcmp(trim($login),"")==0))
{
header("location:index.php?page=balda.php?num=".$numOfGame);
} else if(!checkGameB($game,$login,$numOfPlayers))
	header("location:newgame.php");
else {
	$numOfSize = $ArrGame[$indexNum][0];
	$countGame = count($ArrGame);
	$curPlayer = $ArrGame[$countGame-1][0];
	$playAudio = $ArrGame[$countGame-2][0];
	/*for($i=0; $i<count($ArrGame);$i++)
	{
		echo $ArrGame[$i][0]."<br>";
	}*/
	$j1=0;
	$i1=0;
	$youPlay=false;
?>
<!DOCTYPE html>
<html>
<head>
	<style type="text/css">
   .baldaFiled { 
    padding: 5px;
    top:-200px;
    font-size: 20;
    height: 100%;
	width: 100%;
	text-align:center;
   }

   table#wordsTable
   {  /* Ширина таблицы */
    border: 4px double black; /* Рамка вокруг таблицы */
    border-collapse: collapse; /* Отображать только одинарные линии */ }
   table#wordsTable td,th { 
    padding: 5px; /* Поля вокруг содержимого ячеек */
    border: 1px solid black; /* Граница вокруг ячеек */
   }
   .winner{
   	font-weight: 600;
   }
  </style>
 <title> Балда </title> 
</head>
<body>
<script>
	var property;
	var numOfGame=<?php echo $numOfGame;?>;
	var size=<?php echo $numOfSize?>;
	var numOfPlayers=<?php echo $numOfPlayers;
	unset($temp);?>;
	var word = "";
	var Green = "#00FF00";
	var arr = [];
	var arrp = [];
	var mas = [];
	var position=1;
	var p=0;
	var countLet = [];
	var newLetter=false;
	var newLetterPos=[size,size];
	var curPlayer=<?php echo "$curPlayer";?>;
	var id = <?php echo checkPsw($_COOKIE["CookiePsw"],1)?>;
	var answ='0';
	var sugWord="";
	var finalWords=[];
	var finalWordsCount=0;
	for(var i=0;i<numOfPlayers;i++)
	{
		countLet[i]=[];
		countLet[i][1]=0;

	}
		
	for (var i = 0; i < size; i++)
	{
		arr[i] = [];
		for (var j = 0; j < size; j++)
		{
			arr[i][j] = 0;
		}
	}
	function fieldEl(i,j)
	{
		return document.getElementById('Field'+i+";"+j);
	}
	function hideText(obj)
	{
		var property =  obj.id;
		var data = document.getElementById(property);
		var id = property.substr(4,property.length-1);
		if(countLet[id][1]==0)
		{
			data.value=countLet[id][0];
			countLet[id][1]=1;
		} else {
			data.value="   ";
			countLet[id][1]=0;
		}
		console.log(property);
	}
	function isPosition(rows, cols)
	{
		var fr= +(rows)+1;
		var fc = +(cols)+1;
		for(var i=+rows-1;i<=fr;i++)
		{
			for(var j=+cols-1;j<=fc;j++)
		  	{
		  		if(i==rows && j==cols)
		  			continue;
		  		if(position == 1)
		  		{
		  			if(i>=0 && j>=0 && i<size && j<size && fieldEl(i,j).value != "")
			  				return true;
		  		} else {
		  			if( i>=0 && j>=0 && i<size && j<size && arr[i][j] == (position-1))
			  				return true;
		  		}	
		  	}
		}	
	  	return false;
	}

	function getXmlHttp()
	{
	  var xmlhttp;
	  try {
	    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	  } catch (e) {
	    try {
	      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	    } catch (E) {
	      xmlhttp = false;
	    }
	  }
	  if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
	    xmlhttp = new XMLHttpRequest();
	  }
	  return xmlhttp;
	}
	function getWord(subword)
	{
		var xhr = getXmlHttp();
		xhr.open('GET', '/getWord.php?word='+subword, false);
		xhr.send();
		if (xhr.readyState == 4) {
			if (xhr.status != 200) {
			  //alert( xhr.status + ': ' + xhr.statusText );
			} else {
			  return xhr.responseText;
			}
		}
	}
	function isExist(word)
	{
		for(var i=0;i<finalWords.length-1;i++)
		{
			if(word.localeCompare(finalWords[i]) == 0)
				return true;
		}
		return false;
	}
	/*function getWord(subword)
	{
		//var temp='ъ';
		var req = getXmlHttp();
		req.onreadystatechange = function() {  
			if (req.readyState == 4) { 
				if(req.status == 200) {
						return req.responseText;
				}
			}
		}
		req.open('GET', '/getWord.php?word='+subword, true);
		req.send(null);	
	}*/
	//console.log(getWord('аб'));
	function findWord(row, col,arrp,subword="")
	{	//console.log(row+' '+col+ ' str:' + subword);
		for(var i=row-1; i<=row+1;i++)
		{
			for(var j=col-1; j<=col+1;j++)
			{	//console.log(i+ ' '+j);
				if(arrp[row][col] != -1 && (i!= row || j!=col) && i>=0 && j>=0 && i<size && j<size && arrp[i][j] != "")
				{	
					var tmp = arrp[row][col];
					var str = subword+arrp[i][j];
					//console.log('rec '+i+ ' '+j+ ' ' + str);
					arrp[row][col]=-1;
					findWord(i,j,arrp,str);
					arrp[row][col]=tmp;
				}	
				else if(arrp[i][j] == "" && subword.length>1){
					finalWords[finalWordsCount] = getWord(subword);
					if(finalWords[finalWordsCount].length >1 && !isExist(finalWords[finalWordsCount]))
					{
						//console.log(finalWordsCount+ ' ' + finalWords[finalWordsCount]);
						finalWordsCount++;
					}
				}
			}
		}
	}
	function bot()
	{	
		for(var row=0; row<size; row++)
		{
			for(var col=0; col<size; col++)
			{
				if(arrp[row][col] != "")
				{
					findWord(row, col,arrp);
				}
			}
		}
		for(var i=0;i<finalWords.length;i++)
		{
			for(var j=0;j<finalWords.length-i-1;j++)
			{
				if(finalWords[j].length <finalWords[j+1].length)
				{
					var temp = finalWords[j];
					finalWords[j] = finalWords[j+1];
					finalWords[j+1]=temp;
				}
			}
		}
		console.log(finalWordsCount + ' ' + finalWords);
		return finalWords[0];
	}
	
	/*function bot()
	{
		if(arrp[2][0] != "")
		{
				findWord(2, 0,arrp);
		}
		for(var i=0;i<finalWords.length;i++)
		{
			for(var j=0;j<finalWords.length-i-1;j++)
			{
				if(finalWords[j].length <finalWords[j+1].length)
				{
					var temp = finalWords[j];
					finalWords[j] = finalWords[j+1];
					finalWords[j+1]=temp;
				}
			}
		}
		console.log(finalWordsCount + ' ' + finalWords);
		return finalWords[0];
	}*/
	setInterval(function FcurPlayer() {
		var req = getXmlHttp();
		req.onreadystatechange = function() {  
			if (req.readyState == 4) { 
				if(req.status == 200) { 
						p=req.responseText;
						if(p != curPlayer)
						{
							location.reload();
							//console.log(p);
						}
						//console.log(p + " " + curPlayer);
				}
			}

		}
		req.open('GET', '/update.php?num='+numOfGame+'&id='+id+'&nP='+curPlayer, true);
		req.send(null);
		
	},2000)
	function vote() {
		// (1) создать объект для запроса к серверу
		if(newLetter == false)
		{
			alert("Вы не добавили ни одной буквы!");
			return;
		}
		var req = getXmlHttp()  
	       
	        // (2)
		// span рядом с кнопкой
		// в нем будем отображать ход выполнения
		if(curPlayer != -1)
			var statusElem = document.getElementById('info'+ curPlayer) 
		
		req.onreadystatechange = function() {  
	        // onreadystatechange активируется при получении ответа сервера
			if (req.readyState == 4) { 
				if(req.status == 200) { 
					if(req.responseText == "0")
						alert("Такого слова не существует!");
					else if(req.responseText == "1")
						alert("Это слово уже было!");
					else if(req.responseText == "3")
						alert("Ошибка!");
					else {
						statusElem.innerHTML=req.responseText;
						document.getElementById('addWord').disabled = true;
						document.getElementById('clearWord').disabled = true;
					}	
				}
				// тут можно добавить else с обработкой ошибок запроса
			}
		}
	       // (3) задать адрес подключения
	    var request="";
	    request+="&&Field"+newLetterPos[0]+';'+newLetterPos[1]+'='+fieldEl(newLetterPos[0],newLetterPos[1]).value;
	    console.log(request);

		req.open('GET', '/saveWord.php?num='+numOfGame+'&&word='+word+'&&size='+size+request, true);  
		request="";
		req.send(null);
	}

	function changeCol(obj)
	{
		//alert("ChangeCol()");
	  	property =  obj.id;
	  	var data = document.getElementById(property);
	  	var con = property.indexOf(';');
	  	var rows = property.substr(5,con-5);
	  	var cols = property.substr(con+1,property.length-(con+1));
	  	//rows=Number(rows);
	  	//cols=Number(cols);
	  	//console.log("r "+rows+ " c "+cols);
	  	if(!isPosition(rows,cols) && data.value != "")
	  	{
	  		alert("Так нельзя!");
	  		if(!data.readOnly)
	  			data.value="";
	  		return 0;
	  	} 
	  	var logNewL=(newLetterPos[0] == rows && newLetterPos[1] == cols);
	  	//var log1=("" != document.getElementById('Field'+rows+cols).value && (arrp[rows][cols].charAt(0) == document.getElementById('Field'+rows+cols).value || logNewL));
	  	if(newLetter == true && /*arrp[rows][cols] == "")*/ data.readOnly==false)
	  	{
	  		//alert("Вы уже ввели букву");
	  		data.value="";
	  	} else if(arr[rows][cols] == 0 && (arrp[rows][cols].charAt(0) != "" || position > 1) && "" != data.value)// новая буква записывается в фиальное слово если слово уже начало заполняться или эта буква была поставленна ранее
	  	{
	  		console.log(rows + " " + cols);
	  		data.style.backgroundColor = Green;
	  		arr[rows][cols]=position;
	  		word = word.substring(0,word.length) + data.value.trim();
	  		position++;
	  		if(arrp[rows][cols].charAt(0) == "")
	  		{
	  			newLetterPos[0]=rows;
	  			newLetterPos[1]=cols;
	  			newLetter=true;
	  			data.readOnly = true;
	  		}
	  			
	  	}else if(arrp[rows][cols] == "" && data.value.trim() !="" && !newLetter)
	  	{
	  		arrp[rows][cols] = data.value.trim();
	  		newLetter = true; 
	  		newLetterPos[0]=rows;
	  		newLetterPos[1]=cols;
	  		data.readOnly = true;
	  		//data.style.backgroundColor = Green;
	  	}
	  	if(document.getElementById('clearWord').disabled == false)
	  		document.getElementById('info'+curPlayer).innerHTML=word;

	  	/*console.log(newLetter + " " + newLetterPos[0]+ " " + newLetterPos[1]);
	  	if(newLetter)
	  	{	console.log("Обработчик0");
	  		console.log(newLetterPos[0]+ " " + newLetterPos[1]);
	  		fieldEl(newLetterPos[0],newLetterPos[1]).onclick=function(){
	  			console.log("Обработчик");
			}
	  	}*/	
	}
	function again()
	{
		for(var i=0;i<size;i++)
	    {
	    	for(var j=0;j<size;j++)
	    	{
	    			if(arr[i][j] != 0)
	    			{
	    				console.log(i + " " +j + " "+arr[i][j]);
	    				arr[i][j]=0;
	    				fieldEl(i,j).style.backgroundColor = "#FFFFFF";
	    			}
	    			if(arrp[i][j] == "")
	  					fieldEl(i,j).readOnly=false;
	    	}
	    }
	    if(newLetter)
	    {
	    	newLetter=false;
	    	//console.log("c: "+ newLetterPos[0]+" " +newLetterPos[1]);
	    	//fieldEl(newLetterPos[0],newLetterPos[1]).backgroundColor="#FFFFFF";
		    fieldEl(newLetterPos[0],newLetterPos[1]).value ="";
		    fieldEl(newLetterPos[0],newLetterPos[1]).readOnly=false;
		  	arrp[newLetterPos[0]][newLetterPos[1]]="";
		    newLetterPos[0]=size;
		    newLetterPos[1]=size;
	    }
	    word="";
		position=1;
		document.getElementById('info'+curPlayer).innerHTML=word;
	}
</script>
<div id="baldaFiled" align = "center" >
<?php
	$lenP[$numOfPlayers];
	$numOne=0;
	$game;
	$temp=null;
	if(strcmp(trim($ArrGame[$curPlayer][0]),$login)==0 && $curPlayer>=0)
	{
		$youPlay=true;
	}
	if(isset($_POST["addNewWord"]))
	{
		$word = trim($_POST["newWord"]);
		$word = mb_strtolower($word);
		mysqli_query($mysqli,"INSERT INTO dictionary VALUES(null,'$word','')");
		$word = iconv('UTF-8', 'CP1251',$word);
		writeFile("dictionary/newWords.txt",$word."\r\n",'a');
		echo "<script type=\"text/javascript\">
		window.location.href = 'balda.php?num='+$numOfGame;
		</script>";
	}
?>
<div id='time'>  </div>
<div id="infoPlay"><p>   </p></div>
<script>
	var audio = new Audio(); // Создаём новый элемент Audio
	audio.src = 'balda/04820.mp3'; // Указываем путь к звуку "клика"
	audio.preload = 'auto';
	<?php
	if($youPlay && $playAudio == 1)
	{
		//echo "audio.autoplay = true;";
		echo "audio.play();";
		$idP=$countGame-1;
		mysqli_query($mysqli,"UPDATE $nameOfGame SET field='0' WHERE id=$idP");
	}
	?>
</script>
<input value="Ок" onclick="vote()" type="button" id="addWord" <?php if(!$youPlay) echo "disabled"?>/>
<input value="Заново" onclick="again()" type="button" id="clearWord" <?php if(!$youPlay) echo "disabled"?>/>
<table id="fieldWords">
<?php
	for($i=0;$i<$numOfSize;$i++)
	{
		echo "<tr>";
		for($j=0;$j<$numOfSize;$j++)
		{
			$nameField="Field$i;$j";
			?>
			<td>
			<input type=<?php
			$str = iconv('UTF-8','CP1251',$ArrGame[$i+$indexNum+2][0]);
			$value = iconv('CP1251','UTF-8',$str[$j]);
			if($value==1)
				$numOne++;
			echo "\"text\"";
			?> <?php echo "id=\"".$nameField."\" ";
			if($value != 1)
				echo "onclick";
			else
				echo "onblur";?>="changeCol(this)" size="1" maxlength="1" autocomplete="off" <?php
			if($value != 1)
			{
				echo "value=\"  ".$value."  \"";
				echo " readonly";
			}
			?>>
			</td>
			<?php
		}
		echo "</tr>";
	}

	$PlayerWords[$numOfPlayers];
	$numOfWord=1;
	for($i=0;$i<$numOfPlayers;$i++)
	{
		//$PlayerWords[$i]=file($filePlayer.($i+1).".txt");
		$game = mysqli_query($mysqli,"SELECT word FROM ${nameOfGame}Player$i ORDER BY id");
		$ArrGame = mysqli_fetch_all($game,MYSQLI_NUM);
		for($j=0;$j<count($ArrGame);$j++)
			$PlayerWords[$i][$j] = $ArrGame[$j][0];
	}
?>
</table>
<?php 
	if($numOne > 0)
		echo "<b>Осталось ".($numOne)." ход(ов) или ".round($numOne/($numOfSize*($numOfSize-1))*100)."%</b><br>";
?>
<form method="POST" action="" enctype="">
Новое слово: <input type="text" autocomplete="off" name="newWord">
<input type="submit" name="addNewWord" value="OK"/>
</form>
<a href="newgame.php">Список игр</a></br>
<script>
	//var dateF = new Date(2017, 8, 4, 0, 0, 0, 0).getTime()/1000;
	//var dateF2 = new Date(2017, 11, 25, 0, 0, 0, 0).getTime()/1000;
	//var dateF = new Date(2017, 7, 28, 14, 48, 0, 0).getTime()/1000;
	var dateName = [];
	var nameOfDate = [];
	dateName[0]=new Date(2017, 8, 4, 0, 0, 0, 0).getTime()/1000;
	nameOfDate[0]="До 4 сентября";
	dateName[1]=new Date(2017, 11, 25, 0, 0, 0, 0).getTime()/1000;
	nameOfDate[1]="До зимней сессии";
	dateName[2]=new Date(2018, 0, 22, 0, 0, 0, 0).getTime()/1000;
	nameOfDate[2]="До окончания зимней сессии";
	dateName[3]=new Date(2018, 5, 4, 0, 0, 0, 0).getTime()/1000;
	nameOfDate[3]="До летней сессии";
	indexC = 0;
	function TimeLeft()
	{
		//var index=0;
		var now = new Date().getTime()/1000;
		var left = Math.round(dateName[indexC]-now);
		while(left < 0 && indexC < dateName.length)
		{
			indexC++;
			left = Math.round(dateName[indexC]-now);
		}
		var str;
		if(left > 0)
			str =  '<b>'+nameOfDate[indexC]+' осталось: ' + Math.floor(left/(3600*24)) + ' дней ' + 
			Math.floor(left/(3600))%24 + ' часов ' + Math.floor(left/(60))%60 +  ' минут ' + left%60 +" cекунд</b>";
		else
			str = '<b> Пора! </b>';
		document.getElementById('time').innerHTML=str;
	}
	TimeLeft()
	setInterval(function(){
				TimeLeft();
				},1000)
	
</script>

<table id="wordsTable">
	<tr>
<?php
	
	$max=count($PlayerWords[0]);
	for($i=1;$i<$numOfPlayers;$i++)
	{
		if($max < count($PlayerWords[$i]))
			$max = count($PlayerWords[$i]);
	}
	for($i=0;$i<$numOfPlayers;$i++)
	{
		if($max<15)
			echo "<th>".$PlayerWords[$i][0]."</th>";
		else
			echo "<th colspan=\"2\">".$PlayerWords[$i][0]."</th>";
	}
?>
   </tr>

<?php
	$id=false;
	for($i=1;$i<$max;$i++)
	{
		echo "<tr>";
		for($j=0;$j<$numOfPlayers;$j++)
		{
			if($max<15)
				{
					echo "<td>";
					if($numOfWord<count($PlayerWords[$j]))
						echo $PlayerWords[$j][$numOfWord];
					else
					{
						echo "<div id='info$j'><p>          </p></div>";
						$id=true;
					}
				}
			else
				{	
					if($numOfWord<count($PlayerWords[$j]))
					{
						echo "<td>".$PlayerWords[$j][$numOfWord]."</td><td>";
						if($numOfWord+1<count($PlayerWords[$j]))
							echo $PlayerWords[$j][$numOfWord+1];
						else
						{
							echo "<div id='info$j'><p>          </p></div>";
							$id=true;
						}
					}
					else {
						echo "<td colspan=\"2\"><div id='info$j'><p>          </p></div>";
							$id=true;
					}					
				}
			?>
			</td>
			<?php
		}
		echo "</tr>";
		$numOfWord++;
		if($max>=15)
		{
			$i+=1;
			$numOfWord++;
		}
	}
?>
<tr>
<?php
	if(!$id)
	{
		for($i=0;$i<$numOfPlayers;$i++)
		{	if($max<15)
				echo "<td><div id='info$i'><p>          </p></div></td>";
			else
				echo "<td colspan=\"2\"><div id='info$i'><p>          </p></div></td>";
		}
	}
?>
</tr>
<tr>

<?php 
	for($i=0;$i<$numOfPlayers;$i++)
	{
		$len=0;
		//$lenP[$i]=0;
		for($j=1;$j<count($PlayerWords[$i]);$j++)
		{
			$len+=iconv_strlen(trim($PlayerWords[$i][$j]));
		}
			$lenP[$i]=$len;
		echo "<td";
		if($max>=15)
			echo " colspan=\"2\"";
		echo "><input type=\"button\" color=\"#ffffff\" onclick=\"hideText(this)\" id=\"hide$i\" value=\"";
		if(strcmp(trim($login),"Никита")==0)
			echo $lenP[$i]."\"/>";
		else
			echo "   \"/>";
		echo "</td>";?>
<script>
	<?php echo "countLet[$i][0]=".$lenP[$i];?>
</script>
<?php	}
?>
</tr>
</table>
<?php
	$endOfGame=false;
	$winner=false;
	if($numOfSize*($numOfSize-1)%$numOfPlayers == $numOne)
	{
		$maxL=$lenP[0];
		$numOfP=0;
		for($i=1;$i<$numOfPlayers;$i++)
		{
			if($maxL < $lenP[$i])
			{
				$maxL = $lenP[$i];
				$numOfP=$i;
			}	
		}
		$res = mysqli_query($mysqli, "SELECT word FROM ${nameOfGame}Player${numOfP} WHERE id=1");
		$nameWinner = mysqli_fetch_array($res)[0];
		if(strcmp(trim($nameWinner),$login) == 0)	
			$winner = true;//echo "Вы победили!<br>";
				//echo "$nameWinner $login<br>";	
		$endOfGame=true;
	}	
?>
</div>
<script>
	var flag=<?php if($endOfGame) echo 1; else echo 0;?>;
	var youPlay = <?php if($youPlay) echo "true"; else echo "false";?>;
	var winner = <?php if($winner) echo "true"; else echo "false";?>;
	var color=0x000000;
	if(flag != 1)
	{
		if(youPlay)
			document.getElementById('infoPlay').innerHTML="Ваш ход!";	
		else
			document.getElementById('infoPlay').innerHTML="Ожидание хода";
	} else {	
		document.getElementById('addWord').disabled = true;
		document.getElementById('clearWord').disabled = true;
		if(winner)
		{
			document.getElementById('infoPlay').innerHTML="Вы победили!";
			
				setInterval(function(){
					document.getElementById('infoPlay').style.color=color;
					color+=0x010101;
					if(color>= 0xfffffe)
						color=0x000000;
			},100)
		} else
			document.getElementById('infoPlay').innerHTML="Вы проиграли";
	}
		
	for (var i = 0; i < size; i++)
	{
		arrp[i] = [];
		for (var j = 0; j < size; j++)
		{
			arrp[i][j] = fieldEl(i,j).value.trim();
			//document.write(i+" "+j+" "+arrp[i][j]+"<br>");
		}
	}	
	//console.log('max: ' + bot());
</script>
</body>
</html>
<?php } ?>