<?php
include("file.php"); 
include("forecast.php");
$mysqli = new mysqli("127.0.0.1", "root","", "weather");
//$res = mysqli_query($mysqli,"SELECT pressure, speed, temperature,time FROM weather WHERE id=(SELECT MAX(id) FROM weather)");
//$weatherData = mysqli_fetch_array($res);
$BMP085 = ReadMassiveData("BMP085");
$countBMP085 = count($BMP085);
$velocityArr;
for($i=0;$i<3;$i++)
    $velocityArr[]=(float)($BMP085[$countBMP085-$i*3-2]-$BMP085[$countBMP085-3-$i*3-2])/(strtotime($BMP085[$countBMP085-$i*3-1])-strtotime($BMP085[$countBMP085-3-$i*3-1]))*3600;
$velocity=array_sum($velocityArr)/count($velocityArr);
/*print_r($velocityArr)."<br>";
echo $velocity."<br>";*/
$DHT21 = ReadMassiveData("DHT21");
$countDHT21 = count($DHT21);
$hour = date("G");
do
{
   $hour++; 
}while($hour%3 != 0);
/*    
for($i=0;$i<4;$i++)
{   
    $timeF = strtotime($hour.":00");
    $time = $timeF-time();
    $forecast = forecastDB($weatherData[0], $weatherData[1],$weatherData[2], 0, $time);
    $DBDate = date("c",$timeF);
    echo $time." ".$DBDate." ".$forecast."<br>";
    mysqli_query($mysqli,"INSERT INTO  forecast values(null,'$forecast','$DBDate')");
    echo "INSERT INTO  forecast values(null,'$forecast','$DBDate')"."<br>";
    $hour+=3;
}*/
    $forecastAll=forecastDBAll($BMP085[$countBMP085-2], $velocity,$DHT21[$countDHT21-4],$DHT21[$countDHT21-3]);
    $timeF=strtotime(date("Y-m-d ".$hour.":00:00P"));
    echo date("Y-m-d ".$hour.":00:00P")."<br>";
    $curDate=date("c");
    for($i=0;$i<4;$i++)
    {   
        //$timeF = $curTime;//strtotime($hour.":00");
        $time = $timeF-time();
        $DBDate = date("c",$timeF);
        echo $time." ".$DBDate." ".$forecast."<br>";
        $forecast=$forecastAll[$i];
        mysqli_query($mysqli,"INSERT INTO  forecast values(null,'$forecast','$DBDate','$curDate')");
        echo "INSERT INTO  forecast values(null,'$forecast','$DBDate','$curDate')"."<br>";
        //$hour+=3;
        $timeF+=3*3600;
    }

    accuracyForecast();
?>