<?php 
include("file.php");
session_set_cookie_params(10000);
setcookie("TestCookie", 'value', time()+60*60*60);
session_start();
if(!isset($_SESSION['access']) || $_SESSION['access']!=true){
header("location:index.php");
}
else{ 
?>
<html>
<head>
<title>ds18b20</title>
<style type="text/css">
    #box{display: none;}
    #containerCharts #TScharts{ display: none;}
    #box:checked + #containerCharts #TScharts{ display: block;}
    </style>
<script>
    function chartDate(){
        var date = document.getElementById("date");
        var dateF = document.getElementById("dateFinish");
        var timeType = document.getElementById("timeType");
        if(timeType.value == "сбросить")
            window.location.href = 'http://94.19.250.166:8080/ds18b20.php';
         else{
            if(dateF.value == 0)
                dateF = date;
            //if(date.value == 0)
            //   date = dateF;
            window.location.href = 'http://94.19.250.166:8080/ds18b20.php?date=' + date.value + "&dateF=" + dateF.value;
        }
        
    }
</script>
</head>

<body>
<?php
if(!file_exists("ds18b20settings")) //проверяется наличе существование папки и файлов в ней
    mkdir("ds18b20settings", 0700);
if(!file_exists("ds18b20settings/data_max.txt"))
    writeFile("ds18b20settings/data_max.txt", "", 'w'); 
if(!file_exists("ds18b20settings/data_min.txt"))
    writeFile("ds18b20settings/data_min.txt", "", 'w'); 
if(!file_exists("ds18b20settings/count_ds18b20t.txt"))
    writeFile("ds18b20settings/count_ds18b20t.txt", "", 'w'); 
if(!file_exists("ds18b20settings/count_ds18b20.txt"))
    writeFile("ds18b20settings/count_ds18b20.txt", "", 'w');
if(!file_exists("ds18b20settings/data_round.txt"))
    writeFile("ds18b20settings/data_round.txt", "", 'w');  

$count_max;
if(rNumFile("ds18b20settings/count_ds18b20t.txt")>rNumFile("ds18b20settings/count_ds18b20.txt"))
    $count_max = rNumFile("ds18b20settings/count_ds18b20t.txt");
else
    $count_max = rNumFile("ds18b20settings/count_ds18b20.txt");

    $count_m;
    if (!empty($_GET["date"]))
        $count_m = date("n",strtotime($_GET["date"]) - 86400);//если задана конкретная дата, то считывается тот месяц
    else
        $count_m = date("n");

        $ds18b20;
        $countds18b20;
        while($countds18b20 < $count_max*2)
        {   
            $ds18b20buf = file("ds18b20/ds18b20_".$count_m."_".date("y").".txt");
            $countds18b20 += count($ds18b20buf);
            $count_m--;
        }
        $count_m++;
        
        for(; $count_m <= date("n"); $count_m++)
        {   
            $countds18b201 = count($ds18b20);
            $ds18b20buf = file("ds18b20/ds18b20_".$count_m."_".date("y").".txt");
            for($i = 0; $i < count($ds18b20buf); $i++)
                $ds18b20[$i+$countds18b201] = $ds18b20buf[$i];      
        }

        //for($i = 0; $i < count($ds18b20); $i++)
        //    echo $ds18b20[$i]."<br>";
//$countds18b20 = count($ds18b20);
//echo $count_max."<br>";
/**
*isset() - проверяет на наличие переменной/значения (равно NULL или нет)
*empty() - проверяет переменную на пустоту. Обращаю внимание, 0 - для нее тоже пустота!
**/
$file;// =  "Amount.txt";
if(isset($_POST['changeAmount'])) { 
$update_Amount = $_POST['amount'];
$n_Amount =$_POST['type_n'];
$n_Amount = trim($n_Amount);
switch ($n_Amount) {
    case 1:
        $file = "ds18b20settings/count_ds18b20t.txt";
        break;
    case 2:
        $file ="ds18b20settings/count_ds18b20.txt";
        break;
    default:
        $file ="ds18b20settings/count_ds18b20.txt";
    break;
    }
$update_Amount = trim($update_Amount); 
if(!empty($update_Amount)) 
        writeFile($file, $update_Amount, 'w');
}

if(isset($_GET['settings'])) { 
$SettingValue = $_GET['SettingValue'];
$ChartSettings =$_GET['ChartSettings'];
$ChartSettings = trim($ChartSettings);
switch ($ChartSettings) {
    case 0:
        $file = "ds18b20settings/data_max.txt";
        break;
    case 1:
        $file = "ds18b20settings/data_min.txt";
        break;
    case 2:
        $file = "ds18b20settings/data_round.txt";
        break;
    default:
    break;
    }
$SettingValue = trim($SettingValue); 
if(!empty($SettingValue)) 
        writeFile($file, $SettingValue, 'w');
}
?>
    <table align="left" >
        <tr>
            <td>
<div class="settings">
    <!--<a href="http://192.168.1.48:96/temp.php?T=value&&P=value">получить значение </a><br />-->

    </form>
    <font size="5">колличество значений: </font>
    <form method="POST" action="" enctype="">
    <input type="number" name="amount" />
    <select name="type_n" size = "1"> 
    <option value="1">таблица</option> 
    <option selected value="2">график</option> 
    </select>
    <input type="submit" name="changeAmount" value="OK" /> 
    </form>
    <input type="date" id="date" value="Текст">
    <input type="date" id="dateFinish" value="Текст">
    <input id="timeType" type="button" onclick="chartDate()" value=<?php  if (!empty($_GET["date"])){echo "сбросить";} else {echo "установить";}?>><br>

    </form>
    <font size="5">Настройки: </font>
    <form method="GET" action="" enctype="">
    <input type="text" name="SettingValue" />
    <select name="ChartSettings" size = "1"> 
    <option value="0">Максимальное значение</option> 
    <option selected value="1">Минимальное значение</option> 
    <option value="2">Округление</option> 
    </select>
    <select name="ChartType" size = "1"> 
    <option value="0">T</option> 
    <option value="3">P</option>
    </select>
    <input type="submit" name="settings" value="OK" /> 
    </form>
   
</div>
<?php
        $day;
        $time;
        $countds18b20 = count($ds18b20);
        $dataT;       
        $start = 0;
        $finish = 0;
        $count = rNumFile("ds18b20settings/count_ds18b20.txt") - 1;
        $j = $countds18b20-$count*2;
        if (!empty($_GET["date"]))
        {
            $day = strtotime($_GET["date"]);
            $dayF = strtotime($_GET["dateF"]);
            //if($dateF < $day )//если даты перепутанны
            //{   $temp = $day;
            //    $day = $dayF;
            //    $dayF = $temp;
            //}
            $d = $countds18b20;
            //timeDataN($BMP085, time()-$day, 3, 2, 2) //$count_str - кол-во значений в строке; $count_n - номер  значения в строке; type - 1 относительное значение, 2 абсолютное
            while(strtotime($ds18b20[$d-1]) > ($dayF+86400))
                $d-=2;
            $finish = $d;//timeDataN($BMP085, time()-$dayF - 86400, 3, 2, 2) - 1;
            while(strtotime($ds18b20[$d-1]) > $day)
                $d-=2;
            $start = $d+2;//timeDataN($BMP085, time()-$day, 3, 2, 2) - 1;
            //echo strtotime($BMP085[$i-1]);
            $count = ($finish - $start)/2;
            $j = $start;
        }
        else {
            $finish = $countds18b20;
            $start = $countds18b20-(rNumFile("ds18b20settings/count_ds18b20.txt")-1)*2;
        }
        $Tmin = 0;
        //$tableStart = $countds18b20-$count*2 - $finish;
        $time =strtotime($ds18b20[$finish-1]) - strtotime($ds18b20[$start-1]);
        for($i = 0/*, $j = $countBMP085-$count*3 - $start*/; $i<=$count; $i++, $j+=2)
        {
            $date;  
            if($time < 95000)
                $date = date("G:i",strtotime($ds18b20[$j-1]));
            else
                $date = date(" j F G:i, D",strtotime($ds18b20[$j-1]));

            $T = $ds18b20[$j-2];
            if(abs($T-$ds18b20[$j-4]) < 4 && $T < rNumFile("ds18b20settings/data_max.txt") && $T > rNumFile("ds18b20settings/data_min.txt"))//фильтрация неверных данных
            {
                if($Tmin < $T)
                $Tmin = $T;

                $dataT.=",['".$date."', ".$T."]";
            }  
        }

    $time =strtotime($ds18b20[$countds18b20-1]) - strtotime($ds18b20[$countds18b20-1-(rNumFile("ds18b20settings/count_ds18b20.txt")-1)*2]);
    $hour = (int)($time/3600);
    $minutes = (int)(($time/60)%60);
    if (!empty($_GET["date"]))
        echo "График с ".date("j F",strtotime($_GET["date"]))." по ".date("j F",strtotime($_GET["dateF"]))."<br>";
    else
        echo "График за последние ".$hour." часов ".$minutes." минут<br>";

    //echo $finish." ".$start."</br>";
?>
<!--</td>-->
    <script type="text/javascript" src="charts.js"></script>
    <script type="text/javascript">

      google.charts.load('current', {packages: ['corechart', 'line']});
      google.charts.setOnLoadCallback(drawBackgroundColor);
<?php   

        
        $mobiles = array("Windows",/*"Nexus"*/);
        $width = 470;
        $height = 275;
        foreach( $mobiles as $mobile ) {
            if( preg_match( "#".$mobile."#i", $_SERVER['HTTP_USER_AGENT'] ) ) {
                $width = 650;
                $height = 275;
            }
        }
?>
      function drawBackgroundColor() {

      var dataT = google.visualization.arrayToDataTable([
          ['Time', 'Temperature']
          <?php echo $dataT; ?>
        ]);

        var optionsT = {
          hAxis: {title: 'Time',  titleTextStyle: {color: '#333'}},
          vAxis: {title: 'Temperature', minValue: <?php echo $Tmin; ?>},
          width:  <?php echo $width; ?>/*screen.width/2*/,
          height: <?php echo $height; ?>
        };


      var chartT = new google.visualization.LineChart(document.getElementById('ds18b20Temperature_chart'));
      chartT.draw(dataT, optionsT);
    }

    </script>
<input type="checkbox" id="box"/>
<label id="containerCharts" for="box">
    <b><div id="button" >Графики Thingspeak</div></b>
    <div id="TScharts">
<?php
    echo "<iframe width=\"450\" height=\"260\" style=\"border: 1px solid #cccccc;\" src=\"https://thingspeak.com/channels/83130/charts/1?bgcolor=%23ffffff&color=Green&dynamic=true&max=".rNumFile("ds18b20settings/data_max.txt")."&min=".rNumFile("ds18b20settings/data_min.txt")."&results=".rNumFile("ds18b20settings/count_ds18b20.txt")."&round=".rNumFile("ds18b20settings/data_round.txt")."&type=line\"></iframe>";
?>
    </div>
</label>    

    <div id="ds18b20Temperature_chart"></div>
<?php
//echo $dataT;
echo /*"<td>*/"<div id='div_table1' class=\"graph\">";
echo "</div></td>";
    echo "<td><div class=\"value_table\"><table border=\"3\" align=\"center\">";
    echo  "<tr> 
            <td> Temperature </td>
            <td> Date </td>
        </tr>";
    echo "<tr>";
            if (!empty($_GET["date"]))//если задана конкретная дата для данных, то строится таблица для этого дня
                $countBMP085 = $finish;
            for($n = 0; ($n<rNumFile("ds18b20settings/count_ds18b20t.txt"))&&($j>=0); $n++,$countds18b20-=2)
            //for($j = rNumFile("Amount.txt"); $j>=0;$j--)
            {
                echo "<td>T=".$ds18b20[$countds18b20-2]."</td>";
                echo "<td>".$ds18b20[$countds18b20-1]."</td></tr>";
            }
    echo "</table></div></td></tr>";

?>
</body>
</html>
<?php } ?>