<?php
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
        	}
        	return $sol;
   		}
        function trendNFunc($Y, $X, $nData,$pow_of_pol)
        {
            $pow_ofX = array();
            $pow_ofY = array();
            $matrix =array();
            for($i=$pow_of_pol*2;$i>0;$i--)//считает все необходимые степени t
            {
                for($j=0;$j<$nData;$j++)
                {
                    $pow_ofX[$pow_of_pol*2-$i]+=$X[$j]**$i;
                }
            }
            $pow_ofX[$pow_of_pol*2]=$nData;//последний элемент в системе равен кол-ву входных точек
            for($i=$pow_of_pol;$i>=0;$i--)
            {
                for($j=0;$j<$nData;$j++)
                {
                    $pow_ofY[$pow_of_pol-$i]+=($X[$j]**$i)*$Y[$j];
                }
            }
            for($i=0;$i<$pow_of_pol+1;$i++)//заполняем матрицу согласно рассчитанной теоретически СЛАУ
            {
                for($j=0;$j<$pow_of_pol+1;$j++)
                {
                    $matrix[$i][$j]=$pow_ofX[$j+$i];
                }
            }
            for($j=0;$j<$pow_of_pol+1;$j++)//заполняем матрицу согласно рассчитанной теоретически СЛАУ
            {
                $matrix[$j][$pow_of_pol+1]=$pow_ofY[$j];
            }
            $sol = solveSLAY($matrix);
            return $sol;
        }
        /*function lineTrendN($pow_of_pol,$hour,$BMP085)
        {
            //http://mathprofi.ru/metod_naimenshih_kvadratov.html
            //$BMP085 = ReadMassiveData("BMP085", 0, 0);
            $countBMP085 = count($BMP085);
            $p;
            $t;
            $t0;
            $nData=$pow_of_pol+1;
            $pow_ofDataTime = array();
            $pow_ofDataPressure = array();
            while(time()-strtotime($BMP085[$countBMP085-1-$nData*3]) < $hour*3600)
                $nData++;
            //echo "nData=$nData<br>";
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
            
            return $sol;
        }*/
        function pressureTrendFunc($pow_of_pol,$hour,$BMP085)
        {
            //http://mathprofi.ru/metod_naimenshih_kvadratov.html
            //$BMP085 = ReadMassiveData("BMP085", 0, 0);
            $countBMP085 = count($BMP085);
            $p;
            $t;
            $t0;
            $nData=$pow_of_pol+1;
            $pow_ofDataTime = array();
            $pow_ofDataPressure = array();
            while(time()-strtotime($BMP085[$countBMP085-1-$nData*3]) < $hour*3600)
                $nData++;
            for($i=1;$i<=$nData;$i++)
            {
                $p[$i-1]=$BMP085[$countBMP085-2-($nData-$i)*3];
                $t[$i-1]=strtotime($BMP085[$countBMP085-1-($nData-$i)*3]);
            }
            $t0=$t[0];
            for($i=0;$i<$nData;$i++)
            {
                $t[$i]-=$t0;
                $t[$i]/=3600;
            }
            $t0/=3600;
            $sol = trendNFunc($p, $t, $nData,$pow_of_pol);
            return $sol;
        }
?>