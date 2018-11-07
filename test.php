<?php
include("file.php");
		/*$mysqli = new mysqli("127.0.0.1", "root","", "weather");
            $timeSeason=strtotime("01-09-2018");
            echo $timeSeason."<br>";
            $res = mysqli_query($mysqli,"SELECT `pressure`,`time` WHERE `time` > ".$timeSeason.'"');
            $arrW = mysqli_fetch_all($res,MYSQLI_NUM);
            echo $arrW[0];*/
         solSLAY();
        function solSLAY()
        {
        	$BMP085 = ReadMassiveData("BMP085", 0, 0);
        	$countBMP085 = count($BMP085);
            //$p =[753.95,754.03,754.22,754.01, 753.96, 753.89];
            $p;
            $t;// =[1, 20.18, 60,90,120,150];
            $n=3;//count($p);
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
	            		//if(($n-$j-1)!=0)
	            			$matrix[$i][$j]=/*$t[$i*$step]**(1/($n-$j-1))*/pow($t[$i*$step],$n-$j-1);
	            		//else
	            		//	$matrix[$i][$j]=$t[$i*$step];
	            		echo $matrix[$i][$j]." ";
	            	}
	            	$matrix[$i][$n]=$p[$i*$step];
	            	echo $matrix[$i][$j];
	            	echo "<br>";
	            }
	           //echo "<br>";
	        for($str=0; $str<$n;$str++)
	        {
	            	/*$p[$str]/=$matrix[$str][$str];
	            	for($j=$str;$j<$n;$j++)
	            	{
	            		$matrix[$str][$j]/=$matrix[$str][$str];
	            	}*/
	            
	            for($i=$str+1;$i<$n;$i++)
	            {
	            	$temp=$matrix[$i][$str];
	            	for($j=$str;$j<$n+1;$j++)
	            	{
	            		$matrix[$i][$j]-=($matrix[$str][$j]/$matrix[$str][$str]*$temp);
	            	}
	            }
	        
            /*for($i=0;$i<$n;$i++)
            {
            	for($j=0;$j<$n+1;$j++)
            	{
            		echo $matrix[$i][$j]." ";
            	}
            	echo "<br>";
            }
            echo "<br><br>";*/
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
        	/*$result;
        	for($j=0;$j<$n;$j++)
        	{	
        		$result=0;
        		for($i=0;$i<$n;$i++)
		        	{
		        		$result+=$sol[$i]*pow($t[$j],$n-$i-1);
		        	}
		        echo "<br> result $j: ".round($result,2);
        	}*/
        	/*$ti=140;
        	echo "<br> predictions: t=$ti: ";
        	$result=0;
        	for($i=0;$i<$n;$i++)
		    {
		        $result+=$sol[$i]*pow($ti,$n-$i-1);
		    }
		    echo $result;*/
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
			    	//if(($n-$i-1)!=0)
			        	$result+=round($sol[$i],7)*/**($ti**(1/($n-$i-1)));*/pow($ti,$n-$i-1);
			    	//else
			    	//	$result+=round($sol[$i],7)*$ti;
			    }
			    echo "t=$ti, p=".$result."<br>";
		    }
		    
        }
?>
