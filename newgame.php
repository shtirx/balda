<?php
//include("file.php");
include("passwordLibrary.php");
include("users.php");
date_default_timezone_set('Europe/Minsk');
session_set_cookie_params(10000);
session_start();
if(isset($_POST["quit"]))
	{
		setcookie('CookiePsw','',time() - 3600);
		session_destroy();
		header("location:index.php");
	}
$login=checkPsw($_COOKIE["CookiePsw"]);
//echo "login: ".$login."<br>";
if(!empty($_GET["deleteProf"]))
	{
		if($_GET["deleteProf"] == true)
		{
			deleteProfile($login);
			setcookie('CookiePsw','',time() - 3600);
			session_destroy();
			header("location:index.php");
		}
	}
if((!isset($_SESSION['access']) || !$_SESSION['access']) && strcmp(trim($login),"")==0)
{
header("location:index.php?page=newgame.php");
} else {
$status = getStatus();
?>
<html>
<head>
	<title>Балда</title>
<script>
	var id = <?php echo checkPsw($_COOKIE["CookiePsw"],1)?>;
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
<?php
  /*if( $curl = curl_init() ) {
    curl_setopt($curl, CURLOPT_URL, 'https://api.push.expert/v1/POST');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "x-project-id=40fe83fe5208f7ec53d5bafb462c93de&x-secret-key=AOKXoUot*WNPw246V81A3E1N)5n3Es7#0Z3fy!ke");
    $out = curl_exec($curl);
    echo $out;
    curl_close($curl);
  }*/
?>
    /*!function(p,u,s,h,m,e){
    	p.__cmbk=p.__cmbk||function(){
    		(p.__cmbk.q=p.__cmbk.q || []).push(arguments)};m=u.createElement(s);
    		e=u.getElementsByTagName(s)[0];
    		m.async=!0;
    		m.src=h;
    e.parentNode.insertBefore(m,e);
	}(window,document,'script','//balda.push.community/js/integration.js');

    // назначить текущему токену тег(при регистрации)
    __cmbk('setRegistrationTag','test-users');
    // назначить userId текущему токену (при регистрации)
    __cmbk('setUser','yourInternalId');
    // получить текущий токен
    __cmbk('getToken', function (err, token) {
    if (!err) {console.log(token)            }
    });
    // отключить автоматический показ попапа
    __cmbk('disableAutoRender');
    // отобразить попап
    __cmbk('render');
    // изменить текст в реквесте на разрешение подписок
    __cmbk('setRequestMessage', 'Подключайте пуш-уведомления!');
    // настроить время(в секундах), через которое пользователь снова увидит запрос на показ уведомлений
    __cmbk('setCloseTimeout', 86400);
    // Доступны ли пуш-уведомления в текущем браузере
    __cmbk('availableInBrowser', function(err, isAvailable){
        if(isAvailable) {
          console.log('Push notifications are available');
        }
    });

    // подписка на события в попап-окне
    //__cmbk('onEvent', eventType, cb);
    // поддерживаются события `subscription` и `popup.close`
    // - subscription
    __cmbk('onEvent', 'subscription', function(err, body){
        if(body.result) {
          console.log('Success, user hashToken', body.hashToken)
        } else {
          console.log('Error', body.error)
        }
    });
    // - popup
    __cmbk('onEvent', 'popup.close', function(err, body){
        //body = null
        console.log('popup closed');
    });*/

</script>
</head>
<script>
	function deleteP()
	{
		var result = confirm("Вы дейтсвительно хотите удалить профиль?");
		if(result)
			window.location.href = 'newgame.php?deleteProf=' + result;
	}
	function deleteG(obj, name)
	{	
		property =  obj.id;
	  	var countNum = (property.length-6);
	  	var id = property.substr(6,countNum);
		var result = confirm("Вы дейтсвительно хотите удалить игру "+name+"?");
		if(result)
		{
			window.location.href = 'newgame.php?deleteG=' + id;
		}
	}
</script>
<body>
<div align = "left" >
	<?php
	/*$str = 'бува1 :)';
	if (preg_match("/[\p{P}\p{Z}]/",$str) == 0) {
	   echo "Все верно<br>";
	} else {
	   echo "Есть недопустимые символы<br>";
	}*/
?>
<?php
	echo "Имя: ".$login." ";
	user($login);
	//foreach($_SERVER as $k=>$v) {print("$k = $v<br/>");}
?>
	<form method="POST" action="" enctype="">
	<input type="submit" name="quit" value="Выход"/>
	<input  onclick="deleteP()" type="button" value="Удалить профиль" >
	</form>
<?php
	$mysqli = new mysqli("127.0.0.1", "root","", "balda");
	//$mysqli = new mysqli("balda.sknt.ru", "root","", "balda");
	function isNoun($wordt)
	{
		//if(!mb_detect_encoding($str, 'UTF-8', true))
		
		$PWord = substr($wordt,strlen($wordt)-2,2);
		$PWord = iconv('CP1251', 'UTF-8',trim($PWord));
		//echo "<br>".$PWord;
		if(strcmp($PWord,"ть") == 0)
			return false;
		if(strcmp($PWord,"ый") == 0)
			return false;
		if(strcmp($PWord,"ся") == 0)
			return false;
		if(strcmp($PWord,"ий") == 0)
			return false;
		if(substr_count($wordt," ") > 0)
			return false;
		if(substr_count($wordt,"-") > 0)
			return false;
		if(strcmp($PWord,"ки") == 0)
			return false;
		if(strcmp($PWord,"ри") == 0)
			return false;
		return true;
	}
	/*$dictianory = file("DictionaryOS.TXT");
	$numWord;
	$word="";
	$i=0;
	while(!(strlen($word) == 10 && isNoun($word)))
	{
		$numWord = rand(0,count($dictianory)-1);
		$word = trim($dictianory[$numWord]);

		$i++;
	}*/
	function request($mysqli,$sql)
    {
        if (!$result = $mysqli->query($sql)) {
            echo "Извините, возникла проблема в работе сайта.<br>";
            echo "Ошибка: Наш запрос не удался и вот почему:<br>";
            echo "Запрос: " . $sql . "\n";
            echo "Номер_ошибки: " . $mysqli->errno . "\n";
            echo "Ошибка: " . $mysqli->error . "\n";
            //exit;
        }
        return $result;
    }
	/*if(!file_exists("balda/nextGame.txt"))
	{
		writeFile("balda/nextGame.txt",0,"w");
	}
	$fileField;
	$filePlayer;
	$filecurPlayer;
	$fileSize;
	$folderGame; 
	$listOfGames=file("balda/ListOfGames.txt");*/
	$reload=0;
	$bot = 0;
	$numOfGame;
		if(!empty($_GET["deleteG"]))
		{	
			$numOfGame = $_GET["deleteG"];
			$nameOfGame = mysqli_query($mysqli,"SELECT name FROM listOfGames WHERE id=$numOfGame");
			$nameOfGame = mysqli_fetch_array($nameOfGame)[0];
			$numOfPlayers = mysqli_query($mysqli,"SELECT field FROM $nameOfGame WHERE id=4");
			$numOfPlayers = mysqli_fetch_array($numOfPlayers)[0];
			for($i=0;$i<$numOfPlayers;$i++)
				mysqli_query($mysqli,"DROP TABLE IF EXISTS ${nameOfGame}Player${i}");
			mysqli_query($mysqli,"DROP TABLE IF EXISTS $nameOfGame");
			mysqli_query($mysqli,"DELETE FROM listOfGames WHERE id = $numOfGame");
			$reload = 1;
		}
		
	if(isset($_POST["Submit"]))
	{	
		$Players=$_POST['Players'];
        //$Players=explode(" ",trim($_POST['Players']));
        $countP=count($Players);
        $Players[$countP++]=$login;
        $PlayersChanged;
        
        for($i=0;$i<$countP;$i++)
        {
        	$p=rand(0,$countP-1);
        	if($PlayersChanged[$p] != $i)
        	{
        		$temp=$Players[$i];
	        	$Players[$i]=$Players[$p];
	        	$Players[$p]=$temp;
	        	$PlayersChanged[$i]=$p;
        	}
        }
        /*if($Players[0] == "bot")
        {
        	//echo "true<br>";
        	$temp = $Players[0];
        	$Players[0] = $Players[1];
        	$Players[1] = $temp;
        }*/
		$numOfSize = trim($_POST['numOfSize']);
		$nameOfGame = trim($_POST['nameOfGame']);
		if(empty($nameOfGame))
		{
			$count = mysqli_query($mysqli,"SELECT max(id) FROM listOfGames");
			$count = mysqli_fetch_all($count, MYSQLI_NUM);
			$nameOfGame="game".($count[0][0]+1);
			//echo "name: $nameOfGame<br>";
		} 
		if(preg_match("/[\p{P}\p{Z}]/",$nameOfGame) != 0) {
		   echo "Есть недопустимые символы<br>";
		} else if($countP < 2)
			echo "В игре должно учавствовать минимум 2 игрока!<br>";
		else if(mysqli_num_rows(mysqli_query($mysqli,"SELECT id FROM listOfGames WHERE name='$nameOfGame'")) != 0)
			echo "Такое название уже существует!<br>";
		else {
			if(empty($_POST['numOfSize']))
				$numOfSize = 5;
		    $numOfPlayers = $countP;
			$res = mysqli_query($mysqli, "SELECT word FROM dictionary WHERE CHAR_LENGTH(word) = $numOfSize");
			$answ = mysqli_fetch_all($res,MYSQLI_NUM);
			$r = rand(0,mysqli_num_rows($res));
			$word = $answ[$r][0];
			$sql = "CREATE TABLE ".$nameOfGame." (
		      id INT AUTO_INCREMENT,
		      field TEXT,
		      PRIMARY KEY(id)
		    )";
		    request($mysqli,$sql);
		    for($i=0;$i<$numOfPlayers;$i++)
		    {
		    	$sql = "CREATE TABLE ".$nameOfGame."Player".$i." (
			      id INT AUTO_INCREMENT,
			      word TEXT,
			      PRIMARY KEY(id)
			    )";
			    request($mysqli,$sql);
			    $sql = "INSERT INTO ".$nameOfGame."Player".$i." VALUES (null, '".trim($Players[$i])."')";
		    	request($mysqli,$sql);
		    	$sql = "INSERT INTO $nameOfGame VALUES (null, '".trim($Players[$i])."')";
		    	request($mysqli,$sql);
		    }
		    request($mysqli,"INSERT INTO listOfGames VALUES (null, '".$nameOfGame."')");
		    $sql = "INSERT INTO $nameOfGame VALUES (null, $numOfSize)";
		    request($mysqli,$sql);
		    $sql = "INSERT INTO $nameOfGame VALUES (null, $numOfPlayers)";
		    request($mysqli,$sql);
		    for($i=0;$i<$numOfSize;$i++)
		    {	$str="";
		    	for($j=0;$j<$numOfSize;$j++)
		    		$str.="1";
		    	$sql = "INSERT INTO $nameOfGame VALUES (null,'$str')";
		    	if($i == (int)($numOfSize/2))
		    		$sql = "INSERT INTO $nameOfGame VALUES (null, '".$word."')";	
		    	request($mysqli,$sql);
		    }
		    $time= time();
		    mysqli_query($mysqli,"INSERT INTO $nameOfGame VALUES (null, '$time')");
		    mysqli_query($mysqli,"INSERT INTO $nameOfGame VALUES (null, '1')");
		    $sql = "INSERT INTO $nameOfGame VALUES (null, '0')";	
		    request($mysqli,$sql);

		    /*if($Players[0] == "bot")
		    {
		    	$res = mysqli_query($mysqli,"SELECT id FROM listOfGames WHERE name='$nameOfGame'");
		    	$numOfGame = mysqli_fetch_array($res)[0];
		    	$req = "http://127.0.0.1:8080/bot.php?num=${numOfGame}";
				file_get_contents($req);   
				$bot = 1;
		    }*/
		    if($Players[0] == "bot")
	        {
	        	$resId = mysqli_query($mysqli,"SELECT id FROM listOfGames WHERE name='$nameOfGame'");
	        	$num = mysqli_fetch_array($resId)[0];
	        	mysqli_query($mysqli,"INSERT INTO bot VALUES (null, $num, 0)");
	        }
		    $reload=1;
		}
	}
?>
<script>
	var reload = <?php echo $reload?>;
	/*var bot = <?php echo $reload?>;
	if(bot == 1)
	{
		var xhr = getXmlHttp();
		xhr.open('GET', '/bot.php?num='+<?php echo $numOfGame?>, false);
		xhr.send();
	}*/
	if(reload == 1)
	{
		window.location.href = 'newgame.php';
	}
</script>

<table>
<form method="POST" action="" enctype="">
<?php
	$count = mysqli_query($mysqli,"SELECT max(id) FROM listOfGames");
	$count = mysqli_fetch_all($count, MYSQLI_NUM);
?>
<tr><td>Название</td><td><input type="text" autocomplete="off" name="nameOfGame" size="4" placeholder=<?php echo "game".($count[0][0]+1);;?>></td></tr>
<tr><td>Размер поля</td><td><input type="number" autocomplete="off" name="numOfSize" size="4" min="4" max="10" placeholder="5" value=<?php echo " "//$numOfSize;?> required="required"></td></tr>

<tr><td>Имена</td><td>
<?php
$res = mysqli_query($mysqli, "SELECT name, password FROM users");
$users = mysqli_fetch_all($res,MYSQLI_NUM);;?>
<select name="Players[]" size="4" multiple="multiple" required="required">
<?php 
	
	for($i=0;$i<$res->num_rows;$i++)
	{	if(strcmp($users[$i][0],$login) != 0  || $login === "bot")
		{
		?>
		<!--<option value=<?php //echo "\"".$users[$i][0]."\""; if(strcmp($users[$i][0],$login) == 0) echo "selected disabled";?>> <?php //echo $users[$i][0];
		?> </option>-->
		<option value=<?php echo "\"".$users[$i][0]."\"";?>> <?php echo $users[$i][0];?> </option>
<?php  }
	}	?>
</select></td></tr>
<tr><td colspan="2"><input type="submit" name="Submit" value="Начать новую игру!"/></td></tr>
</form>
</table>

<table>
<?php
$res = request($mysqli,"SELECT id, name FROM listOfGames");
	//$listOfGames=file("balda/ListOfGames.txt");
	$numPlayer;
	$countGames = mysqli_query($mysqli,"SELECT max(id) FROM listOfGames");
	$countGames = mysqli_fetch_all($countGames,MYSQLI_NUM);
	$countGames = $countGames[0][0];
	for($i=0;$i<$res->num_rows;$i++)
	{	//echo "$i <br>";
        $row = $res->fetch_assoc();
		echo "<tr>";
		$numG=$row['id'];
		$play = request($mysqli,"SELECT field FROM ".$row['name']);
		$game = mysqli_fetch_all($play,MYSQLI_NUM);
		$indexNum=0;
		while($game[$indexNum][0]<2)
			$indexNum++;
        $numOfPlayers =$game[$indexNum+1][0];
        $countGame=count($game);
		if(!checkGameB($play,$login,$numOfPlayers)/* || $game[$countGame-1][0] == -2*/)
		{	
			$numPlayer[$numG]=null;
			continue;
		}
        	
        $endOfGame = false;
        if($game[count($game)-1][0] == -2)
			$endOfGame = true;
		echo "<td><b><a href=\"balda.php?num=".$numG."\"  align = \"center\">".$row['name']."</a></b></td>";
		echo "<td>Начата ".date("F d Y H:i ", $game[$countGame-3][0]);
		echo "с игроком</td><td align=\"center\">";
		//echo $numOfPlayers."<br>";
		for($j=0;$j<$numOfPlayers;$j++)
		{
        	if(strcmp(trim($game[$j][0]),$login) != 0)
        	{
        		echo $game[$j][0]."<br>";
			} else {
				$numPlayer[$numG]=$j+1;
				//echo $numPlayer[$numG]." ".$numG."<br>";
			}
		}
		echo "</td>";
		if(!$endOfGame)
				echo "<td align=\"center\"><div id=\"statusOfG".$numG."\"align=\"center\" ></div></td>";
			else
				echo "<td align=\"center\">окончена</td>";
		$countGames = max($countGames,$numG);
		echo "</td><td>";
		?>
		<input type="submit" onclick=<?php echo "\"deleteG(this, '".$row['name']."')\"";?> id=<?php echo "\"Delete".$numG."\"";?> value="Удалить">
		<br>
		</td>
	</tr>
<?php } ?>
</table>
</div>
<div align = "left" >
<table>
<?php
$countS= count($status);
{
	$res = mysqli_query($mysqli, "SELECT name FROM users");
	$name = mysqli_fetch_all($res, MYSQLI_NUM);
	for($i=0;$i<$res->num_rows;$i++)
	{
		echo "<tr><td>".$name[$i][0]."</td><td><div id=\"user".$status[$i][1]."\" align=\"center\" ><p>         </p></div></td></tr>";
	}
}
?>
</table>
</div>
<?php
		/*$url = "https://sanstv.ru/randomWord/lang-ru/strong-2/count-1/word-";//%3F%3F%3F%3F%3F";
		for($i=0;$i<5;$i++)
			$url.="%3F";
		$content = getSslPage($url);
		writeFile("site.txt",$content,"w");

		$wordp = strpos($content, "ol class=");
		$defStop = strpos($content, "title",$wordp);
		$defStart = strpos($content, "<li title=\">",$wordp);
		$definition = substr($content,$defStart,$defStop-$defStart);
		echo "word=".$wordp." ".$defStart." ".$definition."<br>";
		$word="БАНК";
		$url="http://www.gramota.ru/slovari/dic/?bts=x&word=".mb_strtolower($word);
		$content = getSslPage($url);
		$content = iconv('CP1251', 'UTF-8',$content);
		$content = strip_tags($content);
		$Pstart = strpos($content,$word);
		$PStop = strpos($content,"О портале");
		echo substr($content, $Pstart, $Pstop-$Pstart+100);
		writeFile("site.txt", iconv('UTF-8', 'CP1251', substr($content, $Pstart, $Pstop-$Pstart)),"w");*/
?>
<script type="text/javascript">
	var numPlayer=[];
	var playA=true;
	var count=<?php echo $countGames+1;?>;
	console.log("count: "+ count);
	<?php
	for($i=0;$i<$countGames+1;$i++)
	{
		if($numPlayer[$i] != 0)
		{
			echo "numPlayer[$i]=".$numPlayer[$i].";";
		}		
	}
	?>
function curOnline() {
		// (1) создать объект для запроса к серверу
		var req = getXmlHttp()  
		req.onreadystatechange = function() {  
	        // onreadystatechange активируется при получении ответа сервера
			if (req.readyState == 4) { 

				if(req.status == 200) {
					var status=req.responseText;
					console.log(status+ ' '+status.length);
					var i = 0;
					for(i=0;i<status.length;i++)
					{	var idUserp=0;
						if(i!=0)
							idUserp=status.indexOf(';',i)+1;
						var index=status.indexOf(',',idUserp);
						var idUser=status.indexOf(';',index);
						var elem = document.getElementById('user'+status.substring(index+1,idUser));
						console.log(status.substring(index+1,idUser)+' '+index+ ' '+ idUser);
						if(elem)
						{
							elem.innerHTML=status.substring(idUserp,index);// + ' '+idUserp+ ' '+index;
							if(idUser != -1)
								i=idUser-1;
							else
								i=status.length;
						}
					}
				}
			}
		}
		req.open('GET', '/update.php?id='+id+'&type=1', true);
		req.send(null);
	}

	curOnline();
	setInterval(function()
		{
			curOnline();
		}, 60000);

	function FcurPlayer(numOfGame) {
			var audio = new Audio(); // Создаём новый элемент Audio
			audio.src = 'balda/04820.mp3'; // Указываем путь к звуку "клика"
			var req = getXmlHttp();
			req.onreadystatechange = function() {  
				if (req.readyState == 4) { 
					if(req.status == 200) { 
							var p=+req.responseText+1;
							console.log('p '+p);
							if(p != numPlayer[numOfGame])
							{
								document.getElementById('statusOfG'+numOfGame).innerHTML="<b>Ожидание хода</b>";
							} else {
								document.getElementById('statusOfG'+numOfGame).innerHTML="<b>Ваш ход!</b>";
								if(!playA)
								{
									audio.autoplay = true;
									//playA=true;
								}
									
							}
					}
				}
			}
			req.open('GET', '/update.php?num='+numOfGame, true);
			req.send(null);	
		}
	function updateCurPlayer()
	{
		for(var i=0;i<count;i++)
			{
				var elem = document.getElementById('statusOfG'+i);
				if(elem)
				{
					FcurPlayer(i);
				}
			}
	}
	updateCurPlayer();
	setInterval(function()
		{
			 updateCurPlayer();
		},20000);
</script>
</body>
</html>
<?php } ?>