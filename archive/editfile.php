<?php 
include("file.php");
include("forecast.php");
date_default_timezone_set('Europe/Minsk');
session_set_cookie_params(10000);
session_start();
$mysqli = new mysqli("127.0.0.1", "root","", "balda");
$res = mysqli_query($mysqli, "SELECT password FROM users WHERE id=1");
$adminPassword=trim(mysqli_fetch_all($res, MYSQLI_NUM)[0][0]);
if(!$_SESSION['access']  && !isset($_COOKIE["CookiePsw"])){
    header("location:index.php?page=Page.php?name=".$_GET["name"]);
} else if(!hash_equals($adminPassword, crypt($_COOKIE["CookiePsw"],$adminPassword)))
{
    header("location:index.php");
} else {
    $strFile;
	 if(isset($_POST["loadFile"]))
	 {
	 	$adrFile = trim($_POST["file"]);
        if(file_exists($adrFile))
        {
            $file=file($adrFile);
            $strFile;
            for($i=0;$i<count($file);$i++)
                $strFile.=iconv('CP1251', 'UTF-8', $file[$i]);
        } else
            $strFile="Такого файла не существует!";
	 }
   
    if(isset($_POST["saveText"]))
    {
        $adrFile = trim($_POST["file"]);
        $file=trim($_POST["fileText"]);
        if(!empty($file))
        {
            writeFile($adrFile,iconv('UTF-8', 'CP1251', $file),"w");
        } else {
            $file=file($adrFile);
            $strFile;
            for($i=0;$i<count($file);$i++)
                $strFile.=iconv('CP1251', 'UTF-8', $file[$i]);
        } 
    }
?>
<html>
<body>	
</form>
    <form method="POST" action="" enctype="">
    <input type="text" autocomplete="on" name="file" required value=<?php echo '"'.$adrFile.'"'?>>
    <input type= "submit" name="loadFile" value="Открыть"/><br>
    <textarea cols="32" rows="10" name="fileText"><?php echo $strFile;?></textarea><br>
    <input type= "submit" name="saveText" value="Сохранить" <?php if(empty($adrFile)) echo "disabled";?>/> 
</form>	
</body>
</html>
<?php }?>