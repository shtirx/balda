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
            $t=$_GET["T"];
            $p=$_GET["P"];
            writeFile("BMP085/BMP085_".date("n")."_".date("y").".txt", $t."\n ".$p."\n ".$date."\r\n", 'a');// запись T, P, time в файл
            //$sqlSensors = new mysqli("127.0.0.1", "root","", "Sensors");
            $mysqli = new mysqli("127.0.0.1", "root","", "");
            $d=strtotime($date);
            mysqli_query($mysqli,"INSERT INTO Sensors.BMP085 VALUES (null, $t,$p,NOW(),$d)");
            //mysql_close($sqlSensors);
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
                    //---находим скорость давления во времея $curTime
                    $res = mysqli_query($mysqli,"SELECT id FROM Sensors.BMP085 WHERE unixTime>$curTime ORDER by unixTime LIMIT 1;");
                    $id = mysqli_fetch_array($res)[0];
                    $res = mysqli_query($mysqli,"SELECT pressure, unixTime,id FROM Sensors.BMP085 WHERE id>=(${id}-3) AND id<=${id+1} ORDER by unixTime;");
                    $row = mysqli_num_rows($res);
                    $res = mysqli_fetch_all($res,MYSQLI_NUM);
                    $col = count($res[0]);
                    $t2 = $res[3][1]-$curTime;
                    $t1 = $curTime-$res[2][1];
                    $vel;
                    $tmin;
                    $minData;
                    $previousP;
                    $Y;
                    $X;
                    for($countNData=0;$countNData<5;$countNData++)
                    {
                        $Y[$countNData]=$res[$countNData][0];
                        $X[$countNData]=$res[$countNData][1]-$res[0][1];
                    }
                    $vel=trendNFunc($Y, $X, 5,1)[0];
                    if(abs($t2)<abs($t1))
                    {
                        $previousP = $res[3][0];
                    }else{
                        $previousP = $res[2][0];      
                    }
                    $trendP="stable";
                    if($vel>0.17)
                        $trendP="up";
                    else if ($vel<0.17)
                        $trendP="down";
                    //-------------
                    //$previousP = timeData($BMP085, $dtime, 3, 2, 2); //Если данные давления ошибочны
                    //$previousP1h = timeData($BMP085, $dtime + 60*60, 3, 2, 2);//Если данные, которые были час назад ошибочны давления ошибочны
                    //$vel=$previousP-$previousP1h;
                    $vel=round($vel,4);
                    /*$DHT21 = ReadMassiveData("DHT21", date("r", $curTime-3600),date("r", $curTime));
                    $tm = timeData($DHT21, $dtime, 4, 1, 2);
                    $h = timeData($DHT21, $dtime, 4, 2, 2);*/
                    //$mysqli = new mysqli("127.0.0.1", "root","", "weather");
                    mysqli_query($mysqli,"INSERT INTO weather.weather VALUES (null, '$weather',$a,$previousP,$curTime, $vel,null,null)");
                    writeFile("weatherNew.txt",$weather."\n ".$a."\n ".trim($previousP)."\n ".date("r", $curTime)."\n ".$vel."\r\n", 'a'); //запись погоды давления и даты в файл
                    writeFile("PW/".season()."/".round($previousP).".txt", $a."\r\n", 'a'); //запись давления в файл
                    writeFile("PW/".season()."/".$trendP."/".round($previousP).".txt", $a."\r\n", 'a'); //запись давления в зависимости от его изменения в файл  
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