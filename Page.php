<?php 
include("file.php");
include("forecast.php");
session_set_cookie_params(10000);
session_start();
date_default_timezone_set('Europe/Moscow');
$mysqli = new mysqli("127.0.0.1", "root","", "balda");
$res = mysqli_query($mysqli, "SELECT password FROM users WHERE id=1");
$adminPassword=trim(mysqli_fetch_all($res, MYSQLI_NUM)[0][0]);
if(!$_SESSION['access']  && !isset($_COOKIE["CookiePsw"])){
header("location:index.php?page=Page.php?name=".$_GET["name"]);
} else if(!hash_equals($adminPassword, crypt($_COOKIE["CookiePsw"],$adminPassword)))
{
    header("location:index.php");
} else { ?>
<html>
<head>
<title><?php echo $_GET["name"]; ?></title>
<style type="text/css">
    #box{display: none;}
    #containerCharts #TScharts{ display: none;}
    #box:checked + #containerCharts #TScharts{ display: block;}
    </style>
<script>
    function chartDate(){
        var date = document.getElementById("date");
        var dateF = document.getElementById("dateFinish");
        var timeType = document.getElementById("timeType");
        var Name = <?php echo "'".$_GET["name"]."'";?>;
        if(timeType.value == "сбросить")
            window.location.href = 'Page.php?name=' + Name;
         else{
            if(dateF.value == 0)
                dateF = date;
            //if(date.value == 0)
            //   date = dateF;
            window.location.href = 'Page.php?name=' + Name+ '&date=' + date.value + '&dateF=' + dateF.value;/*<?php echo "'http://94.19.250.166:96/Page.php?name=".$Name."'";?>*/
        }
        
    }
</script>
</head>
<body>
<?php
$PageSettings = file("Pages/".$_GET["name"].".txt");
for($i = 0; $i<count($PageSettings); $i++)
{
    $PageSettings[$i] = trim($PageSettings[$i]);
}
$Name = trim($PageSettings[0]);
//echo $Name."<br>";
$AmountData = trim($PageSettings[1]);
//echo $AmountData."<br>";
$TimeUpdate = trim($PageSettings[2]);

if(!file_exists($Name."settings")) //проверяется наличе существование папки и файлов в ней
    mkdir($Name."settings", 0700);


for($i =count($PageSettings)-$AmountData;$i < count($PageSettings)-1; $i++)
{
    //$name1 = $PageSettings[$i];
    //echo $name1."<br>";
    if(!file_exists($Name."settings/".$PageSettings[$i]."_data_max.txt"))
        writeFile($Name."settings/".$PageSettings[$i]."_data_max.txt", "", 'w'); 
    if(!file_exists($Name."settings/".$PageSettings[$i]."_data_min.txt"))
        writeFile($Name."settings/".$PageSettings[$i]."_data_min.txt", "", 'w');
    if(!file_exists($Name."settings/".$PageSettings[$i]."_data_round.txt"))
        writeFile($Name."settings/".$PageSettings[$i]."_data_round.txt", "", 'w');
}
 
if(!file_exists($Name."settings/count_t.txt"))
   writeFile($Name."settings/count_t.txt", "", 'w'); 
if(!file_exists($Name."settings/count.txt"))
    writeFile($Name."settings/count.txt", "", 'w');

if($TimeUpdate == "on")
{
    if(!file_exists($Name."settings/UpdateTime.txt"))
        writeFile($Name."settings/UpdateTime.txt", "", 'w');
    if(!file_exists($Name."settings/UpdateTimeCorect.txt"))
        writeFile($Name."settings/UpdateTimeCorect.txt", "", 'w');
}

$countTable = rNumFile($Name."settings/count_t.txt");
$countChart = rNumFile($Name."settings/count.txt");
$dateS;
if(!empty($_GET["date"]))
    $dateS = $_GET["date"];
else
    $dateS = 0;
$dateF;
if(!empty($_GET["dateF"]))
    $dateF = $_GET["dateF"];
else
    $dateF = 0;
$ds18b20 = ReadMassiveData($Name, $dateS, $dateF);
$countds18b20 = count($ds18b20);

        //for($i = 0; $i < count($ds18b20); $i++)
        //    echo $ds18b20[$i]."<br>";
//$countds18b20 = count($ds18b20);
//echo $count_max."<br>";
/**
*isset() - проверяет на наличие переменной/значения (равно NULL или нет)
*empty() - проверяет переменную на пустоту. Обращаю внимание, 0 - для нее тоже пустота!
**/

if(isset($_POST['change'])) { #если нажата клавиша формы 
$update_time = $_POST['update_time'];#присваиваем значение первого поля первой переменной
#Важный момент. все переменные, полученные от пользователя нужно проверять и очищать!
$update_time = trim($update_time); #убираем пробелы по краям, если они есть
$timef = $_POST['time'];
$timef = trim($timef);
$time_correct=$_POST['time_correct'];
$time_correct = trim($time_correct);
//$time = date("s",strtotime($Date[$jb]))-date("s",strtotime($Date[$jb-1]));
//$time = 2; date("s",strtotime($Date[$jb])-strtotime($Date[$jb-1]));
if(!empty($update_time))
        writeFile($Name."settings/UpdateTime.txt", $update_time*$timef, 'w');     
if(!empty($time_correct))
        writeFile($Name."settings/UpdateTimeCorect.txt", $time_correct, 'w');     
}

$file;// =  "Amount.txt";
if(isset($_POST['changeAmount'])) { 
$update_Amount = $_POST['amount'];
$n_Amount =$_POST['type_n'];
$n_Amount = trim($n_Amount);
switch ($n_Amount) {
    case 1:
        $file = $Name."settings/count_t.txt";
        break;
    case 2:
        $file = $Name."settings/count.txt";
        break;
    default:
        $file = $Name."settings/count.txt";
    break;
    }
$update_Amount = trim($update_Amount); 
if(!empty($update_Amount)) 
        writeFile($file, $update_Amount, 'w');
}

if(isset($_POST['settings'])) { 
$SettingValue = $_POST['SettingValue'];
$ChartSettings =$_POST['ChartSettings'];
$ChartSettings = trim($ChartSettings);
$ChartType =$_POST['ChartType'];
switch ($ChartSettings) {
    case 0:
        $file = $Name."settings/".$PageSettings[count($PageSettings)-$AmountData+$ChartType]."_data_max.txt";
        break;
    case 1:
        $file = $Name."settings/".$PageSettings[count($PageSettings)-$AmountData+$ChartType]."_data_min.txt";
        break;
    case 2:
        $file = $Name."settings/".$PageSettings[count($PageSettings)-$AmountData+$ChartType]."_data_round.txt";
        break;
    default:
    break;
    }
$SettingValue = trim($SettingValue); 
if(!empty($SettingValue)) 
        writeFile($file, $SettingValue, 'w');
}
?>
    <table align="left" >
        <tr>
            <td>
<div class="settings">
    <!--<a href="http://192.168.1.48:96/temp.php?T=value&&P=value">получить значение </a><br />-->

    </form>
    <font size="5">колличество значений: </font>
    <form method="POST" action="" enctype="">
    <input type="number" name="amount" placeholder=<?php echo '"'.$countChart.'"'; ?>/>
    <select name="type_n" size = "1"> 
    <option value="1">таблица</option> 
    <option selected value="2">график</option> 
    </select>
    <input type="submit" name="changeAmount" value="OK" /> 
    </form>
    <input type="date" id="date" value=<?php if(!empty($_GET["date"])) echo "\"".$_GET["date"]."\" disabled";?>>
    <input type="date" id="dateFinish" value=<?php if(!empty($_GET["dateF"])) echo "\"".$_GET["dateF"]."\" disabled";?>>
    <input id="timeType" type="button" onclick="chartDate()" value=<?php  if (!empty($_GET["date"])){echo "сбросить";} else {echo "установить";}?>><br>
    </form>
<?php 
//echo $TimeUpdate;
if($TimeUpdate == "on")
{?>
    <font size="5">Время обновления: </font><br />
    <form method="POST" action="" enctype="">
    <input type="number" name="update_time" placeholder=<?php echo '"Поправка: '.rNumFile($Name."settings/UpdateTime.txt").' сек"'; ?>/>
    <input type="number" name="time_correct" placeholder=<?php echo '"Поправка: '.rNumFile($Name."settings/UpdateTimeCorect.txt").'"'; ?>/>
    <select name="time" size = "1"> 
    <option value="1">секунд</option> 
    <option selected value="60">минут</option> 
    </select>
    <input type="submit" name="change" value="OK" />
    </br>
<?php } ?>
    <font size="5">Настройки: </font><br>
    <form method="POST" action="" enctype="">
    <input type="text" name="SettingValue" />
    <select name="ChartSettings" size = "1"> 
    <option value="0">Максимальное значение</option> 
    <option selected value="1">Минимальное значение</option> 
    <option value="2">Округление</option> 
    </select>
    <select name="ChartType" size = "1"> 
<?php
    for($i =count($PageSettings)-$AmountData, $n = 0;$i < count($PageSettings)-1; $i++, $n++)
    { ?>
    <option value=<?php echo '"'.$n.'"';?>><?php echo $PageSettings[$i][0];?></option> 
<?php }?>    
    </select>
    <input type="submit" name="settings" value="OK" /> 
    </form>
   
</div>
<?php
        $day;
        $time;
        $countds18b20 = count($ds18b20);
        $data;       
        
        $count = rNumFile($Name."settings/count.txt");
        $j = $countds18b20-($count)*$AmountData;
        $start = $j;
        $finish = $countds18b20;
        if (!empty($_GET["date"]))
        {
            $day = strtotime($_GET["date"]);
            $dayF = strtotime($_GET["dateF"]);
            //if($dateF < $day )//если даты перепутанны
            //{   $temp = $day;
            //    $day = $dayF;
            //    $dayF = $temp;
            //}
            $d = $countds18b20;
            //timeDataN($BMP085, time()-$day, 3, 2, 2) //$count_str - кол-во значений в строке; $count_n - номер  значения в строке; type - 1 относительное значение, 2 абсолютное
            while(strtotime($ds18b20[$d-1]) > ($dayF+86400))
                $d-=$AmountData;
            $finish = $d;//timeDataN($BMP085, time()-$dayF - 86400, 3, 2, 2) - 1;
            while(strtotime($ds18b20[$d-1]) > $day)
                $d-=$AmountData;
            $start = $d+$AmountData;//timeDataN($BMP085, time()-$day, 3, 2, 2) - 1;
            //echo strtotime($BMP085[$i-1]);
            $count = ($finish - $start)/$AmountData;
            $j = $start;
        }
        $Datamin;
        //$tableStart = $countds18b20-$count*2 - $finish;
        $time =strtotime($ds18b20[$finish-1]) - strtotime($ds18b20[$start-1]);
        for($i = 0; $i<=$count; $i++, $j+=$AmountData)
        {   
            $date;  
            if($time < 95000)
                $date = date("G:i",strtotime($ds18b20[$j-1]));
            else
                $date = date(" j F G:i, D",strtotime($ds18b20[$j-1]));

            for($countChart = 0, $n = count($PageSettings)-$AmountData; $countChart < $AmountData-1;$countChart++, $n++)
            {
                $T = $ds18b20[$j+$countChart-1-($AmountData-1)];
                $Tn = $ds18b20[$j+$countChart-1-(-1)];
                $Tp = $ds18b20[$j+$countChart-1-(2*$AmountData-1)];
                $DataType = $PageSettings[count($PageSettings)-$AmountData+$countChart];
                $Tt = timeData($ds18b20, (int)$ds18b20[$j-1] + 3*60*60, (int)$PageSettings[1], $countChart+1,1);
                if(($DataType[0]=="T" || $DataType=="HIC") && (abs($T-$Tp)>2.0 || abs($T-$Tn)>2.0 || ($T-$Tt)>10))
                {
                    //echo ($DataType[0]=="T" && abs($T-$Tp)>2)."<br>";
                    continue;
                }
                $dataMin =  (double)rNumFile($Name."settings/".$PageSettings[$n]."_data_min.txt");
                $dataMax = (double)rNumFile($Name."settings/".$PageSettings[$n]."_data_max.txt");
                
                //if($DataType[0]=="T")
                //    echo ($DataType[0]=="T")." ".$date." ".$T." ".$Tp." ".abs($T-$Tp)."<br>"; 
                if(($T > $dataMin) && ($T < $dataMax) )
                {
                   if($Datamin[$countChart] < $T)
                    $Datamin[$countChart] = $T;

                    $data[$countChart].=",['".$date."', ".$T."]"; 
                }
            }    
        }
    $time =strtotime($ds18b20[$countds18b20-1]) - strtotime($ds18b20[$countds18b20-1-(rNumFile($Name."settings/count.txt")-1)*$AmountData]);
    $hour = (int)($time/3600);
    $minutes = (int)(($time/60)%60);
    if (!empty($_GET["date"]))
        echo "График с ".date("j F",strtotime($_GET["date"]))." по ".date("j F",strtotime($_GET["dateF"]))."<br>";
    else
        echo "График за последние ".$hour." часов ".$minutes." минут<br>";

    //echo $finish." ".$start."</br>";
?>
<!--</td>-->
    <script type="text/javascript" src="charts.js"></script>
    <script type="text/javascript">

      google.charts.load('current', {packages: ['corechart', 'line']});
      google.charts.setOnLoadCallback(drawBackgroundColor);
<?php   

        
        $mobiles = array("Windows",/*"Nexus"*/);
        $width = 470;
        $height = 275;
        foreach( $mobiles as $mobile ) {
            if( preg_match( "#".$mobile."#i", $_SERVER['HTTP_USER_AGENT'] ) ) {
                $width = 650;
                $height = 275;
            }
        }
?>
 
function drawBackgroundColor() {
<?php   
    //$countChart = 1;
    for($countChart = 0, $i = count($PageSettings)-$AmountData; $countChart < $AmountData-1;$countChart++, $i++)
    { ?>
      var <?php echo "data".$countChart; ?> = google.visualization.arrayToDataTable([
          ['Time', <?php echo "'".$PageSettings[$i]."'"; ?>]
          <?php echo $data[$countChart]; ?>
        ]);

        var <?php echo "options".$countChart; ?> = {
          hAxis: {title: 'Time',  titleTextStyle: {color: '#333'}},
          vAxis: {title: <?php echo "'".$PageSettings[$i]."'"; ?>, minValue: <?php echo $Datamin[$countChart]; ?>},
          width:  <?php echo $width; ?>/*screen.width/2*/,
          height: <?php echo $height; ?>
        };


      var <?php echo "chart".$countChart; ?>  = new google.visualization.LineChart(document.getElementById(<?php echo "'Chart".$countChart."'"; ?>));
      <?php echo "chart".$countChart; ?>.draw(<?php echo "data".$countChart; ?>,  <?php echo "options".$countChart; ?>);   
<?php  }?>
}


    </script>
<input type="checkbox" id="box"/>
<label id="containerCharts" for="box">
    <b><div id="button" >Графики Thingspeak</div></b>
    <div id="TScharts">
<?php
    /*echo "<iframe width=\"450\" height=\"260\" style=\"border: 1px solid #cccccc;\" src=\"https://thingspeak.com/channels/83130/charts/1?bgcolor=%23ffffff&color=Green&dynamic=true&max=".rNumFile("ds18b20settings/data_max.txt")."&min=".rNumFile("ds18b20settings/data_min.txt")."&results=".rNumFile("ds18b20settings/count_ds18b20.txt")."&round=".rNumFile("ds18b20settings/data_round.txt")."&type=line\"></iframe>";*/
?>
    </div>
</label>    
<?php
    for($countChart = 0; $countChart < $AmountData-1;$countChart++)
        echo "<div id=\"Chart".$countChart."\"></div>";
?>    

</div></td><td>
    <div id='div_table1' class="graph">
        <table border="3" >
            <tr>
            <?php
                for($i =count($PageSettings)-$AmountData;$i < count($PageSettings); $i++)
                {
                    echo "<td>".$PageSettings[$i]." </td>";
                }
            ?>
               
            </tr>
            <?php
                if (!empty($_GET["date"]))//если задана конкретная дата для данных, то строится таблица для этого дня
                    $countds18b20 = $finish;
                for($n = 0; ($n<rNumFile($Name."settings/count_t.txt"))&&($j>=0); $n++,$countds18b20-=$AmountData)
                //for($j = rNumFile("Amount.txt"); $j>=0;$j--)
                {
                    echo "<tr>";
                    for($countTable = 0; $countTable < $AmountData-1; $countTable++)
                    {
                        echo "<td>".$ds18b20[$countds18b20+$countTable-1-($AmountData-1)]." </td>";
                        //echo "<td> Data".($countTable+1)."=".$ds18b20[$countds18b20+$countChart-($AmountData-1)]." </td>";
                        //echo $AmountData."<br>";
                    }
                    echo "<td>".$ds18b20[$countds18b20-1]."</td>";
                    echo "</tr>";
                }
            ?>
        </table>
    </div>
</td>
</tr>
</table>
</body>
</html>
<?php } ?>