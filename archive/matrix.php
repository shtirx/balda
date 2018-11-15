<?php 
include("file.php");
session_set_cookie_params(10000);
//setcookie("TestCookie", '1998nikita', time()+3600*24*1);
session_start();
date_default_timezone_set('Europe/Minsk');
if((!isset($_SESSION['access']) || $_SESSION['access']!=true) && $_COOKIE["TestCookie"] != '1998nikita'){
header("location:index.php");
}
else{ ?>
<html>
<head>
	<style type="text/css">
   .user {
    width: 190px; /* Ширина поля с учетом padding */
    height: 150px; /* Высота */
   }
   .user textarea {
    background: transparent; /* Прозрачный фон */
    margin: 0px; /* Отступы от линии */
    width: 190px; /* Ширина поля */
    padding: 5px 0 5px 5px; /* Поля в тексте */
    height: 150px; /* Высота */
   }
  </style>
</head>
<body>
<?php
$NOD = false;
function changeStr($matrix,$n,$m,$x)
{
	for($i=0;$i<$x;$i++)
	{
		$t = $matrix[$n][$i];
		$matrix[$n][$i] = $matrix[$m][$i];
		$matrix[$m][$i] = $t;
	}
	return $matrix;
}

function countNum($m)
{
	$n=1;
	if($m<0)
		$n++;
	for(;$m/10>=1;$n++,$m/=10);
	return $n;
}
function printMatrix($m,$x,$y)
{
	$max = abs($m[0][0]);
	
	for($j=0;$j<$y;$j++)
	{
		for($i=0;$i<$x;$i++)
		{
			if(abs($m[$j][$i]) > $max)
				$max = abs($m[$j][$i]);
		}
	}
	$nmax=countNum($max);
	for($j=0;$j<$y;$j++)
	{
		for($i=0;$i<$x;$i++)
		{	
			$n=countNum(abs($m[$j][$i]));
			for($k=0; $k<$nmax-$n; $k++)
				echo " ";
			echo $m[$j][$i]." ";
		}
		echo "<br>";
	}
	echo "<br>";
} 

function cone($matrix,$x, $y, $stage)
{
	$koef;
	if($matrix[$stage][$stage] != 1)
	for($j=$stage, $stop = false;$j<$y && !$stop;$j++)
	{
		for($i=$stage;$i<$y && !$stop;$i++)
		{
			if($matrix[$i][$stage] != 0 && $i != $j)
			{
				for($koef=1;abs($koef*$matrix[$i][$stage])<=abs($matrix[$j][$stage])+1;$koef++){
					//echo "k=".$koef." i=$i j=$j<br>";	
					if(abs($matrix[$j][$stage]-$koef*$matrix[$i][$stage]) == 1)
					{
						for($k=0;$k<$x;$k++)
							$matrix[$j][$k] = $matrix[$j][$k]-$koef*$matrix[$i][$k];
						echo "Вычтем ".($i+1)." строку, умноженную на $koef, из ".($j+1)."<br>";
						$stop = true;
					} else if(abs($matrix[$j][$stage]+$koef*$matrix[$i][$stage]) == 1)
					{
						for($k=0;$k<$x;$k++)
							$matrix[$j][$k] = $matrix[$j][$k]+$koef*$matrix[$i][$k];
						echo "Cложим ".($i+1)." строку, умноженную на -$koef, с ".($j+1)."<br>";
						$stop = true;
					}
				}		
			}	
		}
	}
	//$matrix = findone($matrix,$y,$x,$stage);
	return $matrix;
}

function czero($matrix,$x, $y, $stage)
{
	for($j=1+$stage; $j<$y && !$stopTransform;$j++)
	{
		$koef;
		if($matrix[$j][$stage] != 0)
		{	
			for($koef=1;$koef*abs($matrix[$stage][$stage]) != abs($matrix[$j][$stage]) && $koef<500;$koef++);
			if($koef>=500)
			{
				echo "Преобразования невозможны<br>";
				return 0;
			} else if($koef*$matrix[$stage][$stage] == $matrix[$j][$stage])
			{	echo "Домножим ".($stage+1)." стоку на -$koef и сложим с ".($j+1)." строкой<br>";
				for($k=0;$k<$x;$k++)
				$matrix[$j][$k] = -$koef*$matrix[$stage][$k]+$matrix[$j][$k];
			} else {
				echo "Домножим ".($stage+1)." стоку на $koef и сложим с ".($j+1)." строкой<br>";
				for($k=0;$k<$x;$k++)
					$matrix[$j][$k] = $koef*$matrix[$stage][$k]+$matrix[$j][$k];
			}
		}
	}
	return $matrix;
}

function equals($matrix,$x, $y, $stage)
{
	for($j=$stage;$j<$y-1;$j++)
	{
		for($i=$j+1;$i<$y;$i++)
		{
			$k=0;
			for($k=$stage;$k<$x-1;$k++)
			{
					if((!($matrix[$i][$k] == 0 && $matrix[$j][$k] == 0)) && ( $matrix[$i][$k] == 0 || $matrix[$i][$k+1] == 0 || $matrix[$j][$k]/$matrix[$i][$k] != $matrix[$j][$k+1]/$matrix[$i][$k+1]))	
						break;
			}
			if($k == $x-1)
			{
				for($z=0;$z<$x;$z++)
					$matrix[$i][$z] = 0;
				echo "Строки ".($j+1)." и ".($i+1)." пропорциональны<br>";
			}
		}
	}
	return $matrix;
}
function iszero($matrix,$y)
{
	for($i=0;$i<$y-1;$i++)
	{
		if($matrix[$i+1][$i] != 0)
			return false;
	}
	return true;
}

function NOD($matrix,$x,$y,$stage)
{
	$min=abs($matrix[$stage][$stage]);
	for($i = $stage; $i<$y;$i++)
	{
		if(abs($matrix[$i][$stage]) < $min && abs($matrix[$i][$stage]) != 0)
			$min = $matrix[$i][$stage];
	}
	//echo "min=".$min."<br>";
	for($i = $stage; $i<$y;$i++)
	{
		if($matrix[$i][$stage] != 0)
			if($matrix[$i][$stage]%$min != 0)
			{
				$NOD = false;
				return $matrix;
			}	
	}
	$i;
	for($i = $stage; $i<$y-1 && ($matrix[$i][$stage] == $matrix[$i+1][$stage]);$i++);
	if($i == $y-1)
	{
		$NOD = true;
		return findone($matrix,$y,$x,$stage, $min);
	} else 
	{
		$NOD = false;
		return $matrix;
	}
}

function findone($matrix,$y,$x,$stage, $num = 1)
{
	for($i = $stage;$i<$y;$i++)
	{
		if(abs($matrix[$i][$stage]) == $num && $i != $stage)
		{
			$matrix = changeStr($matrix,$stage,$i,$x);
			echo "Поменяем ".($stage+1)." и ".($i+1)." строки местами<br>"; 
			break;
		}	
	}
	return $matrix;
}
function CMatrix($matrix,$x,$y)
{
	printMatrix($matrix,$x,$y);
	for($stage = 0; $stage < $y && !iszero($matrix,$y); $stage++)
	{	//echo "stage=$stage<br>";
		$matrix = equals($matrix,$x,$y,$stage);
		$matrix = NOD($matrix,$x,$y,$stage);
		if(iszero($matrix,$y))
		{
			printMatrix($matrix,$x,$y);
			break;
		}
			
		if(!$NOD)
		{
			$matrix = findone($matrix,$y,$x,$stage);
			if(abs($matrix[$stage][$stage]) != 1)
			{
				$matrix = cone($matrix,$x,$y,$stage);
				if(abs($matrix[$stage][$stage]) != 1)
				{
					printMatrix($matrix,$x,$y);
					$matrix = findone($matrix,$y,$x,$stage);
				}	
			} 		
			printMatrix($matrix,$x,$y);
		}
		$matrix = czero($matrix,$x,$y,$stage);
		if($matrix == 0)
			$stage=$y;
		printMatrix($matrix,$x,$y);
	}
}
?>
<?php
	$matrix[10][10];
	$y;
	$x;
	$start = false;
	if(isset($_POST['setMatrix'])) { #если нажата клавиша формы
	$matrixStr = trim($_POST['matrix']); #убираем пробелы по краям, если они есть
	//if(!empty($update_time))
	//        writeFile($Name."settings/UpdateTime.txt", $update_time*$timef, 'w');
	//echo $matrixStr."<br>";
	//echo "x=$x y=$y<br>";
	$matrixY = explode(";", $matrixStr);
	$y=count($matrixY)-1;
	//for($i=0;$i<$y;$i++)
	//	echo $matrixY[$i]."<br>";
	for($i=0;$i<$y;$i++)
	{
		$matrixX=explode(" ", trim($matrixY[$i]));
		$x=count($matrixX);
		for($j=0;$j<$x;$j++)
			$matrix[$i][$j] = trim($matrixX[$j]);			
	}
	$x=count($matrixX);
		$start = true;
	}?>
</form>
    <font size="5">Матрица: </font>
    <form method="POST" action="" enctype="">
    <div class="user"><textarea cols="10" rows="10"  placeholder = 
    	"	1 2 3;
    	4 5 6;
    	7 8 9;" name="matrix"><?php echo $matrixStr;?></textarea></div>
    <input type="submit" name="setMatrix" value="OK" /> 
</form>	
<?php
if($start)
	CMatrix($matrix,$x,$y);
?>
</body>
</html>
<?php } ?>