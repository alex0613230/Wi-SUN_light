<?php
		
	$db = mysqli_connect("localhost","light","light");
    mysqli_select_db($db,"light");
	date_default_timezone_set('Asia/Taipei');
	
	function show_table_all_info ($name)
	{
			$sql_cmd = "SELECT * FROM $name";
			$rows = mysqli_query ( $GLOBALS['db'],$sql_cmd);
			return $rows;
	}
	
	function emergency_show()
	{
			$sql_cmd = "SELECT * FROM EME WHERE flag != 1";
			$rows = mysqli_query ( $GLOBALS['db'],$sql_cmd);
			$num = mysqli_num_rows ($rows);
			
			for ($i = 0;$i < $num ;$i++)
			{
				$row = mysqli_fetch_row($rows);
				
				$sql = "SELECT * FROM light WHERE IP='".strtoupper($row[1])."'"; //取得警報前狀態並抱存到LIS_OLD
				$row_light = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql));
				
				
				if ($row_light[3] == NULL)
				{
					$old_sts = (int)$row_light[2];
					$sql_up_light_lis_old = "UPDATE light SET LIS_OLD=$old_sts WHERE IP='".strtolower($row[1])."'";
					mysqli_query($GLOBALS['db'],$sql_up_light_lis_old);
				}
				
				$sql_up_eme = "UPDATE light SET LIS='7' WHERE IP='".$row[1]."'";
				mysqli_query ( $GLOBALS['db'],$sql_up_eme);
				
			}
	}
	
	function alert_show($show)//警報管理 table
	{
		$sql="";
		if ($show == "1") $sql = "SELECT * FROM EME WHERE flag!=1 ORDER BY systime DESC";
		else if ($GLOBALS['id'] == '2') $sql = "SELECT * FROM EME ORDER BY systime DESC";
		else
		{
			$sql_serch_ip = "SELECT * FROM light WHERE Name='".$show."'";
			//echo $sql_serch_ip;
			$rows = mysqli_query($GLOBALS['db'],$sql_serch_ip);
			$row = mysqli_fetch_row($rows);
			//echo $num;
			$sql = "SELECT * FROM EME WHERE IP='".$row[1]."' ORDER BY systime DESC";
			//echo $sql;
			
		}
		
		return mysqli_query($GLOBALS['db'],$sql);
		
		
	}
	
	function light_error_show()
	{
			$rows = show_table_all_info("light");
			$num = mysqli_num_rows($rows);
			for ($i=0;$i<$num;$i++)
			{
					$row = mysqli_fetch_row($rows);
					$sql_cmd = "SELECT * FROM Time WHERE IP='".$row[1]."' AND LES>=0 ORDER BY systime DESC";
					$data_num = 0;
					$data_num = mysqli_num_rows(mysqli_query($GLOBALS['db'],$sql_cmd));					
					if ($data_num > 0)
					{
						$data = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql_cmd));
						$sql_update="";
						if ($data[2] > 0) $sql_update = "UPDATE light SET LIS=6 WHERE IP='".$data[1]."'";
						//else if ($data[2] == 0) $sql_update = "UPDATE light SET LIS=0 WHERE IP='".$data[1]."'";
						mysqli_query($GLOBALS['db'],$sql_update);
					}

			}
	}
	
	function insert_command ($cmmand,$name)
	{
		if ($name == "all") //全域超做
		{
			$rows = show_table_all_info("light");
			$num = 0;
			$num = mysqli_num_rows($rows);
			for ($i=0;$i<5;$i++)
			{
				$row = mysqli_fetch_row($rows);
				if ((int)$row[2] == 7) continue; //排除警報、路燈異常的路燈
				else if ((int)$row[2] == 10) continue;
				else
				{
					
					$TS=0;
					/*
					while(true)
					{
						$TS=str_pad(rand(1,9999),4,"0",STR_PAD_LEFT);
						$sql_check_TS = "SELECT * FROM Command WHERE TS='".$TS."'";
						$count=0;
						$count=mysqli_num_rows(mysqli_query($db,$sql_check_TS));
						if ($count==0)
							break;
					}
					*/
					
					
					//INSERT INTO Command (TS,IP,OT,REC) VALUES('$TS','$row[1]','$ot_cmd',NULL)
					$now = date('Y-m-d H:i:s');
					$sql_insert = "INSERT INTO Command (TS,IP,OT,flag,systime) VALUES('$TS','$row[1]','$cmmand',0,'$now')";
					//echo $cmd;
					//echo $sql_insert;
					//echo "</br>";
					mysqli_query ( $GLOBALS['db'],$sql_insert);
				}
			}
		}
		else
		{
			if ($name != "pass")
			{
					$TS=0;
					/*
					while(true)
					{
						$TS=str_pad(rand(1,9999),4,"0",STR_PAD_LEFT);
						$sql_check_TS = "SELECT * FROM Command WHERE TS='".$TS."'";
						$count=0;
						$count=mysqli_num_rows(mysqli_query($db,$sql_check_TS));
						echo "count:".$count."</br>";
						if ($count==0)
							break;
					}
					*/
					$now = date('Y-m-d H:i:s');
					$sql_insert = "INSERT INTO Command (TS,IP,OT,flag,systime) VALUES('$TS','$name','$cmmand',0,'$now')";
					mysqli_query ( $GLOBALS['db'],$sql_insert);
			}
		}
	}
	
	function toupper()
	{
		$sql = "SELECT * FROM light";
		$rows=mysqli_query($GLOBALS['db'],$sql);
		$num  = mysqli_num_rows($rows);
		for ($i=0;$i<$num;$i++)
		{
			$row=mysqli_fetch_row($rows);
			$ip = strtoupper($row[1]);
			$sqlup = "UPDATE light SET IP='$ip' WHERE Name='$row[0]'";
			mysqli_query ( $GLOBALS['db'],$sqlup);
			
			
		}
	}
	
	
?>