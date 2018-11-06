<?php
include("file.php");
include("forecast.php");
date_default_timezone_set('Europe/Minsk');

$date = date("r");

$count_m = 0; 
$count_y = 0;
if(date("d") == 1 && date("G") == 0)// если сейчас 0 час первого дня месяца
{
    if(date("n") == 1)
    {
        $count_m = -11;
        $count_y = 1;
    } else
        $count_m = 1;
}
    
$update=true;
$BMP085 = file("BMP085/BMP085_".(date("n")-$count_m)."_".(date("y")-$count_y).".txt");//чтение данных BMP085 из файла
$time_update = rNumFile("BMP085settings/UpdateTime.txt")-rNumFile("BMP085settings/UpdateTimeCorect.txt");// + round(rNumFile("UpdateTime.txt")/12);
$delay = time()-strtotime($BMP085[count($BMP085)-1]);    
if($delay < (int)($time_update*0.9))
{
    echo $time_update-$delay; 
    $update=false;
} else if (!empty($_GET["T"])&&!empty($_GET["P"]))
{ 
        if(($BMP085[count($BMP085)-2] - $_GET["P"] > 0.5 || $_GET["P"] - $BMP085[count($BMP085)-2] > 0.5 || $BMP085[count($BMP085)-3] - $_GET["T"] > 0.4) && time() - strtotime($BMP085[count($BMP085)-1]) < $time_update+30){
            echo "0";
            $update=false;
        } else {
            echo $time_update;

            writeFile("BMP085/BMP085_".date("n")."_".date("y").".txt", $_GET["T"]."\n ".$_GET["P"]."\n ".$date."\r\n", 'a');// запись T, P, time в файл

            if(date("i") < round($time_update/60) || ((date("i") < round($time_update/60) + 30 && date("i") >= 30)))
            {
                $url1 = "http://www.pogodaonline.ru/weather/maps/city?LANG=ru&CEL=C&SI=kph&MAPS=over&CONT=ruru&LAND=RS&REGION=0006&WMO=26063&UP=0&R=0&LEVEL=140&NOREGION=1";
                $content1 = getSslPage($url1);
                $posTime1=strpos($content1, "MSK");
                $posTimeF1=strpos($content1, "/td>", $posTime1);
                $curTime=strtotime(substr($content1, $posTime1+6, $posTimeF1-$posTime1-7));

                $posStart1=strpos($content1, "<b>погода</b>",$posTime1);    
                $posStart1=strpos($content1, "<tr>",$posStart1);
                $posStart1=strpos($content1, "<td>",$posStart1);
                $posStart1=strpos($content1, "<td>",$posStart1+5);
                $posFinish1=strpos($content1, "</td>",$posStart1+5);
                $weather=substr($content1, $posStart1+4, $posFinish1-$posStart1-4);
                unset($content1);

                if(date("G", $curTime) == 23 && date("G") == 0)
                    $curTime-=86400;

                $a;
                for($i = 0; $i<strlen($weather); $i++)
                {
                    $a += ord($weather{$i});
                }
                
                if($a<100000)
                {
                    $dtime = time()-$curTime;

                    $previousP = timeData($BMP085, $dtime, 3, 2, 2); //Если данные давления ошибочны
                    //$previousPindex = timeData($BMP085, $dtime, 3, 2, 2,true); //Если данные давления ошибочны
                    $previousP1h = timeData($BMP085, $dtime + 60*60, 3, 2, 2);//Если данные, которые были час назад ошибочны давления ошибочны
                    $countBMP085 = count($BMP085);
                    //$vel = ((float)$previousP-(float)$BMP085[$countBMP085-$previousPindex-3])/(strtotime($BMP085[$previousPindex+1])-strtotime($BMP085[$countBMP085-3-$previousPindex+1]))*3600;
                    $vel=$previousP-$previousP1h;
                    $vel=round($vel,3);
                    $mysqli = new mysqli("127.0.0.1", "root","", "weather");
                    $DHT21 = ReadMassiveData("DHT21", date("r", $curTime-3600),date("r", $curTime));
                    $tm = timeData($DHT21, $dtime, 4, 1, 2);
                    $h = timeData($DHT21, $dtime, 4, 2, 2);
                    mysqli_query($mysqli,"INSERT INTO weather VALUES (null, '$weather',$a,$previousP,$curTime, $vel,$tm,$h)");
                    writeFile("weatherNew.txt",$weather."\n ".$a."\n ".trim($previousP)."\n ".date("r", $curTime)."\n ".round($vel,3)."\r\n", 'a'); //запись погоды давления и даты в файл
                    writeFile("PW/".season()."/".round($previousP).".txt", $a."\r\n", 'a'); //запись давления в файл
                    writeFile("PW/".season()."/".trendPressure($previousP, $previousP1h)."/".round($previousP).".txt", $a."\r\n", 'a'); //запись давления в зависимости от его изменения в файл  
                    //exec_script("http://127.0.0.1:8080/wForecast.php"); //Запуск скрипта для пнахождения погоды по похожим случаям в БД
                }
            }
        }
 } 
if((!empty($_GET["ADC"]) && $update))
{
    writeFile("Rain/Rain_".date("n")."_".date("y").".txt", ($_GET["ADC"]-1)."\n ".$date."\r\n", 'a');// запись T, P, time в файл
}

exec_script("http://127.0.0.1:8080/script.php");
?>