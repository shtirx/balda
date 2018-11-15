<?php
include("file.php");
include("forecast.php");
        $arr=[1,0,1,1];
        $i = array_keys($arr,max($arr));
        echo round(array_sum($i)/count($i));
    /*$weather = file("weatherNew.txt");
    $DHT21 = ReadMassiveData("ds18b20", "01-07-2016","27-01-2017");
    //echo timeData($DHT21, time()-$t, 4, 2, 2);
    $DHT21 = ReadMassiveData("DHT21", date("r", time()-3600),date("r", time()));
    echo timeData($DHT21, 1200, 4, 1, 2);
    echo "<br>";
    echo timeData($DHT21, 1200, 4, 2, 2);
    for($i=0;$i<count($weather) ;$i+=5)
    {   
        $t = strtotime($weather[$i+3]);
        if($t <= 1477427400 || $t > strtotime("30-01-2017"))
            continue;
        $w = trim($weather[$i]);
        if(mb_detect_encoding($weather[$i]) != 'UTF-8')
            $w = iconv(mb_detect_encoding($weather[$i]),'UTF-8',$weather[$i]);
        $code = trim($weather[$i+1]);
        $p=trim($weather[$i+2]);
        
        $s = trim($weather[$i+4]);
        $tm = null;
        //$h = null;
        if($t <= strtotime("27-01-2017"))
        {
            $tm = timeData($DHT21, time()-$t, 2, 1, 2);
            //$h = timeData($DHT21, time()-$t, 4, 2, 2);
        }
        mysqli_query($mysqli,"INSERT INTO weather VALUES (null, '$w',$code,$p,$t, $s,$tm,null)");  
    }
    
    /*$dict=file("dictionary/word_rus.txt");
     mysqli_query($mysqli,"CREATE TABLE dictionary (
      id INT AUTO_INCREMENT,
      word TINYTEXT,
      definition TEXT,
      PRIMARY KEY(id)
    )");
    for($i=0;$i<count($dict);$i++)
    {   
        $word = trim($dict[$i]);
        mysqli_query($mysqli,"INSERT INTO dictionary VALUES (null, '$word', '')");
    }*/
    /*$sql = "CREATE TABLE listOfGames (
      id INT AUTO_INCREMENT,
      name TEXT,
      PRIMARY KEY(id)
    )";
    request($mysqli,$sql);*/
    /*for($j=1;$j<$countGemes;$j++)
    {   
        
        if(file_exists("balda/game$j"))
        {   
            $filesize = file("balda/game${j}/size.txt");
            $filefield = file("balda/game${j}/field.txt");
            $curPlayer = rNumFile("balda/game${j}/curPlayer.txt");
            $playAudio = rNumFile("balda/game${j}/playAudio.txt");

            $sql = "CREATE TABLE game".$j." (
              id INT AUTO_INCREMENT,
              field TEXT,
              PRIMARY KEY(id)
            )";
            mysqli_query($mysqli,$sql);
            for($i=0;$i<$filesize[1];$i++)
            {
                $sql = "CREATE TABLE game".$j."Player".$i." (
                  id INT AUTO_INCREMENT,
                  word TEXT,
                  PRIMARY KEY(id)
                )";
                mysqli_query($mysqli,$sql);
                $player = file("balda/game${j}/Player".($i+1).".txt");
                for($k=0;$k<count($player);$k++)
                {
                    $sql = "INSERT INTO game".$j."Player".$i." VALUES (null, '".$player[$k]."')";
                    mysqli_query($mysqli,$sql);
                }
                
                $sql = "INSERT INTO game$j VALUES (null, '$player[0]')";
                mysqli_query($mysqli,$sql);
            }
            mysqli_query($mysqli,"INSERT INTO listOfGames VALUES (null, 'game".$j."')");
            $sql = "INSERT INTO game$j VALUES (null, $filesize[0])";
            mysqli_query($mysqli,$sql);
            $sql = "INSERT INTO game$j VALUES (null, $filesize[1])";
            mysqli_query($mysqli,$sql);
            for($istr=0;$istr<$filesize[0];$istr++)
            {   
                $str =  iconv('CP1251', 'UTF-8',$filefield[$istr]);
                $sql = "INSERT INTO game$j VALUES (null,'$str')";   
                mysqli_query($mysqli,$sql);
            }
            $time= filectime("balda/game${j}/size.txt");
            mysqli_query($mysqli,"INSERT INTO game$j VALUES (null, '$time')");
            mysqli_query($mysqli,"INSERT INTO game$j VALUES (null, '$playAudio')");
            $sql = "INSERT INTO game$j VALUES (null, '$curPlayer')";    
            mysqli_query($mysqli,$sql);
        }
    }*/
?>