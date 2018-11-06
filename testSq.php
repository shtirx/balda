<?php
include("file.php");
			/*$mysqli = new mysqli("127.0.0.1", "root","", "weather");
            $timeSeason=strtotime("01-09-2018");
            echo $timeSeason."<br>";
            $res = mysqli_query($mysqli,"SELECT `pressure`,`time` WHERE `time` > ".$timeSeason.'"');
            $arrW = mysqli_fetch_all($res,MYSQLI_NUM);
            echo $arrW[0];*/
        lineTrend1();
        echo "<br>";
        lineTrend2();
   		function solveSLAY($matrix)
   		{
   			$str=0;
            $sol;
            $n=count($matrix[0])-1;
   		 	/*for($i=0;$i<$n;$i++)
            {
            	for($j=0;$j<$n+1;$j++)
            	{
            		echo "$i $j: ".$matrix[$i][$j]." ";
            	}
            	echo "<br>";
            }*/
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
	            /*echo "str=$str<br>";
	            for($i=0;$i<$n;$i++)
	            {
	            	for($j=0;$j<$n+1;$j++)
	            	{
	            		echo "$i $j: ".$matrix[$i][$j]." ";
	            	}
	            	echo "<br>";
	            }*/
        	}
        	$sol;
        	$sol[$n-1]=$matrix[$n-1][$n]/$matrix[$n-1][$n-1];
        	echo "<br>C0 ".$sol[$n-1]."<br>";
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
        		echo "C".($n-2-$i+1)." ".$sol[$i]."<br>";
        	}
        	return $sol;
   		}
        function lineTrend1()
        {
        	$BMP085 = ReadMassiveData("BMP085", 0, 0);
        	$countBMP085 = count($BMP085);
        	$p;
        	$t;
        	$t0;
        	$nData=3;
            $hour=$hour=$_GET["hour"];
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
            	//echo $p[$i]." ".$t[$i]."<br>";
            }
            $t0=$t[0];
            for($i=0;$i<$nData;$i++)
            {
                $t[$i]-=$t0;
                $t[$i]/=3600;
                echo $p[$i]." ".round($t[$i],2)."<br>";
            }
            $t0/=3600;
            $matrix;
            $matrix[0][0]=0;
            $matrix[1][0]=0;
            $matrix[2][0]=0;
            $matrix[2][1]=0;
            for($i=0;$i<$nData;$i++)
            {
            	$matrix[0][0]+=pow($t[$i],2);
            	$matrix[1][0]+=$t[$i];
            	$matrix[0][2]+=$t[$i]*$p[$i];
            	$matrix[1][2]+=$p[$i];
            }
            $matrix[0][1]=$matrix[1][0];
            $matrix[1][1]=$nData;

            $sol = solveSLAY($matrix);
           
        	echo "y=".$sol[0]."x+".$sol[1]."<br>";
        	$error=0;
        	for($i=0;$i<$nData;$i++)
        	{
        		$error+=($p[$i]-($t[$i]*$sol[0]+$sol[1]))**2;
        	}
            $error=sqrt($error/($nData*($nData-1)));
            echo "error=$error<br>";
            $error=0;
            for($i=0;$i<2;$i++)
            {
                $SumStr=0;
                for($j=0;$j<2;$j++)
                {
                    $SumStr+=$matrix[$i][$j]*$sol[$j];
                }
                $error+=($SumStr-$matrix[$i][2])**2;
            }
            $error=sqrt($error);
            echo "Невязка=$error<br>";
        	for($i=$t[$nData-1];$i<($hour+3);$i+=(1/12))
        	{
        		echo "t=".round($i,2).", ".date("r", $t0+$i)." p=".($i*$sol[0]+$sol[1])."<br>";
        	}
        }
        /*function lineTrend2()
        {
        	$BMP085 = ReadMassiveData("BMP085", 0, 0);
        	$countBMP085 = count($BMP085);
        	$p;
        	$t;
        	$t0;
        	$nData=3;
            $hour=$_GET["hour"];
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
            	//echo $p[$i]." ".$t[$i]."<br>";
            }
            $t0=$t[0];
            for($i=0;$i<$nData;$i++)
            {
                $t[$i]-=$t0;
                //echo $p[$i]." ".$t[$i]."<br>";
            }

            $matrix;
            $matrix=array();
            for($i=0;$i<$nData;$i++)
            {
            	$matrix[0][0]+=pow($t[$i],4);
            	$matrix[0][1]+=pow($t[$i],3);
            	$matrix[0][2]+=pow($t[$i],2);
            	$matrix[0][3]+=pow($t[$i],2)*$p[$i];
            	$matrix[1][2]+=$t[$i];
            	$matrix[1][3]+=$t[$i]*$p[$i];
            	$matrix[2][3]+=$p[$i];
            }
            $matrix[1][0]=$matrix[0][1];
            $matrix[1][1]=$matrix[0][2];
            $matrix[2][0]=$matrix[0][2];
            $matrix[2][1]=$matrix[1][2];
            $matrix[2][2]=$nData;

            $sol = solveSLAY($matrix);
           
        	echo "y=".$sol[0]."x^2+".$sol[1]."x+".$sol[2]."<br>";
        	
        	for($i=0;$i<$nData;$i++)
        	{
        		$error+=($p[$i]-(pow($t[$i],2)*$sol[0]+$sol[1]*$t[$i]+$sol[2]))**2;
        	}
            $error=sqrt($error/($nData*($nData-1)));
        	echo "error=$error<br>";
            $error=0;
            for($i=0;$i<3;$i++)
            {
                $SumStr=0;
                for($j=0;$j<3;$j++)
                {
                    $SumStr+=$matrix[$i][$j]*$sol[$j];
                }
                $error+=($SumStr-$matrix[$i][3])**2;
            }
            $error=sqrt($error);
            echo "Невязка=$error<br>";
        	for($i=$t[$nData-1];$i<($hour+3)*3600;$i+=300)
            {
                echo "t=$i(".round($i/3600,2)."), ".date("r", $t0+$i)." p=".((pow($i,2)*$sol[0]+$sol[1]*$i+$sol[2]))."<br>";
        	}
        }*/
        function lineTrend2()
        {
            $BMP085 = ReadMassiveData("BMP085", 0, 0);
            $countBMP085 = count($BMP085);
            $p;
            $t;
            $t0;
            $nData=3;
            $hour=$_GET["hour"];
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
                //echo $p[$i]." ".$t[$i]."<br>";
            }
            $t0=$t[0];
            for($i=0;$i<$nData;$i++)
            {
                $t[$i]-=$t0;
                $t[$i]/=3600;
                //echo $p[$i]." ".$t[$i]."<br>";
            }
            $t0/=3600;
            $matrix;
            /*$matrix[0][0]=0;
            $matrix[1][0]=0;
            $matrix[2][0]=0;
            $matrix[2][1]=0;*/
            $matrix=array();
            for($i=0;$i<$nData;$i++)
            {
                $matrix[0][0]+=pow($t[$i],4);
                $matrix[0][1]+=pow($t[$i],3);
                $matrix[0][2]+=pow($t[$i],2);
                $matrix[0][3]+=pow($t[$i],2)*$p[$i];
                $matrix[1][2]+=$t[$i];
                $matrix[1][3]+=$t[$i]*$p[$i];
                $matrix[2][3]+=$p[$i];
            }
            $matrix[1][0]=$matrix[0][1];
            $matrix[1][1]=$matrix[0][2];
            $matrix[2][0]=$matrix[0][2];
            $matrix[2][1]=$matrix[1][2];
            $matrix[2][2]=$nData;

            $sol = solveSLAY($matrix);
           
            echo "y=".$sol[0]."x^2+".$sol[1]."x+".$sol[2]."<br>";
            
            for($i=0;$i<$nData;$i++)
            {
                $error+=($p[$i]-(pow($t[$i],2)*$sol[0]+$sol[1]*$t[$i]+$sol[2]))**2;
            }
            $error=sqrt($error/($nData*($nData-1)));
            echo "error=$error<br>";
            $error=0;
            for($i=0;$i<3;$i++)
            {
                $SumStr=0;
                for($j=0;$j<3;$j++)
                {
                    $SumStr+=$matrix[$i][$j]*$sol[$j];
                }
                $error+=($SumStr-$matrix[$i][3])**2;
            }
            $error=sqrt($error);
            echo "Невязка=$error<br>";
            for($i=$t[$nData-1];$i<($hour+3);$i+=(1/12))
            {
                echo "t=".round($i,2).", ".date("r", $t0+$i)." p=".((pow($i,2)*$sol[0]+$sol[1]*$i+$sol[2]))."<br>";
            }
        }
        /*function solSLAY()
        {
        	$BMP085 = ReadMassiveData("BMP085", 0, 0);
        	$countBMP085 = count($BMP085);
            $p;
            $t;
            $n=3;
			if(!empty($_GET["num"]))
			{
				$n=$_GET["num"];
			}
            $step=12;
            for($i=0;$i<=$n*$step;$i+=1)
            {
            	$p[$i]=$BMP085[$countBMP085-2-($n*$step-$i)*3];
            	$t[$i]=strtotime($BMP085[$countBMP085-1-($n*$step-$i)*3]);
            }
            $t0=$t[0];
            $t[0]=1;
            for($i=1;$i<=$n*$step;$i++)
            {
            	$t[$i]-=$t0;
            	$t[$i]/=3600;
            	$t[$i]+=1;
            }
            for($i=0;$i<=$n*$step;$i++)
            {
            	echo "t=".$t[$i]." p=".$p[$i]."<br>";
            }
            $matrix;
            $str=0;
            $sol;
	            for($i=0;$i<$n;$i++)
	            {
	            	for($j=0;$j<$n;$j++)
	            	{
	            		$matrix[$i][$j]=($t[$i*$step],$n-$j-1);
	            		echo $matrix[$i][$j]." ";
	            	}
	            	$matrix[$i][$n]=$p[$i*$step];
	            	echo $matrix[$i][$j];
	            	echo "<br>";
	            }
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
        	echo "<br>C0 ".$sol[$n-1]."<br>";
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
        		echo "C".($n-2-$i+1)." ".$sol[$i]."<br>";
        	}

        	echo "<br> p(t)=";
        	for($i=0;$i<$n-1;$i++)
        	{	
        		$temp=round($sol[$i],7);
        		if($temp!=0)
        			echo $temp."x^1/".($n-$i-1);
        		if($sol[$i+1]>=0)
        			echo "+";
        	}
        	echo $sol[$n-1];
		    echo "<br>";
		    for($j=0;$j<count($p);$j++)
		    	echo round($t[$j],2)." ".$p[$j]."<br>";
		    echo "<br>";
		    for($j=0;$j<$n*$step*3;$j++)
		    {
		    	$ti=$j*5/60+1;
		    	$result=0;
	        	for($i=0;$i<$n;$i++)
			    {
			        $result+=round($sol[$i],7)*pow($ti,$n-$i-1);
			    }
			    echo "t=$ti, p=".$result."<br>";
		    }
		    
        }*/
?>