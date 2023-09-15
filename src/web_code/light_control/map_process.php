<?php

	include("../database/connect_db.php");
	date_default_timezone_set('Asia/Taipei');
	$id=$_GET['id'];
	$ot_cmd="";
	
	//sql=====================================
	//$GLOBALS['db'] = mysqli_connect("localhost","light","light");
	//mysqli_select_db($GLOBALS['db'],"light");
	//========================================
	

	
	if ($id == "all_ww1")
	{
		//$sql_update = "UPDATE light SET LIS='4' WHERE LIS!='8' AND LIS!='9'";
		//mysqli_query($GLOBALS['db'],$sql_update);
		insert_command("0000000A01100000000102000400","all");
		sleep(1);
		insert_command("00000006010300010005","all");
	}
	else if ($id == "all_ww3")
	{
		//$sql_update = "UPDATE light SET LIS='3' WHERE LIS!='8' AND LIS!='9'";
		//mysqli_query($GLOBALS['db'],$sql_update);
		insert_command("0000000A01100000000102000300","all");
		sleep(1);
		insert_command("00000006010300010005","all");
		
	}
	else if ($id == "all_close_control")
	{
		//$sql_update = "UPDATE light SET LIS='0' WHERE LIS!='8' AND LIS!='9'";
		//mysqli_query($GLOBALS['db'],$sql_update);
		insert_command("0000000A01100000000102000600","all");
		sleep(1);
		insert_command("00000006010300010005","all");

	}
	else if ($id == "all_reset")
	{
		/*
		insert_command("0000000A01100000000102000A00","all");
		sleep(1);
		insert_command("00000006010300010005","all");
		*/
		
		$sql = "select IP from light";
		//echo $sql;
		$result = mysqli_query($GLOBALS['db'],$sql) or die("Error in query: $sql.". mysqli_error());
		
		$i=0;
		while(list($GET_IP[$i]) = mysqli_fetch_row($result))
		$i ++;


		
		for($j =0; $j < $i;$j++)
		{
			$now = date('Y-m-d H:i:s');
			$r = rand() % 65535;
			$r = sprintf("%04X", $r);
			//echo "$GET_IP[j]".$GET_IP[$j]."<br>";
			$sql = "INSERT INTO Command VALUES(0,'$GET_IP[$j]','0000000A01100000000102000A00',0,'$now')";
			$result = mysqli_query($GLOBALS['db'],$sql) or die("Error in query: $sql ". mysqli_error());
		}
		
	}
	else if ($id == "all_auto_on")
	{
		//$sql_update = "UPDATE light SET LIS='1' WHERE LIS!='8' AND LIS!='9'";
		/*
		$sql_update = "UPDATE light SET LIS='1' WHERE LIS!='8' AND Name='L01'";
		mysqli_query($GLOBALS['db'],$sql_update);
		sleep(1);
		$sql_update = "UPDATE light SET LIS='1' WHERE LIS!='8' AND Name='L02'";
		mysqli_query($GLOBALS['db'],$sql_update);
		sleep(1.5);
		$sql_update = "UPDATE light SET LIS='1' WHERE LIS!='8' AND Name='L03'";
		mysqli_query($GLOBALS['db'],$sql_update);
		sleep(1);
		$sql_update = "UPDATE light SET LIS='1' WHERE LIS!='8' AND Name='L04'";
		mysqli_query($GLOBALS['db'],$sql_update);
		sleep(2);
		$sql_update = "UPDATE light SET LIS='1' WHERE LIS!='8' AND Name='L05'";
		mysqli_query($GLOBALS['db'],$sql_update);
		sleep(1.6);
		//sleep(0.5);
		*/
		insert_command("0000000A01100000000102000100","all");
		sleep(1);
		insert_command("00000006010300010005","all");
	}
	else if ($id == "all_auto_off")
	{
		//$sql_update = "UPDATE light SET LIS='0' WHERE LIS!='8' AND LIS!='9'";
		//mysqli_query($GLOBALS['db'],$sql_update);
		insert_command("0000000A01100000000102000200","all");
		sleep(1);
		insert_command("00000006010300010005","all");

	}
	else if ($id == "all_alert")
	{
		//$sql_update = "UPDATE light SET LIS='5' WHERE LIS!='8' AND LIS!='9'";
		//mysqli_query($GLOBALS['db'],$sql_update);
		insert_command("0000000A01100000000102000500","all");
		sleep(1);
		insert_command("00000006010300010005","all");

	}
	else if ($id == "all_light_show_on")
	{
		//$sql_update = "UPDATE light SET LIS='5' WHERE LIS!='8' AND LIS!='9'";
		//mysqli_query($GLOBALS['db'],$sql_update);
		insert_command("0000000A01100000000102000B00","all");
		sleep(1);
		insert_command("00000006010300010005","all");

	}
	else if ($id == "all_light_show_off")
	{
		//$sql_update = "UPDATE light SET LIS='5' WHERE LIS!='8' AND LIS!='9'";
		//mysqli_query($GLOBALS['db'],$sql_update);
		insert_command("0000000A01100000000102000C00","all");
		sleep(1);
		insert_command("00000006010300010005","all");

	}
	else if ($id == "all_alert_lift")
	{
		$sql = "SELECT * FROM EME WHERE flag=0";
		$rows = mysqli_query($GLOBALS['db'],$sql);
		$num = mysqli_num_rows($rows);
		for ($i = 0;$i<$num;$i++)
		{
			$row = mysqli_fetch_row($rows);
			$now = date('Y-m-d H:i:s');
			$sql_update = "UPDATE EME SET flag=1,protime='$now'  WHERE IP='$row[1]'"; //警報 flag=1 
			
			$sql = "SELECT * FROM light WHERE IP='".$row[1]."'";
			$row_light= mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql));
			
			//讀取警報前狀態
			$old_sts = $row_light[3];
			//$sql_re_sts = "UPDATE light SET LIS='$row_light[3]',LIS_OLD=NULL WHERE Name='".$row_light[0]."'";
			$sql_re_sts = "UPDATE light SET LIS_OLD=NULL WHERE Name='".$row_light[0]."'";
			
			
			//解除警報
			mysqli_query($GLOBALS['db'],$sql_update); //截除警報 flag=1
			insert_command("0000000A01100000000102000800",$row[1]);
			
			mysqli_query($GLOBALS['db'],$sql_re_sts); //LIS_OLD = NULL
			
			sleep(1);
			
			
			//恢復警報前狀態
			switch ((int)$row_light[3]) //回復燈號 command
			{
				case 0: // ok = close
					insert_command("0000000A01100000000102000600",$row_light[1]);
					break;
				case 3: // ww3
					insert_command("0000000A01100000000102000300",$row_light[1]);
					break;
				case 4: //ww1
					insert_command("0000000A01100000000102000400",$row_light[1]);
					break;						
				case 5: //wr3
					insert_command("0000000A01100000000102000500",$row_light[1]);
					break;
				case 1: //auto
					insert_command("0000000A01100000000102000200",$row_light[1]);
					break;
				case 11: //auto
					insert_command("0000000A01100000000102000B00",$row_light[1]);
					break;
				default:
					insert_command("0000000A01100000000102000600","pass"); //有 emergency、燈號異常的跳過
			}	
			sleep(1);
			insert_command("00000006010300010005",$row_light[1]);
			

			
		}
	}
	else
	{
		$sql_cmd = "SELECT * FROM light WHERE Name='".$id."'";
		$rows = mysqli_query($GLOBALS['db'],$sql_cmd);
		$row = mysqli_fetch_row($rows);
		
		$status = array(0,3,4,5,1,8);
		$satus_index = -1;
		$LIS =(int)$row[2];
		$old_lis=0;
		if ( ($LIS == 2) || ($LIS == 6) || ($LIS == 9)) $LIS = 0;
		
		for ($i = 0;$i<6;$i++)
		{
			if ($LIS == $status[$i])
			{
				$satus_index = $i;
				$old_lis=$i;
				$satus_index ++;
				if ($satus_index > 6) $satus_index = 0;
				break;
			}
		}
		
		//echo $satus_index;
		//$sql_cmd = "UPDATE light SET LIS='$status[$satus_index]' WHERE Name='".$id."'";
		
		//echo$sql_cmd;
		//mysqli_query($GLOBALS['db'],$sql_cmd);
		
		//echo $row[1];
		/*
		switch ((int)$status[$satus_index]) //Command
		{
			case 0: // ok = close
				insert_command("0000000A01100000000102000600",$row[1]);
				break;
			case 3: // ww3
				insert_command("0000000A01100000000102000300",$row[1]);
				break;
			case 4: //ww1
				insert_command("0000000A01100000000102000400",$row[1]);
				break;						
			case 5: //wr3
				insert_command("0000000A01100000000102000500",$row[1]);
				break;
			case 1: //auto
				insert_command("0000000A01100000000102000100",$row[1]);
				break;
			default:
				insert_command("0000000A01100000000102000600","pass"); //有 emergency、燈號異常的跳過
		}	
		*/
		
		
		if (($status[$satus_index]==0) && ($old_lis=5))
		{
			insert_command("0000000A01100000000102000C00",$row[1]);
			sleep(1);
			insert_command("00000006010300010005",$row[1]);
		}
		else
		{
			switch ((int)$status[$satus_index]) //Command
			{
				case 0: // ok = close
					insert_command("0000000A01100000000102000600",$row[1]);
					break;
				case 3: // ww3
					insert_command("0000000A01100000000102000300",$row[1]);
					break;
				case 4: //ww1
					insert_command("0000000A01100000000102000400",$row[1]);
					break;						
				case 5: //wr3
					insert_command("0000000A01100000000102000500",$row[1]);
					break;
				case 1: //auto
					insert_command("0000000A01100000000102000100",$row[1]);
					break;
				case 8: //auto
					insert_command("0000000A01100000000102000B00",$row[1]);
					break;
				default:
					insert_command("0000000A01100000000102000600","pass"); //有 emergency、燈號異常的跳過
			}
			//sleep(1);
			insert_command("00000006010300010005",$row[1]);
		}
		
		
		/*
		$sql_all_data = "SELECT * FROM light";
		$all_data = mysqli_query($db,$sql_all_data); //取得全路燈資料
		$all_num = mysqli_num_rows($all_data); //計數
		
		$opcode = $row[2];
		$opcode += 1;
		if ($opcode >= 5 ) $opcode =5;
		else if ($opcode >= 3)
		{
			$opcode %=3;
		}
		$sql_update = "UPDATE light SET LIS=$opcode WHERE Name='".$id."'";
		
		
		$row = mysqli_fetch_row($all_data);
		$TS="";
		$ot_cmd="";
		while(true)
			{
			$TS=str_pad(rand(1,9999),4,"0",STR_PAD_LEFT);
			$sql_check_TS = "SELECT * FROM Command WHERE TS='".$TS."'";
			$count=mysqli_num_rows(mysqli_query($db,$sql_check_TS));
			echo "count:".$count."</br>";
			if ($count==0)
				break;
		}
		switch ($opcode)
		{
		
			case 0:
				$ot_cmd="0000000102000200";
				break;
			case 1:
				$ot_cmd="0000000102000300";
				break;
			case 2:
				$ot_cmd="0000000102000100";
				break;
		}
		$sql_insert_command = "INSERT INTO Command (TS,IP,OT,REC) VALUES('$TS','$row[1]','$ot_cmd',NULL)";
		echo ($sql_insert_command);
		*/
	}
	
	header('Location:map.php');
?>