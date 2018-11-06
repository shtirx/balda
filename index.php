<?php
include("passwordLibrary.php");
include("users.php");
function location($login,$temp)
{
  if(strcmp($login,$temp)==0)
  {
    //echo "1<br>";
    if(!empty($_GET['page']))
      header("location:".$_GET['page']);
    else
     header("location:info.php");
  } else {
    if(!empty($_GET['page']))
      header("location:".$_GET['page']);
    else
      header("location:newgame.php");
    //echo "0<br>";
  }
}
$numOfGame=0;
if(!empty($_GET["num"]))
{
  $numOfGame=$_GET["num"];
}
date_default_timezone_set('Europe/Minsk');
session_set_cookie_params(10000);
session_start();
$login="";
if(isset($_COOKIE["CookiePsw"]))
  $login=trim(checkPsw($_COOKIE["CookiePsw"]));

$login=trim($login);
user($login);
/*$temp=file("balda/users/user0.txt");
$temp[0]=trim($temp[0]);
$temp[1]=trim($temp[1]);*/
$mysqli = new mysqli("127.0.0.1", "root","", "balda");
$res = mysqli_query($mysqli, "SELECT name FROM users WHERE id=1");
$admin = mysqli_fetch_all($res,MYSQLI_NUM);
if((isset($_SESSION['access']) && $_SESSION['access']) || !empty($login))
{
  location($login,$admin[0][0]); 
} else {
  if(!empty($_POST['paswd']) && !empty($_POST['login'])){

      if(checkUserSing($_POST['paswd'],$_POST['login'])){
        session_set_cookie_params(10000);
        session_start();
        $_SESSION['access']=true;
        setcookie("CookiePsw", $_POST['paswd'], time()+3600*24*14);
        $login=trim(checkPsw($_POST['paswd']));
        location($login,$admin[0][0]);  
        //echo "2<br>";   
      }
        else {
        echo "неверные имя или пароль";
      }
  }
  else
  {
    user();
    ?>
    <!DOCTYPE html>
    <head>
      <title> войти </title>
    </head>
    <body>
    <form method="POST">
    <table>
      <tr><td>Имя:    </td><td><input type="text" name="login"></td></tr>
      <tr><td>Пароль: </td><td><input type="password" name="paswd"></td></tr>
      <tr><td colspan="2"> <input type="submit"></td></tr>
    </table>
    </form>
    <b></b><a href="registration.php">Регистрация</a><br>
  </body>
    <?php
  }
?>
<?php }?>
