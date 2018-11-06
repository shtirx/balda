<?php 
include("file.php");
include("forecast.php");
date_default_timezone_set('Europe/Minsk');
session_set_cookie_params(10000);
//setcookie("TestCookie", '1998nikita', time()+3600*24*1);
session_start();
if((!isset($_SESSION['access']) || $_SESSION['access']!=true) && $_COOKIE["TestCookie"] != '1998nikita'){
header("location:index.php");
}
else{
if(!file_exists("Pages")) //проверяется наличе существование папки и файлов в ней
    mkdir("Pages", 0700);
?>
<html>
<head>
<script>

</script>
	<title> info </title>
    <style type="text/css">
   .table1 { 
    padding: 5px;
    top:-200px;
   }
  </style>
  <style type="text/css">
   .clock { 
    width: 10%; 
    padding: 5px; 
    float: right; 
    position: absolute; 
    top: 15px; 
    left: 83%;
   }
  </style>
</head>
<body bgcolor= <?php echo "#FFCD00"/*.rand(0x00FFB7,0xFF0000)*/?> >
<?php 
/*$str = "";
$num = 0;
for($i = 0; $i<strlen($str); $i++)
{
    if($str[$i] !=' ')
        $num++;
}*/
echo $num;
$ExistedPage = 0;

    if(isset($_POST['Save'])) {
        $Name = trim($_POST['Name']);
        $SettingFolder = trim($_POST['SettingFolder']);
        $TimeUpdate = trim($_POST['TimeUpdate']);


        if(empty($Name))
            $Name = "Name";
        if(empty($TimeUpdate))
            $TimeUpdate = "off";

        if(!file_exists($Name)) //проверяется наличе существование папки и файлов в ней
            mkdir($Name, 0700);
        if(!file_exists($Name."settings")) //проверяется наличе существование папки и файлов в ней
            mkdir($Name."settings", 0700);

        $Fields;
        $n = 0;
        $start = -1;
        $NameField = trim($_POST['NameField']);
        for($i = 0; $i<strlen($NameField); $i++) 
        {
            if($NameField[$i] == ',')
            {
                $Fields[$n] = trim(substr($NameField, $start+1, $i-($start+1) ));
                $n++;
                //echo "start=$start i=$i<br>";
                $start = $i;
                
            }
        }
        $Fields[$n] = trim(substr($NameField, $start+1));

        if(!file_exists("Pages/".$Name.".txt"))
            writeFile("Pages/ListofPages.txt", $Name."\r\n", 'a');

        writeFile("Pages/".$Name.".txt", $Name."\r\n".($n+1)."\r\n".$TimeUpdate."\r\n".trim($_POST['archive'])."\r\n", 'w');
        for($i = 0; $i <= $n;$i++)
        {
            writeFile("Pages/".$Name.".txt", $Fields[$i]."\r\n", 'a');
            //echo $Fields[$i]."<br>";
        }
    }
?>
    </form>
    <font size="5">Create new page</font>
    <form method="POST" action="" enctype="">
    <input type="text" name="Name" placeholder="Имя"/><br>
    <input type="text" name="NameField" placeholder="имена полей"/><br>
    Период обновления <input type="checkbox" name="TimeUpdate"/><br>
    Архив <input type="checkbox" name="archive"/><br>
    <input type="submit" name="Save" value="OK" /> 
    </form>
</body>
<?php } ?>