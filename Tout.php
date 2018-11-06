<?php
include("file.php"); 
date_default_timezone_set('Europe/Minsk');
if (!empty($_GET["T"])) 
 { 
 	//$ourFileName = "BMP085.txt";
 	if(($_GET["T"] != "value"))
 	{
		writeFile("ds18b20/ds18b20_".date("n")."_".date("y").".txt", $_GET["T"]."\n ".date("r")."\r\n", 'a');
		//$adr = "http://api.thingspeak.com/update?key=RR09YQEEARY892PI&field1=".$_GET["T"];
    	//$fp = fopen($adr, "r");
 		//echo " Получены новые вводные: значение - ".$_GET["T"]." ".$_GET["P"];
        //echo /*"delay=s".*/rNumFile("UpdateTime.txt")/*."p"*/;
         //http://api.thingspeak.com/update?key=2PUS370UGTVHO600&field1=$_GET["T"]&field2=$_GET["P"]
        //$adr = "http://api.thingspeak.com/update?api_key=RR09YQEEARY892PI&field1=".$_GET["T"];
        //fopen($adr, "r");
        echo "data received";
 	}	
 	/*else
 		{
            $lines = rFile("BMP085.txt");
    		foreach($lines as $single_line)
    		{
    			if($i == 0)
    			{
    				echo "T=".$single_line;
    				$i++;
    			}	
    			else
    			{
    				echo " P=".$single_line."<br />\n";
    				$i = 0;
    			}		
    		}
        		
		}*/
 } 
 else { 
 	echo "Переменные не дошли. Проверьте все еще раз."; 
 }
?>