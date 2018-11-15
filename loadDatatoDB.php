<?php
	include("math.php");
	$mysqli = new mysqli("127.0.0.1", "root","", "Sensors");
	for($j=1;$j<=10;$j++)
	{
		$BMP085=file("BMP085/BMP085_${j}_18.txt");
		$count = count($BMP085);
		for($i=0;$i<$count;$i+=3)
		{
			$t=$BMP085[$i];
			$p=$BMP085[$i+1];
			$d=strtotime($BMP085[$i+2]);
			mysqli_query($mysqli,"INSERT INTO `BMP085` VALUES (null, $t,$p,null,$d)");
		}
	}
?>