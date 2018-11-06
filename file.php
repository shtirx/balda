<?php
	function translit($str) {
	   $rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
	   $lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
	   return str_replace($rus, $lat, $str);
	}
	function writeFile($ourFileName, $data, $type)
	{
		/*$ourFileHandle = fopen($ourFileName, $type) or die("can't open file");
		fclose($ourFileHandle);*/

		$fh = fopen($ourFileName, $type) or die("can't open file");
		fwrite($fh, $data);
		fclose($fh);
	}
	function rFile($ourFileName)
	{
		$fh = fopen($ourFileName, 'r');
		$theData = fgets($fh, filesize($ourFileName));
		fclose($fh);
		$lines = file($ourFileName);
		return $lines;
	}

	function rNumFile($ourFileName, $count=10)
	{
		$fp=fopen($ourFileName,'r');
        $N=fread($fp,$count); //максимальное считуемое число пятизначно
        fclose($fp);
		return $N;
	}

	function rLineFile($ourFileName, $count)
	{
		$fp=fopen($ourFileName,'r');
        $N=fread($fp,$count); //максимальное считуемое число двухзначно
        fclose($fp);
		return $N;
	}

	function printData($Name, $type=0)
	{
		$DataStr;
		$count_max;
		$ds18b20 = ReadMassiveData($Name, 0, 0);
		$PageSettings = file("Pages/".$Name.".txt");
		for($i = 0; $i<count($PageSettings); $i++)
		{
		    $PageSettings[$i] = trim($PageSettings[$i]);
		}
		$Name = $PageSettings[0];
		$AmountData = $PageSettings[1];
		$TimeUpdate = $PageSettings[2];
		$archive = $PageSettings[3];
		if($archive != "on" && $type == 0)
		{
			$countds18b20 = count($ds18b20);
			$time = strtotime($ds18b20[$countds18b20-1]); 
			$DataStr = "<b><a href=\"Page.php?name=".$Name."\"  align = \"center\">".$Name."</a></b><br>Сейчас ";
			for($i = 0, $n = count($PageSettings)-$AmountData; $i <$AmountData-1; $i++, $n++)
			{
				$DataStr.=$PageSettings[$n][0]."=".trim($ds18b20[$countds18b20-$AmountData+$i])." ";
			}
			$DataStr.="<br> Последнее обновление в ". date("G:i", $time)." (".(int)((time()-$time)/60)." минут назад)</br><br>";
		}
		else if($archive == "on" && $type == 1)
			$DataStr = "<b><a href=\"Page.php?name=".$Name."\"  align = \"center\">".$Name."</a></b><br>";
	return $DataStr;
	}

	function ReadMassiveData($Name, $dateS=0, $dateF=0)//,$absPath="")
	{
		$ds18b20=array();
		$count_max;
		$countTable;
		$countChart;
		if(empty($absPath))
		{
			$countTable = rNumFile($Name."settings/count_t.txt");
			$countChart = rNumFile($Name."settings/count.txt");
		}
		
		$PageSettings = file("Pages/".$Name.".txt");

		if($countTable>$countChart)
		    $count_max = $countTable;
		else
		    $count_max = $countChart;

	    $countds18b20;

	    $count_mf;
	    $count_m;
	    $yearStart;
	    $yearFinish;
	    if($dateS != 0 )
	    {
	    	$count_m = date("n",strtotime($dateS));//если задана конкретная дата, то считывается тот месяц
	    	$yearStart=date("y",strtotime($dateS));
	    }
    	else {
    		$count_m = date("n");
    		$yearStart=date("y");
    	}
        	

    	//echo $yearStart;
        if($dateF != 0 ) {
        	$count_mf = date("n",strtotime($dateF));//если задана конкретная дата, то считывается тот месяц
        	$yearFinish=date("y",strtotime($dateF));
        }
    	else {
    		$count_mf = date("n");
    		$yearFinish=date("y");
    	}
        	

	    while($countds18b20 < $count_max*$PageSettings[1])
	    {   
	    	if(file_exists(/*$absPath."/".*/$Name."/".$Name."_".$count_m."_".$yearStart.".txt"))
	        {
				$ds18b20buf = file(/*$absPath."/".*/$Name."/".$Name."_".$count_m."_".$yearStart.".txt");
	        	$countds18b20 += count($ds18b20buf);
	        }
	        $count_m--;
	        if($count_m == 0){
	        	$count_m = 12;
	        	$yearStart--;
	        }
	    }
	    if($count_m<12)
	    	$count_m++;
	     
	    for(; $yearStart < $yearFinish || $count_m <= $count_mf; $count_m++)
	    {   
	    	if($count_m == 13){
	    		$count_m = 1;
	    		$yearStart++;
	    	}
	        //$countds18b201 = count($ds18b20);
	        if(file_exists(/*$absPath."/".*/$Name."/".$Name."_".$count_m."_".$yearStart.".txt"))
	        	$ds18b20buf = file(/*$absPath."/".*/$Name."/".$Name."_".$count_m."_".$yearStart.".txt");
	        //for($i = 0; $i < count($ds18b20buf); $i++)
	            //$ds18b20[$i+$countds18b201] = $ds18b20buf[$i];
	         $ds18b20=array_merge($ds18b20,$ds18b20buf);      
	     }
	    return $ds18b20;
	}
	function ReadMassiveDataOff($Name, $dateS=0, $dateF=0)//,$absPath="")
	{
		$ds18b20=array();
		$count_max;
		$countTable;
		$countChart;
		if(empty($absPath))
		{
			$countTable = rNumFile($Name."settings/count_t.txt");
			$countChart = rNumFile($Name."settings/count.txt");
		}
		
		$PageSettings = file("Pages/".$Name.".txt");

		if($countTable>$countChart)
		    $count_max = $countTable;
		else
		    $count_max = $countChart;

	    $countds18b20;

	    $count_mf;
	    $count_m;
	    $yearStart;
	    $yearFinish;
	    if($dateS != 0 )
	    {
	    	$count_m = date("n",strtotime($dateS));//если задана конкретная дата, то считывается тот месяц
	    	$yearStart=date("y",strtotime($dateS));
	    }
    	else {
    		$count_m = date("n");
    		$yearStart=date("y");
    	}
        	

    	//echo $yearStart;
        if($dateF != 0 ) {
        	$count_mf = date("n",strtotime($dateF));//если задана конкретная дата, то считывается тот месяц
        	$yearFinish=date("y",strtotime($dateF));
        }
    	else {
    		$count_mf = date("n");
    		$yearFinish=date("y");
    	}
        	

	    while($countds18b20 < $count_max*$PageSettings[1])
	    {   
	    	if(file_exists(/*$absPath."/".*/$Name."/".$Name."_".$count_m."_".$yearStart.".txt"))
	        {
				$ds18b20buf = file(/*$absPath."/".*/$Name."/".$Name."_".$count_m."_".$yearStart.".txt");
				$countds18b20 += count($ds18b20buf);
	        	$ds18b20=array_merge($ds18b20buf,$ds18b20);
	        }
	        $count_m--;
	        if($count_m == 0){
	        	$count_m = 12;
	        	$yearStart--;
	        }
	    }
	    if($count_m<12)
	    	$count_m++;
	     
	    /*for(; $yearStart < $yearFinish || $count_m <= $count_mf; $count_m++)
	    {   
	    	if($count_m == 13){
	    		$count_m = 1;
	    		$yearStart++;
	    	}
	        //$countds18b201 = count($ds18b20);
	        if(file_exists($Name."/".$Name."_".$count_m."_".$yearStart.".txt"))
	        	$ds18b20buf = file($Name."/".$Name."_".$count_m."_".$yearStart.".txt");
	        //for($i = 0; $i < count($ds18b20buf); $i++)
	            //$ds18b20[$i+$countds18b201] = $ds18b20buf[$i];
	         $ds18b20=array_merge($ds18b20,$ds18b20buf);      
	     }*/
	    return $ds18b20;
	}
	function clearFile($file)
	{
		writeFile("$file", "","w"); 
	}
	function getSslPage($url) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($ch, CURLOPT_HEADER, false);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_REFERER, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $result = curl_exec($ch);
	    curl_close($ch);
	    return $result;
	}
	function exec_script($url, $params = array())
	{
	    $parts = parse_url($url);
	 
	    if (!$fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80))
	    {
	        return false;
	    }
	 
	    $data = http_build_query($params, '', '&');
	 
	    fwrite($fp, "POST " . (!empty($parts['path']) ? $parts['path'] : '/') . " HTTP/1.1\r\n");
	    fwrite($fp, "Host: " . $parts['host'] . "\r\n");
	    fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
	    fwrite($fp, "Content-Length: " . strlen($data) . "\r\n");
	    fwrite($fp, "Connection: Close\r\n\r\n");
	    fwrite($fp, $data);
	    fclose($fp);
	 
	    return true;
	}
?>