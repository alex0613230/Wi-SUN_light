<?php

	include("../database/connect_db.php");
	date_default_timezone_set('Asia/Taipei');
	
	function repeite_check()
	{
		$sql = "SELECT * FROM reserv_repeite";
		$rows = mysqli_query($GLOBALS['db'],$sql);
		$num = mysqli_num_rows($rows);
		
		for ($i = 0;$i<$num;$i++)
		{
			$row = mysqli_fetch_row($rows);
			$re = explode('_',$row[3]);
			$now = date('Y-m-d');
			if ((count($re)==1) && ($re[0]=='-1'))
			{
					$sql_ch = "SELECT * FROM reserv_single WHERE IP='$row[1]' AND mode=2 AND op=$row[4] AND hms='$row[2]'";
					$num_ch = 0;
					$num_ch = mysqli_num_rows(mysqli_query($GLOBALS['db'],$sql_ch));
					//echo $num_ch."</br>";
					if ($num_ch == 0)
					{
						$ti  = date('Y-m-d H:i:s');
						$sql_insert = "INSERT INTO reserv_single (id,IP,mode,ymd,hms,op,flag,systime) VALUES(NULL,'$row[1]',2,'$now','$row[2]',$row[4],0,'$ti')";
						mysqli_query($GLOBALS['db'],$sql_insert);
						//echo $sql_ch."</br>";
						//echo "insert mode 2 to repeite1"."</br>";
					}
			}
			else if ((count($re) >= 1))
			{
				
				$week = date("w",strtotime($now)); //今天星期幾
				
				$re_week_ch = FALSE;
				for ($j = 0;$j<count($re);$j++)
				{
					if ((int)$re[$j] == (int)$week)
					{
						$re_week_ch = TRUE;
						break;
					}
				}
				if ($re_week_ch)
				{
					$sql_ch = "SELECT * FROM reserv_single WHERE IP='$row[1]' AND mode=2 AND op=$row[4] AND hms='$row[2]'";
					$num_ch = 0;
					$num_ch = mysqli_num_rows(mysqli_query($GLOBALS['db'],$sql_ch));
					//echo $num_ch."</br>";
					if ($num_ch == 0)
					{
						$ti  = date('Y-m-d H:i:s');
						$sql_insert = "INSERT INTO reserv_single (id,IP,mode,ymd,hms,op,flag,systime) VALUES(NULL,'$row[1]',2,'$now','$row[2]',$row[4],0,'$ti')";
						mysqli_query($GLOBALS['db'],$sql_insert);
						//echo $sql_ch."</br>";
						//echo "insert mode 2 to repeite1"."</br>";
					}
				}
				
				
				
			}
			
			
		}
	}
	
	function reserv_single_ch()
	{
		$sql = "SELECT * FROM reserv_single WHERE flag=0";
		$rows = mysqli_query($GLOBALS['db'],$sql);
		$num = mysqli_num_rows($rows);
		//echo $num;
		for ($i = 0;$i<$num;$i++)
		{
			
			$row = mysqli_fetch_row($rows);
			$now  = date('Y-m-d H:i:s');
			
			$da = explode(' ',$now);
			$ymd = explode('-',$da[0]);
			$hms = explode (':',$da[1]);
			$row_hms = explode (':',$row[4]);
			//echo "row_ymd".$row[3];
			//echo $now;
			//echo "</br>";
			if (($row[3] == $da[0]) && ($row_hms[0] == $hms[0]) && ($row_hms[1] == $hms[1]))
			{
				$sql_update = "UPDATE reserv_single SET flag=1 WHERE id='$row[0]'";
				echo $sql;
				echo"</br>";
				
				$cmd="";
				switch ($row[5])
				{
					case 1:
						$cmd = "0000000A01100000000102000300";
						break;
					case 2:
						$cmd = "0000000A01100000000102000400";
						break;
					case 3:
						$cmd = "0000000A01100000000102000500";
						break;
					case 4:
						$cmd = "0000000A01100000000102000600";
						break;
					case 5:
						$cmd = "0000000A01100000000102000100";
						break;
					case 6:
						$cmd = "0000000A01100000000102000200";
						break;
					case 7:
						$cmd = "0000000A01100000000102000B00";
						break;
					case 8:
						$cmd = "0000000A01100000000102000C00";
						break;
				}
				
				mysqli_query($GLOBALS['db'],$sql_update);
				insert_command($cmd,$row[1]);
				insert_command("00000006010300010005",$row[1]);

			}
		}
		
	}
	
	while (true)
	{
		repeite_check();
		reserv_single_ch();
		sleep(5);
	}
	
?>