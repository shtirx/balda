<?php 
include("file.php");
session_set_cookie_params(10000);
setcookie("TestCookie", 'value', time()+60*60*60);
session_start();
if(!isset($_SESSION['access']) || $_SESSION['access']!=true){
header("location:index.php");
}
else{ 
?>
<html>
<head>
<title>ds18b20</title>
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
        if(timeType.value == "сбросить")
            window.location.href = 'http://94.19.250.166:8080/Rain.php';
         else{
            if(dateF.value == 0)
                dateF = date;
            //if(date.value == 0)
            //   date = dateF;
            window.location.href = 'http://94.19.250.166:8080/Rain.php?date=' + date.value + "&dateF=" + dateF.value;
        }
        
    }
</script>
</head>

<body>
<?php
if(!file_exists("Rainsettings")) //проверяется наличе существование папки и файлов в ней
    mkdir("Rainsettings", 0700);
if(!file_exists("Rainsettings/data_max.txt"))
    writeFile("Rainsettings/data_max.txt", "", 'w'); 
if(!file_exists("Rainsettings/data_min.txt"))
    writeFile("Rainsettings/data_min.txt", "", 'w'); 
if(!file_exists("Rainsettings/count_Raint.txt"))
    writeFile("Rainsettings/count_Raint.txt", "", 'w'); 
if(!file_exists("Rainsettings/count_Rain.txt"))
    writeFile("Rainsettings/count_Rain.txt", "", 'w');
if(!file_exists("Rainsettings/data_round.txt"))
    writeFile("Rainsettings/data_round.txt", "", 'w');  

$count_max;
if(rNumFile("Rainsettings/count_Raint.txt")>rNumFile("Rainsettings/count_Rain.txt"))
    $count_max = rNumFile("Rainsettings/count_Raint.txt");
else
    $count_max = rNumFile("Rainsettings/count_Rain.txt");

    $count_m;
    if (!empty($_GET["date"]))
        $count_m = date("n",strtotime($_GET["date"]) - 86400);//если задана конкретная дата, то считывается тот месяц
    else
        $count_m = date("n");

        $Rain;
        $countRain;
        while($countRain < $count_max*2)
        {   
            $Rainbuf = file("Rain/Rain_".$count_m."_".date("y").".txt");
            $countRain += count($Rainbuf);
            $count_m--;
        }
        $count_m++;
        
        for(; $count_m <= date("n"); $count_m++)
        {   
            $countRain1 = count($Rain);
            $Rainbuf = file("Rain/Rain_".$count_m."_".date("y").".txt");
            for($i = 0; $i < count($Rainbuf); $i++)
                $Rain[$i+$countRain1] = $Rainbuf[$i];      
        }

        //echo count($Rain);

        //for($i = 0; $i < count($ds18b20); $i++)
        //    echo $ds18b20[$i]."<br>";
//$countds18b20 = count($ds18b20);
//echo $count_max."<br>";
/**
*isset() - проверяет на наличие переменной/значения (равно NULL или нет)
*empty() - проверяет переменную на пустоту. Обращаю внимание, 0 - для нее тоже пустота!
**/
$file;// =  "Amount.txt";
if(isset($_POST['changeAmount'])) { 
$update_Amount = $_POST['amount'];
$n_Amount =$_POST['type_n'];
$n_Amount = trim($n_Amount);
switch ($n_Amount) {
    case 1:
        $file = "Rainsettings/count_Raint.txt";
        break;
    case 2:
        $file ="Rainsettings/count_Rain.txt";
        break;
    default:
        $file ="Rainsettings/count_Rain.txt";
    break;
    }
$update_Amount = trim($update_Amount); 
if(!empty($update_Amount)) 
        writeFile($file, $update_Amount, 'w');
}

if(isset($_GET['settings'])) { 
$SettingValue = $_GET['SettingValue'];
$ChartSettings =$_GET['ChartSettings'];
$ChartSettings = trim($ChartSettings);
switch ($ChartSettings) {
    case 0:
        $file = "Rainsettings/data_max.txt";
        break;
    case 1:
        $file = "Rainsettings/data_min.txt";
        break;
    case 2:
        $file = "Rainsettings/data_round.txt";
        break;
    default:
    break;
    }
$SettingValue = trim($SettingValue); 
if(!empty($SettingValue)) 
        writeFile($file, $SettingValue, 'w');
}
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
        writeFile("UpdateTime.txt", $update_time*$timef, 'w');     
if(!empty($time_correct))
        writeFile("UpdateTimeCorect.txt", $time_correct, 'w');     
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
    <input type="number" name="amount" />
    <select name="type_n" size = "1"> 
    <option value="1">таблица</option> 
    <option selected value="2">график</option> 
    </select>
    <input type="submit" name="changeAmount" value="OK" /> 
    </form>
    <input type="date" id="date" value="Текст">
    <input type="date" id="dateFinish" value="Текст">
    <input id="timeType" type="button" onclick="chartDate()" value=<?php  if (!empty($_GET["date"])){echo "сбросить";} else {echo "установить";}?>><br>

    <font size="5">Время обновления: </font><br />
    <form method="POST" action="" enctype="">
    <input type="number" name="update_time" />
    <input type="number" name="time_correct" placeholder=<?php echo '"Поправка: '.rNumFile("UpdateTimeCorect.txt").'"'; ?>/>
    <select name="time" size = "1"> 
    <option value="1">секунд</option> 
    <option selected value="60">минут</option> 
    </select>
    <input type="submit" name="change" value="OK" />

<?php
    echo "</br>Время обновления: ";
    $update = rNumfile("UpdateTime.txt");
    if($update%60 == 0)
        echo ($update/60)." минут";
    else
        echo $update." секунд";
?>

    </form>
    <font size="5">Настройки: </font>
    <form method="GET" action="" enctype="">
    <input type="text" name="SettingValue" />
    <select name="ChartSettings" size = "1"> 
    <option value="0">Максимальное значение</option> 
    <option selected value="1">Минимальное значение</option> 
    <option value="2">Округление</option> 
    </select>
    <input type="submit" name="settings" value="OK" /> 
    </form>
   
</div>
<?php
        $day;
        $time;
        $countRain = count($Rain);
        $dataRain;       
        $start = 0;
        $finish = 0;
        $count = rNumFile("Rainsettings/count_Rain.txt") - 1;
        $j = $countRain-$count*2;
        if (!empty($_GET["date"]))
        {
            $day = strtotime($_GET["date"]);
            $dayF = strtotime($_GET["dateF"]);
            //if($dateF < $day )//если даты перепутанны
            //{   $temp = $day;
            //    $day = $dayF;
            //    $dayF = $temp;
            //}
            $d = $countRain;
            //timeDataN($BMP085, time()-$day, 3, 2, 2) //$count_str - кол-во значений в строке; $count_n - номер  значения в строке; type - 1 относительное значение, 2 абсолютное
            while(strtotime($Rain[$d-1]) > ($dayF+86400))
                $d-=2;
            $finish = $d;//timeDataN($BMP085, time()-$dayF - 86400, 3, 2, 2) - 1;
            while(strtotime($Rain[$d-1]) > $day)
                $d-=2;
            $start = $d+2;//timeDataN($BMP085, time()-$day, 3, 2, 2) - 1;
            //echo strtotime($BMP085[$i-1]);
            $count = ($finish - $start)/2;
            $j = $start;
        }
        else {
            $finish = $countRain;
            $start = $countRain-(rNumFile("Rainsettings/count_Rain.txt")-1)*2;
        }
        $Rainmin = 0;
        //$tableStart = $countds18b20-$count*2 - $finish;
        $time = strtotime($Rain[$finish-1]) - strtotime($Rain[$start-1]);
        for($i = 0/*, $j = $countBMP085-$count*3 - $start*/; $i<=$count; $i++, $j+=2)
        {
            $date;  
            if($time < 95000)
                $date = date("G:i",strtotime($Rain[$j-1]));
            else
                $date = date(" j F G:i, D",strtotime($Rain[$j-1]));

            $rain = $Rain[$j-2];
            //if(abs($T-$Rain[$j-4]) < 4 && $Rain < rNumFile("Rainsettings/data_max.txt") && $Rain > rNumFile("Rainsettings/data_min.txt"))//фильтрация неверных данных
            {
                if($Rainmin < $rain)
                    $Rainmin = $rain;

                $dataRain.=",['".$date."', ".$rain."]";
            }  
        }

    $time =strtotime($Rain[$countRain-1]) - strtotime($Rain[$countRain-1-(rNumFile("Rainsettings/count_Rain.txt")-1)*2]);
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

      var dataRain = google.visualization.arrayToDataTable([
          ['Time', 'Temperature']
          <?php echo $dataRain; ?>
        ]);

        var optionsRain = {
          hAxis: {title: 'Time',  titleTextStyle: {color: '#333'}},
          vAxis: {title: 'Temperature', minValue: <?php echo $Rainmin; ?>},
          width:  <?php echo $width; ?>/*screen.width/2*/,
          height: <?php echo $height; ?>
        };


      var chartRain = new google.visualization.LineChart(document.getElementById('RainTemperature_chart'));
      chartRain.draw(dataRain, optionsRain);
    }

    </script>
<input type="checkbox" id="box"/>
<label id="containerCharts" for="box">
    <b><div id="button" >Графики Thingspeak</div></b>
    <div id="TScharts">
<?php
    echo "<iframe width=\"450\" height=\"260\" style=\"border: 1px solid #cccccc;\" src=\"https://thingspeak.com/channels/100957/charts/3?bgcolor=%23ffffff&color=%23d62020&dynamic=true&max=".rNumFile("Rainsettings/data_max.txt")."&min=".rNumFile("Rainsettings/data_min.txt")."&results=".rNumFile("Rainsettings/count_Rain.txt")."&round=".rNumFile("Rainsettings/data_round.txt")."&type=line\"></iframe><br>";
?>
    </div>
</label>    

    <div id="RainTemperature_chart"></div>
<?php
//echo $dataT;
echo /*"<td>*/"<div id='div_table1' class=\"graph\">";
echo "</div></td>";
    echo "<td><div class=\"value_table\"><table border=\"3\" align=\"center\">";
    echo  "<tr> 
            <td> Rain </td>
            <td> Date </td>
        </tr>";
    echo "<tr>";
            if (!empty($_GET["date"]))//если задана конкретная дата для данных, то строится таблица для этого дня
                $countRain = $finish;
            for($n = 0; ($n<rNumFile("Rainsettings/count_Raint.txt"))&&($j>=0); $n++,$countRain-=2)
            //for($j = rNumFile("Amount.txt"); $j>=0;$j--)
            {
                echo "<td>".$Rain[$countRain-2]."</td>";
                echo "<td>".$Rain[$countRain-1]."</td></tr>";
            }
    echo "</table></div></td></tr>";
?>
</body>
</html>
<?php } ?>