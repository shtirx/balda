<?php 
include("file.php");
include("forecast.php");
date_default_timezone_set('Europe/Minsk');
session_set_cookie_params(10000);
setcookie("TestCookie", 'value', time()+60*60*60);
session_start();
if(!isset($_SESSION['access']) || $_SESSION['access']!=true){
header("location:index.php");}
else{ 
?>
<html>
<head>
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
            window.location.href = 'http://94.19.250.166:8080/first.php';
        else{
            if(dateF.value == 0)
                dateF = date;
            //if(date.value == 0)
            //   date = dateF;
            window.location.href = 'http://94.19.250.166:8080/first.php?date=' + date.value + "&dateF=" + dateF.value;
        } 
        
    }
</script>
<title>BMP085</title>
</head>
<body>
<?php
if(!file_exists("BMP085settings")) //проверяется наличе существование папки и файлов в ней
    mkdir("BMP085settings", 0700);
if(!file_exists("BMP085settings/data_maxTemperature.txt"))
    writeFile("BMP085settings/data_maxTemperature.txt", "", 'w'); 
if(!file_exists("BMP085settings/data_minTemperature.txt"))
    writeFile("BMP085settings/data_minTemperature.txt", "", 'w'); 
if(!file_exists("BMP085settings/count_BMP085t.txt"))
    writeFile("BMP085settings/count_BMP085t.txt", "10", 'w'); 
if(!file_exists("BMP085settings/count_BMP085.txt"))
    writeFile("BMP085settings/count_BMP085.txt", "10", 'w');
if(!file_exists("BMP085settings/data_roundTemperature.txt"))
    writeFile("BMP085settings/data_roundTemperature.txt", "", 'w');

if(!file_exists("BMP085settings/data_maxPressure.txt"))
    writeFile("BMP085settings/data_maxPressure.txt", "", 'w'); 
if(!file_exists("BMP085settings/data_minPressure.txt"))
    writeFile("BMP085settings/data_minPressure.txt", "", 'w'); 
if(!file_exists("BMP085settings/data_roundPressure.txt"))
    writeFile("BMP085settings/data_roundPressure.txt", "", 'w');  

$count_max;
if(rNumFile("BMP085settings/count_BMP085t.txt")>rNumFile("BMP085settings/count_BMP085.txt"))
    $count_max = rNumFile("BMP085settings/count_BMP085t.txt");
else
    $count_max = rNumFile("BMP085settings/count_BMP085.txt");

    $count_m;
    $count_y;
    if (!empty($_GET["date"]))
    {
        $count_m = date("n",strtotime($_GET["date"]) - 86400);//если задана конкретная дата, то считывается тот месяц
        $count_y = date("y",strtotime($_GET["date"]) - 86400);//если задана конкретная дата, то считывается тот месяц
    }    
    else{
        $count_m = date("n");
        $count_y = date("y");
    }
        
    $BMP085;// = file("BMP085/BMP085_".$count_m."_".date("y").".txt");
    $countBMP085;// = count($BMP085);
    while($countBMP085 < $count_max*3)
    {   if($count_m == 0)
        {
            $count_m = 12;
            $count_y--;
        }
        $BMP085buf = file("BMP085/BMP085_".$count_m."_".$count_y.".txt");
        $countBMP085 += count($BMP085buf);
        $count_m--;
    }
    $count_m++;
    for(; $count_m <= date("n"); $count_m++)
    {   
        if($count_m == 13)
        {
            $count_m = 11;
            $count_y++;
        }
        $countBMP0851 = count($BMP085);
        $BMP085buf = file("BMP085/BMP085_".$count_m."_".$count_y.".txt");
        for($i = 0; $i < count($BMP085buf); $i++)
            $BMP085[$i+$countBMP0851] = $BMP085buf[$i];
    }

    //$correct; //корректировка задержки
    //if($countBMP085%4)
    //    $correct = 7;
    //else
    //    $correct = 11;
    //writeFile("UpdateTimeCorect.txt", $correct, 'w');
/**
*isset() - проверяет на наличие переменной/значения (равно NULL или нет)
*empty() - проверяет переменную на пустоту. Обращаю внимание, 0 - для нее тоже пустота!
**/
//Запись задержки в файл
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

// Запись колличества точек в файл
$file;// =  "Amount.txt";
if(isset($_POST['changeAmount'])) { 
$update_Amount = $_POST['amount'];
$n_Amount =$_POST['type_n'];
$n_Amount = trim($n_Amount);
switch ($n_Amount) {
    case 1:
        $file = "BMP085settings/count_BMP085t.txt";
        break;
    case 2:
        $file = "BMP085settings/count_BMP085.txt";
        break;
    default:
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
$ChartType =$_GET['ChartType'];
$ChartType = trim($ChartType);
$ChartSettings+= $ChartType;
switch ($ChartSettings) {
    case 0:
        $file = "BMP085settings/data_maxTemperature.txt";
        break;
    case 1:
        $file = "BMP085settings/data_minTemperature.txt";
        break;
    case 2:
        $file = "BMP085settings/data_roundTemperature.txt";
        break;
    case 3:
        $file = "BMP085settings/data_maxPressure.txt";
        break;
    case 4:
        $file = "BMP085settings/data_minPressure.txt";
        break;
    case 5:
        $file = "BMP085settings/data_roundPressure.txt";
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
    </form>
    <font size="5">колличество значений: </font>
    <form method="POST" action="" enctype="">
    <input type="number" name="amount" placeholder=<?php echo '"'.rNumFile("BMP085settings/count_BMP085.txt").'"'; ?> />
    <select name="type_n" size = "1"> 
    <option value="1">таблица</option> 
    <option selected value="2">график</option> 
    </select>
    <input type="submit" name="changeAmount" value="OK" /> 
    </form>
    <input type="date" id="date" value="Текст">
    <input type="date" id="dateFinish" value="Текст">
    <input id="timeType" type="button" onclick="chartDate()" value=<?php  if (!empty($_GET["date"])){echo "сбросить";} else {echo "установить";}?>><br>
<?php
    //Период времени, отраженный на графике и твыборка точек для графика в зависимости от времени
    $day;
    $time;
    $countBMP085 = count($BMP085);
    $data;
    $dataT;       
    $start = 0;
    $finish = 0;
    $count = rNumFile("BMP085settings/count_BMP085.txt") - 1;
    $j = $countBMP085-$count*3;
    if (!empty($_GET["date"]))
    {
        $day = strtotime($_GET["date"]);
        $dayF = strtotime($_GET["dateF"]);
        //if($dateF < $day )//если даты перепутанны
        //{   $temp = $day;
        //    $day = $dayF;
        //    $dayF = $temp;
        //}
        $d = $countBMP085;
        //timeDataN($BMP085, time()-$day, 3, 2, 2) //$count_str - кол-во значений в строке; $count_n - номер  значения в строке; type - 1 относительное значение, 2 абсолютное
        while(strtotime($BMP085[$d-1]) > ($dayF+86400))
            $d-=3;
        $finish = $d;//timeDataN($BMP085, time()-$dayF - 86400, 3, 2, 2) - 1;
        while(strtotime($BMP085[$d-1]) > $day)
            $d-=3;
        $start = $d+3;//timeDataN($BMP085, time()-$day, 3, 2, 2) - 1;
        //echo strtotime($BMP085[$i-1]);
        $count = ($finish - $start)/3;
        $j = $start;
    }
    else {
        $finish = $countBMP085;
        $start = $countBMP085-(rNumFile("BMP085settings/count_BMP085.txt")-1)*3;
    }
    $Pmin = 0;
    $Tmin = 0;
    //$tableStart = $countBMP085-$count*3 - $finish;
    $time =strtotime($BMP085[$finish-1]) - strtotime($BMP085[$start-1]);
    for($i = 0/*, $j = $countBMP085-$count*3 - $start*/; $i<=$count; $i++, $j+=3)
    {
        $date;  
        if($time < 90000)
            $date = date("G:i",strtotime($BMP085[$j-1]));
        else
            $date = date(" j F G:i, D",strtotime($BMP085[$j-1]));


        $P = $BMP085[$j-2];
        if(abs($P-$BMP085[$j-5]) < 0.8 && $P < rNumFile("BMP085settings/data_maxPressure.txt") && $P > rNumFile("BMP085settings/data_minPressure.txt"))//фильтрация неверных данных
        {
            if($Pmin < $P)
                $Pmin = $P;
            $data.=",['".$date."', ".$P."]";
        }

        $T = $BMP085[$j-3];
        if(abs($T-$BMP085[$j-6]) < 1.5 && $T <rNumFile("BMP085settings/data_maxTemperature.txt")&& $T > rNumFile("BMP085settings/data_minTemperature.txt"))//фильтрация неверных данных
        {
            if($Tmin < $T)
                $Tmin = $T;
            $dataT.=",['".$date."', ".$T."]";
        }
        

        
    }

    $hour = (int)($time/3600);
    $minutes = (int)(($time/60)%60);
    if (!empty($_GET["date"]))
        echo "График с ".date("j F",strtotime($_GET["date"]))." по ".date("j F",strtotime($_GET["dateF"]))."<br>";
    else
        echo "График за последние ".$hour." часов ".$minutes." минут<br>";
?>
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
    <option value="1">Минимальное значение</option> 
    <option value="2">Округление</option> 
    </select>
    <select name="ChartType" size = "1"> 
    <option value="0">T</option> 
    <option value="3">P</option>
    </select>
    <input type="submit" name="settings" value="OK" /> 
    </form> 
</div>

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
      var data = google.visualization.arrayToDataTable([
          ['Time', 'Pressure']
          <?php echo $data; ?>
          
          /*['2013',  1000],
          ['2014',  1170],
          ['2015',  660],
          ['2016',  1030]*/
        ]);

      var dataT = google.visualization.arrayToDataTable([
          ['Time', 'Temperature']
          <?php echo $dataT; ?>
        ]);
    

      var options = {
          hAxis: {title: 'Time',  titleTextStyle: {color: '#333'}},
          vAxis: {title: 'Pressure', minValue: <?php echo $Pmin; ?>},
          width: <?php echo $width; ?>/*screen.width/2*/,
          height: <?php echo $height; ?>
        };

        var optionsT = {
          hAxis: {title: 'Time',  titleTextStyle: {color: '#333'}},
          vAxis: {title: 'Temperature', minValue: <?php echo $Tmin; ?>},
          width:  <?php echo $width; ?>/*screen.width/2*/,
          height: <?php echo $height; ?>
        };

      var chart = new google.visualization.LineChart(document.getElementById('BMP085Pressure_chart'));
      chart.draw(data, options);

      var chartT = new google.visualization.LineChart(document.getElementById('BMP085Temperature_chart'));
      chartT.draw(dataT, optionsT);
    }

    </script>

<input type="checkbox" id="box"/>
<label id="containerCharts" for="box">
    <b><div id="button" >Графики Thingspeak</div></b>
    <div id="TScharts">
<?php
    echo "<iframe width=\"450\" height=\"260\" style=\"border: 1px solid #cccccc;\" src=\"https://thingspeak.com/channels/100957/charts/2?bgcolor=%23ffffff&color=%23d62020&dynamic=true&max=".rNumFile("BMP085settings/data_maxPressure.txt")."&min=".rNumFile("BMP085settings/data_minPressure.txt")."&results=".rNumFile("BMP085settings/count_BMP085.txt")."&round=".rNumFile("BMP085settings/data_roundPressure.txt")."&type=line\"></iframe><br>";
    echo "<iframe width=\"450\" height=\"260\" style=\"border: 1px solid #cccccc;\" src=\"https://thingspeak.com/channels/100957/charts/1?bgcolor=%23ffffff&color=%23d62020&dynamic=true&max=".rNumFile("BMP085settings/data_maxTemperature.txt")."&min=".rNumFile("BMP085settings/data_minTemperature.txt")."&results=".rNumFile("BMP085settings/count_BMP085.txt")."&round=".rNumFile("BMP085settings/data_roundTemperature.txt")."&type=line\"></iframe>";
?>
    </div>
</label>

    <div id="BMP085Pressure_chart" ></div> <!--style = "width:500; height:300"-->   
    <div id="BMP085Temperature_chart"></div>
<td><div id='div_table1' class="graph">
<?php
echo "</div></td>";
	echo "<td><div class=\"value_table\"><table border=\"3\" align=\"center\">";
	echo  "<tr> 
			<td> Temperature </td>
			<td> Pressure </td>
			<td> Date </td>
		</tr>";
	echo "<tr>";

            if (!empty($_GET["date"]))//если задана конкретная дата для данных, то строится таблица для этого дня
                $countBMP085 = $finish;

    		for($n = 0; ($n<rNumFile("BMP085settings/count_BMP085t.txt"))&&($countBMP085>=0); $n++,$countBMP085-=3)
            //for($j = rNumFile("Amount.txt"); $j>=0;$j--)
    		{ foreach( $mobiles as $mobile ) {
                    if( preg_match( "#".$mobile."#i", $_SERVER['HTTP_USER_AGENT'] ) ) {
                        echo "<td>T=".$BMP085[$countBMP085-3]."</td>"; //T
                        echo "<td>P=".$BMP085[$countBMP085-2]."</td>"; //P
                        echo "<td>".$BMP085[$countBMP085-1]."</td></tr>";//Date
                    } else {
                        echo "<td>".$BMP085[$countBMP085-3]."</td>"; //T
                        echo "<td>".$BMP085[$countBMP085-2]."</td>"; //P
                        echo "<td>".$BMP085[$countBMP085-1]."</td></tr>";//Date
                    }
                }
    			
    		}
	echo "</table></div></td></tr>";
?>
</body>
</html>
<?php } ?>