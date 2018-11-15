<?php
	$unixTime=$_GET["unix_time"];
	echo Date("r",$unixTime);
?>