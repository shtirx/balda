<?php
	include("file.php");
	date_default_timezone_set('Europe/Moscow');
	function checkUserF($psw,$login)
	{
		$psw=trim($psw);
		$n=rNumFile("balda/users/next.txt");
	    for($i=0;$i<$n;$i++)
	    {
	    	if(!file_exists("balda/users/user".$i.".txt"))
	    		continue;
	    	$temp=file("balda/users/user".$i.".txt");
	    	$hashPsw = $temp[1];
	    	if (hash_equals(trim($hashPsw), crypt($psw, trim($hashPsw))) || (strcmp($login,trim($temp[0])) == 0 ))
	      	//if($hashPsw==$pass)
	      	{
		        return $temp[0];
	        }
	    }
	    return false;
	}
	function checkUser($psw,$login)
	{
	    $psw=trim($psw);
		$mysqli = new mysqli("127.0.0.1", "root","", "balda");
		$res = mysqli_query($mysqli, "SELECT name, password FROM users");
		$HashPswArr = mysqli_fetch_all($res,MYSQLI_NUM);
		for($i=0;$i<$res->num_rows;$i++)
		{
			$hashPsw = $HashPswArr[$i][1];
			if (hash_equals(trim($hashPsw), crypt($psw, trim($hashPsw))) || (strcmp($login,trim($HashPswArr[$i][0])) == 0 ))
			{
		        return $HashPswArr[$i][0];
	        }
	    }
	    return false;
	}
	function checkUserSing($psw,$login)
	{
	    $psw=trim($psw);
		$mysqli = new mysqli("127.0.0.1", "root","", "balda");
		$res = mysqli_query($mysqli, "SELECT name, password FROM users");
		$HashPswArr = mysqli_fetch_all($res,MYSQLI_NUM);
		for($i=0;$i<$res->num_rows;$i++)
		{
			$hashPsw = $HashPswArr[$i][1];
			if (hash_equals(trim($hashPsw), crypt($psw, trim($hashPsw))) && (strcmp($login,trim($HashPswArr[$i][0])) == 0 ))
			{
		        return $HashPswArr[$i][0];
	        }
	    }
	    return false;
	}
	function checkUserSingF($psw,$login)
	{
		$psw=trim($psw);
		$n=rNumFile("balda/users/next.txt");
	    for($i=0;$i<$n;$i++)
	    {
	    	if(!file_exists("balda/users/user".$i.".txt"))
	    		continue;
	    	$temp=file("balda/users/user".$i.".txt");
	    	$hashPsw = $temp[1];
	    	if (hash_equals(trim($hashPsw), crypt($psw, trim($hashPsw))) && (strcmp($login,trim($temp[0])) == 0 ))
	      	//if($hashPsw==$pass)
	      	{
		        return $temp[0];
	        }
	    }
	}
	function checkPsw($psw,$type=0)
	{
		$psw=trim($psw);
		$mysqli = new mysqli("127.0.0.1", "root","", "balda");
		$res = mysqli_query($mysqli, "SELECT name, password, id FROM users");
		$HashPswArr = mysqli_fetch_all($res,MYSQLI_NUM);
		for($i=0;$i<$res->num_rows;$i++)
		{
			$hashPsw = $HashPswArr[$i][1];
			if (hash_equals(trim($hashPsw), crypt($psw, trim($hashPsw))))
			{
	      		if($type==0)
		       		return $HashPswArr[$i][0];
		        else
		        	return $HashPswArr[$i][2];
	        }
		}
	    return "";
	}
	function checkPswF($psw,$type=0)
	{
		$psw=trim($psw);
		$n=rNumFile("balda/users/next.txt");
	    for($i=0;$i<$n;$i++)
	    {
	    	if(!file_exists("balda/users/user".$i.".txt"))
	    		continue;
	    	$temp=file("balda/users/user".$i.".txt");
	    	$hashPsw = $temp[1];
	    	if (hash_equals(trim($hashPsw), crypt($psw, trim($hashPsw))))
	      	//if($hashPsw==$pass)
	      	{
	      		if($type==0)
		        	return $temp[0];
		        else
		        	return $i;
	        }
	    }
	    return "";
	}
	function checkGame($nameFile,$login)
	{
		$nameFile=trim($nameFile);
		$login=trim($login);
		for($i=1;file_exists("balda/".$nameFile."/Player".$i.".txt");$i++)
		{
			$file=file("balda/".$nameFile."/Player".$i.".txt");
			if(strcmp(trim($file[0]),$login) == 0)
				return true;
		}
		return false;
	}
	function checkGameB($play,$login, $numOfPlayers)
	{
		//$play->data_seek(3);
        //$numOfPlayers = $play->fetch_assoc()['field'];
		for($i=0;$i<$numOfPlayers;$i++)
		{
			$play->data_seek($i);
        	$player = $play->fetch_assoc()['field'];
        	if(strcmp(trim($player),$login) == 0)
        	{
        		return true;
        	}	
		}
		return false;
	}

	function deleteProfile($login)
	{
	    $mysqli = new mysqli("127.0.0.1", "root","", "balda");
		if(mysqli_query($mysqli, "DELETE FROM users WHERE name='$login'"))
			return true;
	    return false;
	}
	function deleteProfileF($login)
	{
	    $users=rNumFile("balda/users/next.txt");
	    for($i=0;$i<$users;$i++)
	    {
	    	if(file_exists("balda/users/user$i.txt"))
	    	{
	    		$temp=file("balda/users/user$i.txt");
	    		if(strcmp(trim($login),trim($temp[0])) == 0)
	    		{
	    			unlink("balda/users/user$i.txt");
	    			unlink("balda/users/user$i"."status.txt");
	    			return true;
	    		}	
	    	}
	    }
	    return false;
	}
		function updateStatusF()
		{
			$count=rNumFile("balda/users/next.txt");
			for($i=0;$i<$count;$i++)
			{
				if(file_exists("balda/users/user".$i."status.txt"))
					isOnline($i);
			}
		}
		function getStatus()
		{
			$status;
			$mysqli = new mysqli("127.0.0.1", "root","", "balda");
			$res = mysqli_query($mysqli, "SELECT time, id FROM users");
			$timeArr = mysqli_fetch_all($res,MYSQLI_NUM);
			return $timeArr;
		}
		function getStatusF()
		{
			$status;
			$cur=0;
			$count=rNumFile("balda/users/next.txt");
			for($i=0;$i<$count;$i++)
			{
				if(file_exists("balda/users/user".$i."status.txt"))
				{
					$status[$cur][0] = rNumFile("balda/users/user".$i."status.txt");
					$status[$cur][1] = $i;
					$cur++;
				}
			}
			return $status;
		}
		function isOnlineF($id)
		{	
			if(file_exists("balda/users/user".$id."status.txt"))
				if(time()- filectime("balda/users/user".$id."status.txt") > 30 &&  rLineFile("balda/users/user".$id."status.txt", 10)<=1)
				{
					writeFile("balda/users/user".$id."status.txt",filectime("balda/users/user".$id."status.txt"),"w");
				}
		}
		/*function updateStatus()
		{
			isOnline();
		}
		function isOnline()
		{	
			$mysqli = new mysqli("127.0.0.1", "root","", "balda");
			$res = mysqli_query($mysqli, "SELECT time, id FROM users");
			$timeArr = mysqli_fetch_all($res,MYSQLI_NUM);
			for($i=0; $i<$res->num_rows;$i++)
			{	$curTime = time();
				if($curTime - $timeArr[$i][0] > 30 &&  $timeArr[$i][0]<=1)
				{
					$id = $timeArr[$i][1];
					mysqli_query($mysqli, "UPDATE users SET time = $curTime WHERE id=$id");
				}
			}
		}*/
?>