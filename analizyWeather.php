<?php
include("file.php");
    $BMP085 = file("BMP085/BMP085_10_18.txt");
    $countBMP085 = floor(count($BMP085)/3);
    echo $countBMP085."<br>";
    $hour=11;
    if(!empty($_GET["hour"]))
    {
        $hour=$_GET["hour"];
    }
    $hourPrediction=6;
    $countBMP085-=12*($hour+$hourPrediction+1);
    $massiveOfErrors= array();
    $averageError= array();
    $maxPow=2;
    $step=200;
    //for(;$hour<=7;$hour+=2)
    {
        echo "hour=$hour<br>";
        writeFile("analizyWeather/analizyWeather${hourPrediction}.txt","hour=$hour\r\n", 'a');
        for($j=0;$j<$countBMP085;$j+=$step)
        {
            //echo "start=$j<br>";
            for($i=1;$i<=$maxPow;$i++)
            {
                echo "<br>pol=$i<br>";
                $massiveOfErrors[$j][$i-1] = lineTrendN($i,$j);
                $averageError[$i-1]+=$massiveOfErrors[$j][$i-1];
                //echo $massiveOfErrors[$j][$i]." ";
            }
            //echo "<br>";
        }
        for($i=0;$i<$maxPow;$i++)
        {
                $averageError[$i]/=($countBMP085/$step);
                echo ($i+1)." ".$averageError[$i]."<br>";
                writeFile("analizyWeather/analizyWeather${hourPrediction}.txt",($i+1)." ".$averageError[$i]."\r\n", 'a');
        }
    }   
   		function solveSLAY($matrix)
   		{
   			$str=0;
            $sol;
            $n=count($matrix[0])-1;

	        for($str=0; $str<$n;$str++)
	        { 
	            for($i=$str+1;$i<$n;$i++)
	            {
	            	$temp=$matrix[$i][$str];
	            	for($j=$str;$j<$n+1;$j++)
	            	{
	            		$matrix[$i][$j]-=($matrix[$str][$j]/$matrix[$str][$str]*$temp);
	            	}
	            }
        	}
        	$sol;
        	$sol[$n-1]=$matrix[$n-1][$n]/$matrix[$n-1][$n-1];
        	//echo "<br>C0 ".$sol[$n-1]."<br>";
        	for($i=$n-2;$i>=0;$i--)
        	{
        		$sum=0;
        		for($j=$n-1;$j>$i;$j--)
        		{
        			$sum+=$sol[$j]*$matrix[$i][$j];
        		}
        		$sol[$i]=$matrix[$i][$n];
        		$sol[$i]-=$sum;
        		$sol[$i]/=$matrix[$i][$i];
        		//echo "C".($n-2-$i+1)." ".$sol[$i]."<br>";
        	}
        	return $sol;
   		}

        function lineTrendN($pow_of_pol,$start)
        {
            //http://mathprofi.ru/metod_naimenshih_kvadratov.html
            //$BMP085 = ReadMassiveData("BMP085", "2018-10-01", "2018-11-01");
            global $BMP085;// = file("BMP085/BMP085_10_18.txt");
            global $countBMP085;// = floor(count($BMP085)/3);
            //echo $countBMP085."<br>";
            $p = array();
            $t = array();
            $t0;
            $error=0;
            $nData=0;
            global $hour;
            global $hourPrediction;
            /*if(!empty($_GET["hour"]))
            {
                $hour=$_GET["hour"];
            }*/
            $pow_ofDataTime = array();
            $pow_ofDataPressure = array();
            $predictions = array();
            /*if(!empty($_GET["num"]))
            {
                $nData=$_GET["num"];
            }*/
            //for($i=0;$i<$nData;$i++)
            
            for($i=0;strtotime($BMP085[($i+$start)*3+2])-strtotime($BMP085[$start*3+2])<$hour*3600;$i++)
            {
                //echo $BMP085[$i]."<br>";
                $p[$i]=$BMP085[($i+$start)*3+1];
                $t[$i]=strtotime($BMP085[($i+$start)*3+2]);
                $nData++;
            }
            //echo "nData=$nData<br>";
            $t0=$t[0];
            //$t00=$t0;
            for($i=0;$i<$nData;$i++)
            {
                $t[$i]-=$t0;
                $t[$i]/=3600;
                //if($pow_of_pol==1)
                //    echo $p[$i]." ".round($t[$i],2)."<br>";
            }
            $t0/=3600;
            $matrix;
            $matrix=array();
            for($i=$pow_of_pol*2;$i>0;$i--)//считает все необходимые степени t
            {
                for($j=0;$j<$nData;$j++)
                {
                    $pow_ofDataTime[$pow_of_pol*2-$i]+=$t[$j]**$i;
                }
            }
            $pow_ofDataTime[$pow_of_pol*2]=$nData;//последний элемент в системе равен кол-ву входных точек
            for($i=$pow_of_pol;$i>=0;$i--)
            {
                for($j=0;$j<$nData;$j++)
                {
                    $pow_ofDataPressure[$pow_of_pol-$i]+=($t[$j]**$i)*$p[$j];
                }
            }
            unset($t);
            for($i=0;$i<$pow_of_pol+1;$i++)//заполняем матрицу согласно рассчитанной теоретически СЛАУ
            {
                for($j=0;$j<$pow_of_pol+1;$j++)
                {
                    $matrix[$i][$j]=$pow_ofDataTime[$j+$i];
                }
            }
            for($j=0;$j<$pow_of_pol+1;$j++)//заполняем матрицу согласно рассчитанной теоретически СЛАУ
            {
                $matrix[$j][$pow_of_pol+1]=$pow_ofDataPressure[$j];
            }
            $sol = solveSLAY($matrix);
            for($i=0;$i<$nData;$i++)//счиатем дисперсию
            {
                $y=0;
                for($j=0;$j<$pow_of_pol+1;$j++)
                {
                    $y+=$sol[$j]*$t[$j]**($pow_of_pol-$j);
                }
                $error+=($p[$i]-$y)**2;
            }
            $error=sqrt($error/($nData*($nData-1)));
            /*echo "error=$error<br>";
            echo "y=";
            for($j=$pow_of_pol;$j>0;$j--)//печатаем полученый полином
            {
                 echo $sol[$pow_of_pol-$j]."x^$j+";
            }
            echo $sol[$pow_of_pol]."<br>";*/
            $error=0;
            $i=$nData;
            for($k=0;(strtotime($BMP085[($i+$start)*3+2])-strtotime($BMP085[($nData+$start)*3+2]))/3600<$hourPrediction;$i++,$k++)//предполагаем давление в будущем
            {   
                $pi=0;
                for($j=0;$j<$pow_of_pol+1;$j++)
                {
                    $pi+=$sol[$j]*(strtotime($BMP085[($i+$start)*3+2])/3600-$t0)**($pow_of_pol-$j);
                }
                $predictions[$k][0]=$pi;
                $predictions[$k][1]=$BMP085[($i+$start)*3+1];
                $predictions[$k][2]=strtotime($BMP085[($i+$start)*3+2])/3600-$t0;
                $error+=abs($predictions[$k][0]-$predictions[$k][1]);
                //echo $BMP085[($i+$start)*3+2]."<br>";
                echo "t=".$predictions[$k][2]." p=".$predictions[$k][0]." ".$predictions[$k][1]." ".abs($predictions[$k][0]-$predictions[$k][1])."<br>";
            }
            $error=($error)/($i-$nData);
            //echo "errorPredictions=$error<br>";
            unset($predictions);
            unset($pow_ofDataTime);
            unset($pow_ofDataPressure);
            unset($matrix);
            unset($p);
            return $error;
        }
        function lineTrendNWork($pow_of_pol)
        {
            //http://mathprofi.ru/metod_naimenshih_kvadratov.html
            $BMP085 = ReadMassiveData("BMP085", 0, 0);
            $countBMP085 = count($BMP085);
            $p;
            $t;
            $t0;
            $nData=3;
            $hour=$_GET["hour"];
            $pow_ofDataTime = array();
            $pow_ofDataPressure = array();
            while(time()-strtotime($BMP085[$countBMP085-1-$nData*3]) < $hour*3600)
                $nData++;
            if(!empty($_GET["num"]))
            {
                $nData=$_GET["num"];
            }
            echo "nData=$nData<br>";
            for($i=1;$i<=$nData;$i++)
            {
                $p[$i-1]=$BMP085[$countBMP085-2-($nData-$i)*3];
                $t[$i-1]=strtotime($BMP085[$countBMP085-1-($nData-$i)*3]);
            }
            $t0=$t[0];
            //$t00=$t0;
            for($i=0;$i<$nData;$i++)
            {
                $t[$i]-=$t0;
                $t[$i]/=3600;
                if($pow_of_pol==1)
                    echo $p[$i]." ".round($t[$i],2)."<br>";
            }
            $t0/=3600;
            $matrix;
            $matrix=array();
            for($i=$pow_of_pol*2;$i>0;$i--)//считает все необходимые степени t
            {
                for($j=0;$j<$nData;$j++)
                {
                    $pow_ofDataTime[$pow_of_pol*2-$i]+=$t[$j]**$i;
                }
            }
            $pow_ofDataTime[$pow_of_pol*2]=$nData;//последний элемент в системе равен кол-ву входных точек
            for($i=$pow_of_pol;$i>=0;$i--)
            {
                for($j=0;$j<$nData;$j++)
                {
                    $pow_ofDataPressure[$pow_of_pol-$i]+=($t[$j]**$i)*$p[$j];
                }
            }
            for($i=0;$i<$pow_of_pol+1;$i++)//заполняем матрицу согласно рассчитанной теоретически СЛАУ
            {
                for($j=0;$j<$pow_of_pol+1;$j++)
                {
                    $matrix[$i][$j]=$pow_ofDataTime[$j+$i];
                }
            }
            for($j=0;$j<$pow_of_pol+1;$j++)//заполняем матрицу согласно рассчитанной теоретически СЛАУ
            {
                $matrix[$j][$pow_of_pol+1]=$pow_ofDataPressure[$j];
            }
            $sol = solveSLAY($matrix);
            
            echo "y=";
            for($j=$pow_of_pol;$j>0;$j--)//печатаем полученый полином
            {
                 echo $sol[$pow_of_pol-$j]."x^$j+";
            }
            echo $sol[$pow_of_pol]."<br>";
            /*for($i=0;$i<$nData;$i++)//счиатем дисперсию
            {
                $y=0;
                for($j=0;$j<$pow_of_pol+1;$j++)
                {
                    $y+=$sol[$j]*$t[$j]**($pow_of_pol-$j);
                }
                $error+=($p[$i]-$y)**2;
            }
            $error=sqrt($error/($nData*($nData-1)));
            echo "error=$error<br>";
            $error=0;
            for($i=0;$i<$pow_of_pol+1;$i++)//считаем невязку
            {
                $SumStr=0;
                for($j=0;$j<$pow_of_pol+1;$j++)
                {
                    $SumStr+=$matrix[$i][$j]*$sol[$j];
                }
                $error+=($SumStr-$matrix[$i][$pow_of_pol+1])**2;
            }
            $error=sqrt($error);
            echo "Невязка=$error<br>";*/
            for($i=$t[$nData-1];$i<($hour+3);$i+=(1/12))//предполагаем давление в будущем
            {   
                $pi=0;
                for($j=0;$j<$pow_of_pol+1;$j++)
                {
                    $pi+=$sol[$j]*$i**($pow_of_pol-$j);;
                }
                echo "t=".round($i,2).", ".date("r", ($t0+$i)*3600)." p=$pi<br>";
            }
        }

?>