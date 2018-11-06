<html>
<head>

</head>
<body>
<?php
include("passwordLibrary.php");
include("users.php");
user();
	$add=0;
	/*if(!file_exists("balda/users"))
		mkdir("balda/users", 0700);
	if(!file_exists("balda/users/next.txt"))
		writeFile("balda/users/next.txt",0,"w");*/
	if(isset($_POST["Submit"]))
	{
		$psw = trim($_POST["psw"]);
		$login=trim($_POST["Name"]);
		$pswAgain = trim($_POST["pswAgain"]);
		if(empty($psw) || empty($login) || empty($pswAgain))
		{
			echo "Некоторые поля пустые!";
		}else if(checkUser($psw,$login) != false)
			echo "Данный пароль или имя уже существует!";
		else if(strcmp($psw,$pswAgain) == 0)
		{
			/*$n=rNumFile("balda/users/next.txt");
			writeFile("balda/users/user".$n.".txt",$login."\r\n".
				crypt($psw)."\r\n","w");
			writeFile("balda/users/next.txt",++$n,"w");*/
			$mysqli = new mysqli("127.0.0.1", "root","", "balda");
			$hashPsw = crypt($psw);
			mysqli_query($mysqli,"INSERT INTO users VALUES (null, '$login','$hashPsw', null)");
			$add=1;
		}
	}
?>
<script>
	var add=<?php echo $add;?>;
	if(add==1)
		window.location.href = 'index.php';
</script>
<table>
<form method="POST" action="" enctype="">
<tr><td>Имя:            </td><td><input type="text" autocomplete="off" name="Name" required></td></tr>
<tr><td>Пароль:         </td><td><input type="password" autocomplete="off" name="psw" required><br></td></tr>
<tr><td>Пароль еще раз:</td><td><input type="password" autocomplete="off" name="pswAgain" required></td></tr>
<tr><td colspan="2"><input type="submit" name="Submit" value="Зарегистрироваться"/></td></tr>
<table>
</body>
</html>
