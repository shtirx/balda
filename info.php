<?php 
include("file.php");
include("forecast.php");
date_default_timezone_set('Europe/Moscow');
session_set_cookie_params(10000);
session_start();
$mysqli = new mysqli("127.0.0.1", "root","", "balda");
$res = mysqli_query($mysqli, "SELECT password FROM users WHERE id=1");
$adminPassword=trim(mysqli_fetch_all($res, MYSQLI_NUM)[0][0]);
if(!$_SESSION['access']  && !isset($_COOKIE["CookiePsw"])){
    header("location:index.php?page=info.php");
} else if (!hash_equals($adminPassword, crypt($_COOKIE["CookiePsw"],$adminPassword)))
{
    header("location:index.php");
}
else{
//echo $_COOKIE["TestCookie"]; 
?>
<html>
<head>
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
<div class="clock" align = "right" >   
<?php
    if(!file_exists("PW/".season()."/stable")) //проверяется наличе существование папки и файлов в ней
        mkdir("PW/".season()."/stable", 0700);
    if(!file_exists("PW/".season()."/up"))
        mkdir("PW/".season()."/up", 0700);
    if(!file_exists("PW/".season()."/down"))
        mkdir("PW/".season()."/down", 0700);
    if(!file_exists("rightForecast/"))
        mkdir("rightForecast/", 0644);    
    //Here is the known mobile user agents list
    $mobiles = array("Windows",/*"Nexus"*/);

    foreach( $mobiles as $mobile ) {
        if( preg_match( "#".$mobile."#i", $_SERVER['HTTP_USER_AGENT'] ) ) {
            //Ok, this is a mobile browser, let's redirect it!
            //header('Location:http://mobile.mysite.com/'); 
            //exit();
            echo " <iframe frameborder=\"no\" scrolling=\"no\" style=\"width:150px;height:280px;\" src=\"https://yandex.ru/time/widget/?geoid=2&lang=ru&layout=vert&type=analog&face=digits\"></iframe>";
        }
    }
    if(isset($_POST['updateAccarity'])) { #если нажата клавиша формы
            $acc3 =  file("rightForecast/Accuarity3.txt");
            $acc6 =  file("rightForecast/Accuarity6.txt");
            $timeF = $_POST['timeF'];
            
            if($timeF != 1)
            {
                $accuarity = accuarity($timeF);
                writeFile("rightForecast/Accuarity$timeF.txt", time()."\r\n".$accuarity, 'w');
            } 
            else
                for($hour=3;$hour<=12;$hour+=3)
                {
                    $accuarity = accuarity($hour);
                    writeFile("rightForecast/Accuarity$hour.txt", time()."\r\n".$accuarity, 'w');
                }
                    
        }
?>
</div>
<div class="table1" align = "center" > <!--style="color: #DBEB00"--> 
<?php
//accuracyForecast();
$ListofPages = file("Pages/ListofPages.txt");

for($i = 0; $i<count($ListofPages); $i++)
{
   echo printData(trim($ListofPages[$i]));
}

$BMP085;
$BMP085 = ReadMassiveData("BMP085", 0, 0);
$countBMP085 = count($BMP085);
//$wind_dir_text_uk = array("N", "NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW", "SW", "WSW", "W", "WNW", "NW", "NNW", "FALSE");

// Переменные $abs_pressure, $abs_pressure_1h, $wind_dir_avg, $wh_temp_out берутся из базы данных.
// Процесс заполнения базы и выборки из нее подробно рассмотрен в предыдущей статье,
// поэтому здесь опускаем эту часть кода

// Бесхитростное определение тенденции в изменении давления
// Здесь и в основной функции значения переведены из мм.рт.ст в гПа

//$pressure_trend=trendPressure($BMP085[$countBMP085-2], timeData($BMP085, 60*60, 3, 2, 1),10,1);
/*$velocity=(float)($BMP085[$countBMP085-2]-$BMP085[$countBMP085-6-2])/(strtotime($BMP085[$countBMP085-1])-strtotime($BMP085[$countBMP085-6-1]))*3600;
$DHT21 = ReadMassiveData("DHT21");
$countDHT21 = count($DHT21);
$weatherDB = forecastDB($BMP085[$countBMP085-2], $velocity,$DHT21[$countDHT21-4],$DHT21[$countDHT21-3],3);
echo $BMP085[$countBMP085-2]." ".round($velocity,3)." ".$DHT21[$countDHT21-4]." ".$DHT21[$countDHT21-3]." на ".date("H:i", time()+3*3600).": $weatherDB<br>";
print_r($weatherDB);*/
$abs_pressure = $BMP085[$countBMP085-2]*1.33322;
$abs_pressure_1h = timeData($BMP085, 60*60, 3, 2, 1)*1.33322;
if ( $abs_pressure > $abs_pressure_1h + 0.25)
{
    $pressure_trend = 1;
    $pressure_trend_text = "Рост";
}
elseif ( $abs_pressure_1h > $abs_pressure + 0.25)
{
    $pressure_trend = 2;
    $pressure_trend_text = "Падение";
}
else
{
    $pressure_trend = 0;
    $pressure_trend_text = "Не меняется";
}

$forecast = betel_cast($abs_pressure, date('n'), "FALSE", $pressure_trend, 1, 1050, 950, 20);

echo "Тенденция давления: $pressure_trend_text<br>";
echo "Прогноз: ".$forecast."<br>";

$url1 = "http://www.pogodaonline.ru/weather/maps/city?LANG=ru&CEL=C&SI=kph&MAPS=over&CONT=ruru&LAND=RS&REGION=0006&WMO=26063&UP=0&R=0&LEVEL=140&NOREGION=1";
$content1 = getSslPage($url1);

$posTime1=strpos($content1, "MSK");
$posTimeF1=strpos($content1, "/td>", $posTime1);
$curTime1=substr($content1, $posTime1+6, $posTimeF1-$posTime1-7);

$posStart1=strpos($content1, "<b>погода</b>",$posTime1);
$posStart1=strpos($content1, "<tr>",$posStart1);
$posStart1=strpos($content1, "<td>",$posStart1);
$posStart1=strpos($content1, "<td>",$posStart1+5);
$posFinish1=strpos($content1, "</td>",$posStart1+5);
$sdvig = 0;
$curWeather1=substr($content1, $posStart1+4+$sdvig, $posFinish1-$posStart1-4+$sdvig);
unset($sdvig);
$url2 = "http://www.pogodaonline.ru/weather/maps/city?LANG=ru&SI=kph&CEL=C&WMO=26063&TIME=std&CONT=ruru&R=0&LEVEL=140&REGION=0006&LAND=RS&ART=feuchte&NOREGION=1&PLZ=&PLZN=&SORT=__&TEMP=___&WETTER=__&&TYP=__&SEITE=0";
$content1 = getSslPage($url2);
$posStart1=strpos($content1, "<b>относительная влажность</b>",$posTime1);
$posStart1=strpos($content1, "<td>",$posStart1+120);
$posFinish1=strpos($content1, "</td>",$posStart1+5);
$curHumidity=substr($content1, $posStart1+4, $posFinish1-$posStart1-4);
//writeFile("site.txt",$curTime1." ".$curWeather1,'w');
//echo "currentW $curWeather1<br>"; 
echo "<b>Фактическая погода: </b><a href=\"$url1\">".strip_tags($curWeather1).", влажность: $curHumidity ($curTime1)</a> </br>"; 
//echo "<b>Фактическая погода: </b><a href=\"http://www.foreca.ru/Russia/Saint_Petersburg\">".$curWeather." ($curTime)</a>, ".$curT/* ." ".$curVelocity*/.", влажность ".$curHumidity."</br>"; 
unset($content1);// удаляет content

?>
    <td><div class="value_table"><table border="3" align="center">
    <tr> 
        <td><b>Время</b></td>
        <th colspan="2">Прогноз</th>
        <td><b>Точность</b></td>
    </tr>
    <tr>
<?php 
    //$mysqliF = new mysqli("127.0.0.1", "root","", "weather");
    // $resF = mysqli_query($mysqliF,"SELECT `forecast`, `time`FROM forecast WHERE id>=((SELECT MAX(id) FROM forecast)-3) ORDER BY id ASC");
    //$forecastDB = mysqli_fetch_all($resF, MYSQLI_NUM);
    $hour = date("G", time());
    if($hour < 3)
        $hour = 3;
    else if ($hour < 6)
        $hour = 6;
    else if($hour < 9)
        $hour = 9;
    else if($hour < 12)
        $hour = 12;
    else if($hour < 15)
        $hour = 15;
    else if ($hour < 18)
        $hour = 18;
    else if($hour < 21)
        $hour = 21;
    else if($hour <=23)
        $hour = 0;
    for($i=3, $t = strtotime($hour.":00"); $i<=12;$i+=3, $t+=10800)
    {
        echo "<td>".date("G:i", $t)."</td>";

        $dt =  ($t - time())/3600;
        if($dt<0)
            $dt =  24 + $dt;
        $forecast = myForecast($BMP085[$countBMP085-2], timeData($BMP085, 60*60, 3, 2, 1), $dt/*, $V*/);

        echo "<td>".imgForecast($forecast)."</td>";
        echo "<td>"/*."db: ".$forecastDB[(int)($i/3)-1][0]."<br>"*/.$forecast."</td>";
        $acc = file("rightForecast/Accuarity$i.txt");
        echo "<td>".$acc[1]."%<br>(обновлено ".((int)((time()-$acc[0])/60))." минут назад)</td>";
        echo "</tr>"; 
    }   
?>
</table></div></td></tr>
<form method="POST" action="" enctype="">
 Обновить точность прогноза   
<select name="timeF" size = "1"> 
<option value="3">3</option> 
<option selected value="6">6</option>
<option selected value="9">9</option>  
<option selected value="12">12</option> 
<option selected value="1">Все</option> 
</select>
<input type="submit" name="updateAccarity" value="OK">
</form>
<?php
    if(isset($_POST['seriesSubmit'])) { #если нажата клавиша формы
        $series = $_POST['series'];
        $series = trim($series);
        $calendar = $_POST['calendar'];
        $calendar = trim($calendar);
        if(!empty($series))
        {
            writeFile("series.txt", $series."\n ".$calendar."\r\n", 'a');
        } 
}

        $series= file("series.txt");
        $countSeries = count($series);
        $name;
        $date;
        //echo $result."</br>";

        for($i = 0;$i<$countSeries; $i++)
        {
            if($i%2 == 0)
                $name[(int)($i/2)] = $series[$i];
            else
                $date[(int)($i/2)] = strtotime($series[$i]);
        }
        unset($series);

        for($K = 0; $K<$countSeries/2; $K++) //сортировка пузырьком по возрастанию
            for($N = 0; $N<$countSeries/2; $N++)
            {
                if($date[$N] > $date[$N+1] /*&& $name[$N] != $name[$N+1]*/)
                {
                    $a = $date[$N+1]; //переставляем даты
                    $date[$N+1] = $date[$N];
                    $date[$N] = $a;

                    $a = $name[$N+1]; //переставляем имена
                    $name[$N+1] = $name[$N];
                    $name[$N] = $a;
                }
            }

        echo "</br><b>Ближайшие сериалы</b></br>";
        $count = 3;
        for($i = 0; $i<$count && $count<100; $i++)
            if($name[$i] != $name[$i+1] && ($date[$i] - time()) > -86400)
            {
                echo $name[$i]." ".date("d F, l Y", $date[$i]);
                $dtime = ($date[$i] - time())/86400;
                if($dtime < 0 && $dtime > -1)
                    echo " (сегодня)</br>";
                else if ($dtime > 0 && $dtime < 1)
                    echo " (завтра)</br>";
                else
                    echo " (через ".(int)($dtime)." дней)</br>";
            } 
            else
                $count++;
        //echo "countSeries=$countSeries</br>";

?>
<form method="POST" action="" enctype="">
    <font size="3">новый сериал: </font>
    <input type="text" name="series">
    <input type="date"name="calendar"> 
    <input type="submit" name="seriesSubmit" value="OK">
</form>
<b><a href="Newpage.php"  align = "center">Добавить новую страницу</a></b><br><br>
Архив<br>
<?php
    for($i = 0; $i<count($ListofPages); $i++)
    {
       echo printData(trim($ListofPages[$i]),1);
    }
?>
<!--</br><b></b><a href="matrix.php">преобразование матриц</a></br>
<b></b><a href="newgame.php">Балда</a></br>
<b></b><a href="editfile.php">Редоктор файлов</a></br>-->
</div> 
</body>
<?php } ?>