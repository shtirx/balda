<?
include("math.php");
function season()
    {
        $month = date("n");
        $season;
        $firstMonth;
        if(($month > 0 && $month < 3) || $month == 12)
            $season = "winter";
        else if($month > 2 && $month < 6)
            $season = "spring"; 
        else if($month > 5 && $month < 9)
            $season = "summer";
        else if($month > 8 && $month < 12)
            $season = "autumn";
        return $season;
    }
function forecastLineTernd($BMP085, $hourPrediction)
{
    //$hourPrediction=3;
    $pow_of_pol=1;
    $hourData=2;
    //$BMP085 = ReadMassiveData("BMP085", 0, 0);
    $nameWeather = Array("Ясно", "Частично облачно", "Облачно", "Дождь");
    $trendCoeffs = pressureTrendFunc($pow_of_pol,$hourData,$BMP085);//коэффициенты полинома, наденные МНК
    $velocity=$trendCoeffs[0];
    $trendPressure;
    if($velocity > 0.18)
    {   
        $trendPressure = "up";
    } else if($velocity < -0.18)
    {
        $trendPressure = "down";
    } else {
        $trendPressure = "stable";
    }
    $Ph=0;
    for($j=0;$j<$pow_of_pol+1;$j++)
    {
        $Ph+=$trendCoeffs[$j]*($hourPrediction+$hourData)**($pow_of_pol-$j);
        if(round($Ph,1)>700)
        {
            echo "P=".round($Ph)." h=".round($hourPrediction,2)." v=".round($velocity,4)."<br>";
        }
    }
    $weather = file("PW/".(season())."/".$trendPressure."/".round($Ph).".txt");
    $typeWeather;
    $result = count($weather);
    for($i = 0; $i<$result; $i++)
        {
            switch(selectW(trim($weather[$i])))
            {
                case 0:
                    $typeWeather[0]+=1;//ясно
                break;
                case 1:
                    $typeWeather[1]+=1; //Частично облачно
                break;
                case 2:
                    $typeWeather[2]+=1; //обачно
                break;
                case 3:
                    $typeWeather[3]+=1; //дождь
                    $typeWeather[2]+=1; //обачно
                    break;
                case 4:
                    $typeWeather[3]+=1; //дождь
                break;
                /*case 5:
                    $error.=$Wsorted[$i]." ";
                break;*/
                default:
                break;
            }
        }
    $forecast=array_keys($typeWeather,max($typeWeather));
    return $nameWeather[$forecast[0]];
}
function forecastDB($pressure, $velocity,$T, $H, $timeS)
{
    $mysqli = new mysqli("127.0.0.1", "root","", "weather");
    $flag=false;
    $res;
    for($ps=0.02;$ps<=0.2 && !$flag;$ps+=0.02)
    {
        $pmin = $pressure-$ps;
        $pmax = $pressure+$ps;
        for($ss=0.01;$ss<=0.15 && !$flag;$ss+=0.01)
        {
            $smin = $velocity-$ss;
            $smax = $velocity+$ss;
            for($ts=0.1;$ts<=2 && !$flag;$ts+=0.1)
            {
                $Tmin=$T-$ts;
                $Tmax=$T+$ts;
                $time = time()-3600*24;
                $res = mysqli_query($mysqli,"SELECT time FROM weather WHERE `pressure` >= $pmin AND `pressure` <= $pmax AND speed >= $smin AND speed <= $smax AND temperature >= $Tmin AND temperature <= $Tmax AND time <= $time");
                if($res->num_rows > 2)
                {
                    echo $ps." ".$ss." ".$ts."<br>";
                    $fh = fopen("rightForecast/typeF.txt", 'a');
                    fwrite($fh, $ps."\n ".$ss."\n ".$ts."\r\n".
                    "SELECT * FROM weather WHERE `pressure` >= $pmin AND `pressure` <= $pmax AND speed >= $smin AND speed <= $smax AND temperature >= $Tmin AND temperature <= $Tmax  AND time <= $time"."\r\n");
                    fclose($fh);
                    $flag=true;
                    echo "SELECT * FROM weather WHERE `pressure` >= $pmin AND `pressure` <= $pmax AND speed >= $smin AND speed <= $smax AND temperature >= $Tmin AND temperature <= $Tmax  AND time <= $time"."<br>";
                }
                    
            }
        }
    }
    $res = mysqli_fetch_all($res, MYSQLI_NUM);
    $typeW=array(0,0,0,0);
    $typeWN=array("Ясно","Переменная облачность","Облачно","Дождь");
    $weatherArr;
    for($i=0;$i<count($res);$i++)
    {
        $timeF = (int)($res[$i][0])+$timeS*3600;
        $resW = mysqli_query($mysqli,"SELECT code FROM weather WHERE time >= $timeF ORDER BY time ASC LIMIT 1");
        $weatherArr[]=mysqli_fetch_array($resW)[0];
    }
    for($i=0;$i<count($weatherArr);$i++)
    {
        $typeW[selectW($weatherArr[$i])]++;
    }
    $posw=0;
    $max=0;
    for($i=0;$i<count($typeW);$i++)
    {
        if($typeW[$i]>$max)
        {
           $max = $typeW[$i];
           $posw = $i;
        }
    }
    return $typeWN[$posw];
    //return $typeW;
}
function accuracyForecast()
{   
    $resArr;
    $resArrP;
    $count=0;
    $forecast = ["Ясно"=>0,"Переменная облачность"=>1,"Облачно"=>2,"Дождь"=>3];
    for($i=0; $i<12; $i++)
    {   $resArr[$i][0]=0;
        $mysqli = new mysqli("127.0.0.1", "root","", "weather");
        $res = mysqli_query($mysqli,"SELECT forecast.forecast, weather.weather, weather.code FROM weather, forecast WHERE weather.time=UNIX_TIMESTAMP(forecast.time) AND ((UNIX_TIMESTAMP(forecast.time) - UNIX_TIMESTAMP(forecast.timeForecast)) <= ($i+1)*3600) AND ((UNIX_TIMESTAMP(forecast.time) - UNIX_TIMESTAMP(forecast.timeForecast)) >= $i*3600)");
        $weatherArr = mysqli_fetch_all($res,MYSQLI_NUM);
        for($j=0;$j<count($weatherArr);$j++)
        {
          if(selectW($weatherArr[$j][2]) == $forecast[$weatherArr[$j][0]])
            $resArr[$i][0]++;
        }
        $resArr[$i][1]=count($weatherArr);
        $count+=$resArr[$i][1];
        $resArrP[$i]=round($resArr[$i][0]/$resArr[$i][1],3);
    }
    //print_r($resArrP);
    //print_r($resArr);
    $curDate=date("c");
    //echo "INSERT INTO accuracy VALUES(null, $resArrP[0],$resArrP[1],$resArrP[2],$resArrP[3],$resArrP[4],$resArrP[5],$resArrP[6],
    //    $resArrP[7],$resArrP[8],$resArrP[9],$resArrP[10],$resArrP[11],$count,'$curDate')<br>";
    mysqli_query($mysqli,"INSERT INTO accuracy VALUES(null, $resArrP[0],$resArrP[1],$resArrP[2],$resArrP[3],$resArrP[4],$resArrP[5],$resArrP[6],
        $resArrP[7],$resArrP[8],$resArrP[9],$resArrP[10],$resArrP[11],$count,'$curDate')");
}
//Составление прогноза погоды методом нахождения похожих погодных условий в базе данных
function forecastDBAll($pressure, $velocity,$T, $H)
{
    $hour = date("G");
    do
    {
       $hour++; 
    }while($hour%3 != 0);
    $mysqli = new mysqli("127.0.0.1", "root","", "weather");
    $flag=false;
    $res;
    $sqlReq;
    for($ps=0.02;$ps<=0.2 && !$flag;$ps+=0.02)
    {
        $pmin = $pressure-$ps;
        $pmax = $pressure+$ps;
        for($ss=0.01;$ss<=0.15 && !$flag;$ss+=0.01)
        {
            $smin = $velocity-$ss;
            $smax = $velocity+$ss;
            for($ts=0.1;$ts<=2 && !$flag;$ts+=0.1)
            {
                $Tmin=$T-$ts;
                $Tmax=$T+$ts;
                $time = time()-3600*24;
                $res = mysqli_query($mysqli,"SELECT time FROM weather WHERE `pressure` >= $pmin AND `pressure` <= $pmax AND speed >= $smin AND speed <= $smax AND temperature >= $Tmin AND temperature <= $Tmax AND time <= $time");
                if($res->num_rows > 2)
                {
                    echo $ps." ".$ss." ".$ts."<br>";
                    $fh = fopen("rightForecast/typeF.txt", 'a');
                    fwrite($fh, $ps."\n ".$ss."\n ".$ts."\r\n".
                    "SELECT * FROM weather WHERE `pressure` >= $pmin AND `pressure` <= $pmax AND speed >= $smin AND speed <= $smax AND temperature >= $Tmin AND temperature <= $Tmax  AND time <= $time"."\r\n".date("r")."\r\n");
                    fclose($fh);
                    $flag=true;
                }
                $sqlReq = "SELECT * FROM weather WHERE `pressure` >= $pmin AND `pressure` <= $pmax AND speed >= $smin AND speed <= $smax AND temperature >= $Tmin AND temperature <= $Tmax  AND time <= $time"."<br>";
            }
        }
    }
    echo $sqlReq;
    $res = mysqli_fetch_all($res, MYSQLI_NUM);
    
    $typeWN=array("Ясно","Переменная облачность","Облачно","Дождь");
    
    $weatherArrAll;
    $curTime=strtotime(date("Y-m-d ".$hour.":00:00P"));
    for($j=0;$j<4;$j++)
    {
        $weatherArr;
        $typeW=array(0,0,0,0);
        $timeS = $curTime-time();
        for($i=0;$i<count($res);$i++)
        {
            $timeF = (int)($res[$i][0])+$timeS;
            echo "f:" .$timeS." $timeF<br>";
            $resW = mysqli_query($mysqli,"SELECT code FROM weather WHERE abs(time-$timeF)=(SELECT min(abs(time-$timeF)) FROM weather)");
            $weatherArr[]=mysqli_fetch_array($resW)[0];
            echo "SELECT code FROM weather WHERE abs(time-$timeF)=(SELECT min(abs(time-$timeF)) FROM weather)"."<br>";
        }
        for($i=0;$i<count($weatherArr);$i++)
        {
            $typeW[selectW($weatherArr[$i])]++;
        }
        $maxW = max($typeW);
        if($maxW > 0)
        {
            $poswArr =array_keys($typeW, $maxW);
            $posw = round(array_sum($poswArr)/count($poswArr));
            echo "posw $posw<br>"; 
            $weatherArrAll[]=$typeWN[$posw];
        } else
            $weatherArrAll[]="-";
        //$hour+=3;
        $curTime+=3*3600;
        print_r($typeW);
        unset($weatherArr);
        unset($typeW);
    }
    return $weatherArrAll;
}
function betel_cast( $z_hpa, $z_month, $z_wind, $z_trend, $z_where = 1, $z_baro_top = 1050, $z_baro_bottom = 950, $wh_temp_out = 20)
{

$z_forecast_uk = Array("Settled fine", "Fine weather", "Becoming fine", "Fine,
becoming less settled", "Fine, possible showers", "Fairly fine,
improving", "Fairly fine, possible showers early", "Fairly fine,
showery later", "Showery early, improving", "Changeable,
mending", "Fairly fine, showers likely",
"Rather unsettled clearing later", "Unsettled, probably improving",
"Showery, bright intervals", "Showery, becoming less settled",
"Changeable, some rain", "Unsettled, short fine intervals",
"Unsettled, rain later", "Unsettled, some rain",
"Mostly very unsettled", "Occasional rain, worsening",
"Rain at times, very unsettled", "Rain at frequent intervals",
"Rain, very unsettled", "Stormy, may improve", "Stormy,
much rain");

// Если зима, то снег, а не дожди!
if ( $wh_temp_out < 2 )
$z_forecast = Array("Отличная, ясно", "Хорошая, ясно",
"Становление хорошей, ясной", "Хорошая, но ухудшается",
"Хорошая, возможен снегопад", "Достаточно хорошая, улучшается",
"Достаточно хорошая, возможен снегопад",
"Достаточно хорошая, но ожидается снегопад",
"Снегопад, но улучшается", "Переменчивая, но улучшается",
"Достаточно хорошая, вероятен снегопад", "Пасмурно, но проясняется",
"Пасмурно, возможно, улучшение",
"Снегопады, возможны временные прояснения",
"Снегопады, становится менее устойчивой",
"Переменчивая, небольшой снег", "Пасмурная, короткие прояснения",
"Пасмурная, ожидается снег", "Пасмурная, временами снег",
"Преимущественно, очень пасмурная", "Временами снег, ухудшение",
"Временами снег, очень плохая, пасмурно", "Снег очень часто",
"Снег, очень плохая, пасмурно", "Штормовая, но улучшается",
"Штормовая!, снегопад");
else
$z_forecast = Array("Отличная, ясно", "Хорошая, ясно",
"Становление хорошей, ясной", "Хорошая, но ухудшается",
"Хорошая, возможен ливень", "Достаточно хорошая, улучшается",
"Достаточно хорошая, возможен ливень",
"Достаточно хорошая, но ожидается ливень", "Ливень, но улучшается",
"Переменчивая, но улучшается", "Достаточно хорошая, вероятны ливни",
"Пасмурно, но проясняется", "Пасмурно, возможно, улучшение",
"Ливни, возможны временные прояснения",
"Ливни, становится менее устойчивой",
"Переменчивая, небольшие дожди", "Пасмурная, короткие прояснения",
"Пасмурная, ожидаются дожди", "Пасмурная, временами дожди",
"Преимущественно, очень пасмурная", "Временами дожди, ухудшение",
"Временами дожди, очень плохая, пасмурно", "Дожди очень часто",
"Дожди, очень плохая, пасмурно", "Штормовая, но улучшается",
"Штормовая!, дожди");

// equivalents of Zambretti 'dial window' letters A - Z
$rise_options  = Array(25,25,25,24,24,19,16,12,11,9,8,6,5,2,1,1,0,0,0,0,0,0) ;
$steady_options  = Array(25,25,25,25,25,25,23,23,22,18,15,13,10,4,1,1,0,0,0,0,0,0) ;
$fall_options = Array(25,25,25,25,25,25,25,25,23,23,21,20,17,14,7,3,1,1,1,0,0,0) ;

    $z_range = $z_baro_top - $z_baro_bottom;
    $z_constant = round(($z_range / 22), 3);

    $z_season = (($z_month >= 4) && ($z_month <= 9)) ;    // true if 'Summer'

    if($z_hpa == $z_baro_top) {$z_hpa = $z_baro_top - 1;}
    $z_option = floor(($z_hpa - $z_baro_bottom) / $z_constant);
     $z_output = "";

    if($z_option < 0) {
        $z_option = 0;
        $z_output = "Exceptional Weather, ";
    }
    if($z_option > 21) {
        $z_option = 21;
        $z_output = "Exceptional Weather, ";
    }

    if ($z_trend == 1) {
        $z_output .= $z_forecast[$rise_options[$z_option]]." (".$z_forecast_uk[$rise_options[$z_option]].")" ;
    } else if ($z_trend == 2) {
        $z_output .= $z_forecast[$fall_options[$z_option]]." (".$z_forecast_uk[$fall_options[$z_option]].")" ;
    } else {
        $z_output .= $z_forecast[$steady_options[$z_option]]." (".$z_forecast_uk[$steady_options[$z_option]].")" ;
    }
    return ($z_output);
}
    function createWeatherList()
    {
        $season = season();
        
        if(!file_exists("PW/".$season)) //проверяется наличе существование папки и файлов в ней
            mkdir("PW/".$season, 0700);
        
        $Weather = file("weatherNew.txt");
        $countPW = count($Weather);

        for($i = 0;$i < $countPW; $i+=4)
            writeFile("PW/".$season."/".round($Weather[$i+2]).".txt", trim($Weather[$i+1])."\n", 'a');
}
        function deleteWeatherList()
        {
            $season = season();
            for($p = 730; $p<775; $p++)
            {
                if(file_exists("PW/".$season."/".$p.".txt"))
                    unlink("PW/".$season."/".$p.".txt");
            }
            
        }

        function trendPressure($curP, $P1h, $V=10,$type=0)
        {
            $velocity;
            if($V == 10)
                $velocity = ($curP - $P1h)/1.0;
            else
                $velocity = $V;
            if($velocity > 0.18)
            {   
                if($type == 1)
                    return 1;
                return "up";
            }
            else if($velocity < -0.18)
            {
                if($type == 1)
                    return 2;
                return $forecast = "down";
            } else
            {
                if($type == 1)
                    return 3;
                return $forecast = "stable";
            }
                 
        }
        function myForecast($curP, $P1h,$time,$V=10)
        {
            /* ясно - 1508 1539 1516
            облачно 2724 2669
            переменная облачность 7611
            дождь 1904 1936 
            ливень 2331
            Частично облачно 5700
            Преимущественно ясно 7117
            Преимущественно облачно 8333
            Облачно и слабый дождь 7313
            Преимущественно облачно и слабый дождь 12977
            Облачно и временами осадки 8917
            Облачно, дождь с грозами 7745
            Преимущественно облачно, дождь с грозами 13409
            Преимущественно облачно и временами осадки 14581
            Частично облачно и временами осадки 11948
            Частично облачно и слабый дождь  10344
            Частично облачно, дождь с грозами 10776
            */
            $typeWeather = Array(0, 0, 0, 0); // ясно 0 переменная облачность 1 облачно 2 дождь 3
            $nameWeather = Array("Ясно", "Частично облачно", "Облачно", "Дождь");

            $season = season();
            $forecast;
            $error;
            $velocity;
            if($V == 10)
                $velocity = ((float)$curP - (float)$P1h)/1.0;
            else
                $velocity = $V;

            /*if($velocity > 0.22)
                $forecast = "Улучшение погоды, ";
            if($velocity < -0.22)
                $forecast = "Ухудшение погоды, ";*/


            $Ph = round((float)$curP + $velocity*(float)($time));

            //$P3h = 752;

             if($Ph >= 790)
            {
                $forecast .= "Ясно ";
                return $forecast;
            }     
            if($Ph <= 720)
            {
                $forecast .= "Дождь";
                if(date("n") <= 9 && date("n") >= 5)
                    $forecast .= ", гроза ";
                return $forecast;
            } else {
                while(!file_exists("PW/".$season."/".trendPressure($curP, $P1h, $V)."/".$Ph.".txt") && $Ph >= 720 && $Ph <= 790)
                {
                    if($velocity > 0)
                        $Ph++;
                    else
                        $Ph--;

                    if($Ph >= 790)
                    {
                        $forecast .= "Ясно ";
                        return $forecast;
                    }     
                    if($Ph <= 720)
                    {
                        $forecast .= "Дождь";
                        if(date("n") <= 9 && date("n") >= 5)
                            $forecast .= ", гроза ";
                        return $forecast;
                    }
                        
                } 
            } 
            $Wsorted = file("PW/".$season."/".trendPressure($curP, $P1h)."/".$Ph.".txt");
            $result = count($Wsorted);

            for($i = 0; $i<$result; $i++)
            {
                switch(selectW(trim($Wsorted[$i])))
                {
                    case 0:
                        $typeWeather[0]+=1;//ясно
                    break;
                    case 1:
                        $typeWeather[1]+=1; //Частично облачно
                    break;
                    case 2:
                        $typeWeather[2]+=1; //обачно
                    break;
                    case 3:
                        $typeWeather[3]+=1; //дождь
                        $typeWeather[2]+=1; //обачно
                    break;
                    case 4:
                        $typeWeather[3]+=1; //дождь
                    break;
                    case 5:
                        $error.=$Wsorted[$i]." ";
                    break;
                }

            }

            $showers = round($typeWeather[3]/$result, 2)*100;

            for($N = 0; $N<3; $N++)
                for($K = 0;$K<(3-$N); $K++)
                {
                    if($typeWeather[$K+1] > $typeWeather[$K])
                    {
                        $a = $typeWeather[$K];
                        $typeWeather[$K] = $typeWeather[$K+1];
                        $typeWeather[$K+1] = $a;

                        $a = $nameWeather[$K];
                        $nameWeather[$K] = $nameWeather[$K+1];
                        $nameWeather[$K+1] = $a;
                    }
                }

            

            $forecast .= $nameWeather[0];
            if($nameWeather[0] == "Дождь")
                $forecast .= " (в. д. ".$showers."%)";

            /*if(abs($typeWeather[0] - $typeWeather[1]) <= round($result*0.01))
            {
                if($nameWeather[1] == "Дождь")
                    $forecast .= ", ".$nameWeather[1]." (в. д. ".$showers."%)";
                else
                    $forecast .= ", ".$nameWeather[1];

            if(($nameWeather[1] == "Ясно" && $nameWeather[0] == "Облачно")||($nameWeather[0] == "Ясно" && $nameWeather[1] == "Облачно"))
                            $forecast = "Частично облачно";
            }*/

            if($error != "")
                $error = " error: ".$error;

            if($velocity < - 0.8 && $time <= 3) {//если скорость падения слишком большая
                if(date("n") <= 9 && date("n") >= 5) //летом - гроза, в остальное время - осадки
                    $forecast.=", Гроза";
                else
                    $forecast.=", Дождь";

            }
            //if($velocity < - 0.5 && $velocity > - 0.8 && $time <= 3) //если скорость падения слишком большая
            //    $forecast.=", Дождь";

            return $forecast."(всего $result)</br> Vp=".round($velocity,2)." (".trendPressure($curP, $P1h).") Ph=$Ph".$error;//."\n".$nameWeather[0]." ".$typeWeather[0]." ".$nameWeather[1]." ".$typeWeather[1]." ".$nameWeather[2]." ".$typeWeather[2]." ".$nameWeather[3]." ".$typeWeather[3]." ";//(round($countRain/$result, 2))
        }


        function timeData($Data, $time_s, $count_str, $count_n, $type, $index=false) //$count_str - кол-во значений в строке; $count_n - номер  значения в строке; type - 1 относительное значение, 2 абсолютное
        {
            $count_n--;
            $countM = count($Data);
            $i;
            $z_time;
            if($type == 1)
                $z_time = strtotime($Data[$countM-1]);
            else
                $z_time = time();

            for($i = $countM-$count_str+$count_n, $t = $countM-1; $i>0; $i-=$count_str,$t-=$count_str)
            {   $time = $z_time-strtotime($Data[$t]);
                if($time > $time_s)
                    {
                        if(abs($time - $time_s) < abs($z_time-strtotime($Data[$t+$count_str]) - $time_s))
                        {
                            if(!$index)
                                return $Data[$i];
                            else 
                                return $i;
                        }
                        else 
                            {
                                if(!$index)
                                return $Data[$i+$count_str];
                            else 
                                return $i+$count_str;
                            }
                    }
            }
        }

        function timeDataN($Data, $time_s, $count_str, $count_n, $type) //$count_str - кол-во значений в строке; $count_n - номер  значения в строке; type - 1 относительное значение, 2 абсолютное
        {
            $count_n--;
            $countM = count($Data);
            $i;
            $z_time;
            if($type == 1)
                $z_time = strtotime($Data[$countM-1]);
            else
                $z_time = time();

            for($i = $countM-$count_str+$count_n, $t = $countM-1; $i>0; $i-=$count_str,$t-=$count_str)
            {   $time = $z_time-strtotime($Data[$t]);
                if($time > $time_s)
                    {
                        if(abs($time - $time_s) < abs($z_time-strtotime($Data[$t+$count_str]) - $time_s))
                            return $i;
                        else 
                            return $i+$count_str;
                    }
            }
        }

        function Dtime($Data, $start, $time_s, $count_str) //$count_str - кол-во значений в строке; $count_n - номер  значения в строке; type - 1 относительное значение, 2 абсолютное
        {
            $countM = count($Data);
           
            for($i = 0; $i<$countM-1; $i+=$count_str)
            {    $time = strtotime($Data[$start+$i])-strtotime($Data[$start]);
                if($time >= $time_s)
                    {
                        if(abs($time - $time_s) < abs(strtotime($Data[$i-$count_str])-strtotime($Data[$start]) - $time_s))
                            return $start+$i;
                        else 
                            return $start+$i-$count_str;
                    }
            }
        }

        function imgForecast($forecast)
        {   
            if(strpos($forecast, "Дождь") !==false)
            {
                return "<img src=\"img/rain.png\" height=\"70\" width=\"70\">";
            }
            else if(strpos($forecast, "Ясно") === 0)
            {
                return "<img src=\"img/sunny.png\" height=\"70\" width=\"70\">";
            } else if(strpos($forecast, "Частично облачно") === 0)
            {
                return "<img src=\"img/partly_cloudy.png\" height=\"70\" width=\"70\">";
            } else if(strpos($forecast, "Частично облачно") === 0)
            {
                return "<img src=\"img/partly_cloudy.png\" height=\"70\" width=\"70\">";
            } else if(strpos($forecast, "Облачно") === 0)
            {
                return "<img src=\"img/cloudy.png\" height=\"70\" width=\"70\">";
            } 
        }

        function accuarity($time)
        {
            $mysqli = new mysqli("127.0.0.1", "root","", "weather");
            $timeSeason=strtotime("01-09-2018");
            $res = mysqli_query($mysqli,"SELECT pressure,time WHERE time >=$timeSeason");
            $arrW = mysqli_fetch_all($res,MYSQLI_NUM);
            //for($i=0,$i<)
            myForecast($arrW, $weather[$i-1], 3);
        }
        /*function accuarity($time)
        {
            $weather = file("weatherNew.txt");
            $dataofAccuarity;
            $start;
            $yes;
            $error;
            $begin;
            $i=0;
            $finish=0;
            
            

            while(date("n", strtotime($weather[3+$start])) != 6/&& $start<count($weather))//аменить проверку месяца
                $start+=4;
            if($begin == 0 )
                $begin = $start;

            for($i = $start+3;$i < count($weather); $i+=4)
            {
                while(strtotime($weather[$i]) == strtotime($weather[$i+4]))
                {
                    $i+=4;
                    $error++;
                }
                    
                $curN=Dtime($weather, $i, 3600, 4);
                    
                $forecast = myForecast($weather[$curN-1], $weather[$i-1], $time);
                $code = selectW($weather[$curN-2]);
                if(strpos($forecast, "Дождь") === 0)
                {
                    $forecast = "Дождь";
                    if($code == 3 || $code == 4)
                        $yes++;
                }
                else if(strpos($forecast, "Ясно") === 0)
                {
                    $forecast = "Ясно";
                    if($code == 0)
                        $yes++;
                } else if(strpos($forecast, "Частично облачно") === 0)
                {
                    $forecast = "Частично облачно";
                    if($code == 1)
                        $yes++;
                } else if(strpos($forecast, "Облачно") === 0)
                {
                    $forecast = "Облачно";
                    if($code == 2 || $code == 3)
                        $yes++;
                } else
                    $error++;
                
            } 
            writeFile("rightForecast/Accuarity$time".season().".txt",trim(count($weather))."\r\n".trim($yes)."\r\n".trim($error)."\r\n".trim($begin)."\r\n".trim(time())."\r\n",'w');
            return round($yes/((count($weather)-$begin)/4.0-$error), 4)*100;
        }*/
        function selectW($code)
        {
            switch($code)
                {
                    case 1516: //ясно
                    //case 1539: //ясно
                    //case 1508: //ясно
                    case 7117: //Преимущественно ясно
                    case 5700: //Частично облачно
                    //case 8333: //Преимущественно облачно
                    //case 10344://Частично облачно и слабый дождь
                    case 1418: // сухо
                    case 1828: // ясный
                    case 7002://ясно или малооблачно
                        return 0;//ясно
                    break;
                    //case 7611: //переменная облачность
                    case 8333: //Преимущественно облачно
                    case 10344://Частично облачно и слабый дождь
                    case 5700: //Частично облачно
                    case 4274: //малооблачно
                    case 6280: //малооблачно, дымка
                    case 6216://малооблачно дымка
                        return 1; //Частично облачно
                    break;
                    //case 2724: //облачно
                    case 8333: //Преимущественно облачно
                    case 2669: //облачно
                    case 6934: //Облачно и мокрый снег
                    case 2701: //облачно
                    case 3044: //облачный
                    case 4975://Туман
                    case 4687://облачно, дымка
                    case 9246://туман с осаждением изморози
                    case 1887://Туман
                    case 2031://дымка
                    case 4522://туман местами
                    case 5030://облачный, дымка
                            return 2; //обачно
                    break;
                    case 12977://Преимущественно облачно и слабый дождь
                    case 7313: //Облачно и слабый дождь
                    case 8917: //Облачно и временами осадки
                    case 10776://Частично облачно, дождь с грозами
                    case 14581://Преимущественно облачно и временами осадки
                    case 11948://Частично облачно и временами осадки
                    case 14873: //Преимущественно облачно и слабый мокрый снег
                    case 9209: //Облачно и слабый мокрый снег
                    case 6911: //Облачно и слабый снег
                    case 8134: //Облачно и временами снег
                    case 12575: //Преимущественно облачно и слабый снег
                    case 13798: //Преимущественно облачно и временами снег
                    case 9942: //Частично облачно и слабый снег
                    case 10825: //Облачно и временамий мокрый снег
                    case 2989: //пасмурно
                    case 4578://замерзающий морось
                        return 3; //обачно, дождь
                    break;
                    //case 1904: //дождь
                    //case 1936: //дождь
                    //case 2331: //ливень
                    case 13409://Преимущественно облачно, дождь с грозами
                    case 5038: //Облачно и дождь
                    case 4636: //Облачно и снег
                    case 7745: //Облачно, дождь с грозами
                    case 10344://Частично облачно и слабый дождь
                    case 11165://Частично облачно и временами снег
                    case 2216: //морось
                    case 6904: //слабый ливневый снег
                    case 3786: //слабый снег
                    case 3080://снегопад
                    case 4188://слабый дождь
                    case 4629: //ливневый снег
                    case 6402://замерзающий морось
                    case 7380: //ливневый снег с дождем
                    case 7306://слабый ливневый дождь
                    case 6099://замерзающий дождь
                    case 8136://ливневый дождь со снегом
                    case 5275://умеренные грозы
                        return 4; //дождь
                    break;
                    case 45:
                    case 598:
                    case 599:
                    case 600:
                    case 601:
                    case 602:
                    case 603:
                    case 604:
                        //return 5;
                    break;
                    default:
                        return 5;
                    break;                      
                }
        }
?>