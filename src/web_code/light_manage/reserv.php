<?php
	include("../database/connect_db.php");
	date_default_timezone_set('Asia/Taipei');
	$id="";
	$data_font="";
	$sselect_time="";
	session_start();
	function time_trans ($ti)
	{
		if ( $ti[2] >= 60) //sec >60 
		{
			//echo " if 1";
			$ti[1] +=floor($ti[2]/60);
			$ti[2] = $ti[2] % 60;
		}
						
		if ($ti[1] >= 60)//min>60
		{
			//echo " if 2";
			$ti[0] +=floor($ti[1]/60);
			$ti[1] = $ti[1] % 60;
		}
		return $ti;
	}
	
	if (isset($_POST["select_mode"])){
		$select=$_POST["select_mode"];
		$select1=$_POST["select_mode1"];
		//echo $select;
		$GLOBALS['id'] = $select;
		;
		$GLOBALS['data_font'] = $select ."_".$select1;
	}
	
	
	if (isset($_POST["select_mode2"])) 
	{  

	}
	
	
	if (isset($_POST["select_mode4"]))
	{

	}
	//echo "id=".$id."</br>";
	//echo "select=".$data_font."</br>";
	//echo "select=".$sselect_time."</br>";
	//echo "data_font:".$GLOBALS['data_font'];
	
	
	
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">	
	<title>定時管理 </title>	
	
	<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js" ></script> 
	<script src = "jquery-tablepage-1.0.js" ></script>

	<style>
		.chi {font-family:"標楷體";font-size:20px;}
		div, th, button, input, select{font-size:24px;}
	</style>
	
</head>
<body>
	<center>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;width:80%;">
		<h3>管理人員功能</h3>
		
		
		<table border="1"  align="center" width='100%' height = '40'>
		<?
		     if($_SESSION['permit'] == 1)
			{
				echo 
				"<tr  bgcolor='#FCFCFC'  height = '50' >
					<th width='20%'>
						<a href='./Register_member.php'>使用者管理</a>
				       </th>
				       <th width='20%'>
						<a href='./ma.php'>受控設備管理</a>
				       </th>
					<th width='20%'>
							<h>定時管理</h>
				       </th>
				       <th width='20%'>
						<a href='./time.php'>統計管理</a>
				       </th>
				       <th width='20%'>
						<a href='../light_control/alert_lift.php'>警報管理</a> 
				       </th>
				</tr>";
			}
			if($_SESSION['permit'] == 2)
			{
				echo "<tr  bgcolor='#FCFCFC' height = '50'>
				       <th width='25%'>
						<a href='./ma.php'>受控設備管理</a>
				       </th>
						<th width='25%'>
							<a href='./reserv.php'>定時管理</a>
				       </th>
				       <th width='25%'>
						<a href='./time.php'>統計管理</a>
				       </th>
				       <th width='25%'>
						<a href='../light_control/alert_lift.php'>警報管理</a> 
				       </th>
				</tr>";
				
			}
		?>
						
		</table>
		</div>
		<br/>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;width:80%;">
				<tr>						
					<th bgcolor="#97CBFF" >
						<a href = "../light_manage/reserv_add.php">
						<input type="button" value="新增定時開關" style="align;"></button>
						</a>
					</th>
		</div>
		<br/>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;width:80%;">
		
			<form name="form2" id="mode2" method="post" >
			選擇要顯示的受控設備
				<select name="select_mode" id="select_mode">
					<option value="1">全部</option>
					<?php
						$rows = show_table_all_info("light");
						$num = mysqli_num_rows($rows);
			
						for ($i=0;$i<$num;$i++)
						{
							$row = mysqli_fetch_row($rows);
							echo "<option value=\"".$row[0]."\">".$row[0]."</option>";
						}
					?>
				</select>
				
			選擇報表類型
				<select name="select_mode1" id="select_mode1">
					<option value="1">全部資料</option>
					<option value="2">今日行程</option>
					<option value="3">本月行程</option>
					<option value="4">固定行程</option>
				</select>
				<input type="submit" name="Submit" value="Select" />
			</form>
		</div>
		</br>
		
		<!--
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;">
		
		<form name="form3" id="mode3" method="post" >
		-->	
			<?php
							/*
				if ($GLOBALS['data_font'] == '1') //全部資料
				{
					echo"選擇年分";
					echo "<select name=\"select_mode2\" id=\"select_mode2\">";
					
					$sql = "SELECT * FROM Time ORDER BY systime DESC";
					echo $ssql;
					$row = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql));
					$data_time = explode(' ',$row[6]);
					$data = explode('-',$data_time[0]);
					$time = explode(':',$data_time[1]);
					for ($i=((int)$data[0]-5);$i<=((int)$data[0]);$i++)
					{
						echo "<option value=\"".$GLOBALS['id']."_".$GLOBALS['data_font']."_".$i."\">";
						echo $i;
						echo "</option>";
					}
				}
				else if ($GLOBALS['data_font'] == '2')
				{
					echo"選擇年分";
					echo "<select name=\"select_mode4\" id=\"select_mode4\">";
					$sql = "SELECT * FROM Time ORDER BY systime DESC";
					echo $ssql;
					$row = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql));
					$data_time = explode(' ',$row[6]);
					$data = explode('-',$data_time[0]);
					$time = explode(':',$data_time[1]);
					
					for ($i=((int)$data[0]-5);$i<=((int)$data[0]);$i++)
					{
						echo "<option value=\"".$GLOBALS['id']."_".$GLOBALS['data_font']."_".$i."\">";
						echo $i;
						echo "</option>";
					}
					
					echo "</select>";
					echo"選擇月分";
					echo "<select name=\"select_mode5\" id=\"select_mode5\">";
					for ($i=1;$i<=12;$i++)
					{
						echo "<option value=\"".$GLOBALS['id']."_".$GLOBALS['data_font']."_".$i."\">";
						echo $i;
						echo "</option>";
					}
				}
				*/
			?>
		<!--
			</select>
				<input type="submit" name="Submit" value="Select" />
		</form>
		</div>
		-->
		
		
		<?php //show data
			if (!empty($GLOBALS['data_font']))
			{
				$data = explode('_',$GLOBALS['data_font']);
				
				//預先撈資料
				$sql = "";
				if (($data[0] == '1') && ($data[1] != '4'))
				{					
					$sql = "SELECT * FROM reserv_single ORDER BY systime DESC"; 
				}
				else if (($data[0] != '1') && ($data[1] != '4'))
				{
					$light = "SELECT * FROM light WHERE Name='$data[0]' ";
					$ip = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$light));
					$sql = "SELECT * FROM reserv_single WHERE IP='$ip[1]' ORDER BY systime DESC";
				}
				else if (($data[0] == '1') && ($data[1] == '4'))
				{
					$sql = "SELECT * FROM reserv_repeite ORDER BY systime DESC";
				}
				else if (($data[0] != '1') && ($data[1] == '4'))
				{
					$light = "SELECT * FROM light WHERE Name='$data[0]'";
					$ip = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$light));
					$sql = "SELECT * FROM reserv_repeite WHERE IP='$ip[1]' ORDER BY systime DESC";
				}
				
				//echo"sql:".$sql;
				$rows = mysqli_query($GLOBALS['db'],$sql);
				$num = mysqli_num_rows($rows);
				
					
				//show table==========================================
					echo "<div style=\"border-width:5px;border-style:solid;border-color:blue;padding:5px;width:80%;\">";
					
					//echo "data[0]".$data[0];
					if ($data[0] == '1') //all
					{
						switch ($data[1])
						{
							case '1':
								echo "<h3>全部受控設備的定時資料</h3>";
								break;
							case '2':
								echo "<h3>全部受控設備今日行程資料</h3>";
								break;
							case '3':
								echo "<h3>全部受控設備本月行程資料</h3>";
								break;
							case '4':
								echo "<h3>全部受控設備固定行程資料</h3>";
								break;
						}
					}
					
					else if ($data[0] != '1') //single
					{
						switch ($data[1])
						{
							case '1':
								echo "<h3>".$data[0]."受控設備所有定時資料</h3>";
								break;
							case '2':
								echo "<h3>".$data[0]."受控設備今日行程資料</h3>";
								break;
							case '3':
								echo "<h3>".$data[0]."受控設備本月行程資料</h3>";
								break;
							case '4':
								echo "<h3>".$data[0]."受控設備固定行程資料</h3>";
								break;
						}
						
					}
					
					echo "<table id=\"tb1\" border=\"1\" width=\"90%\" align=\"center\" >";
					echo "<thead>";
						echo "<tr>";
						if ( ($data[1] != '4'))
						{
							echo "<th> 受控設備編號 </th>";
							echo "<th> 紀錄編號 </th>";
							echo "<th> IPv6 位址 </th>";
							echo "<th> 執行模式 </th>";
							echo "<th> 預定時間 </th>";
							echo "<th> 動作 </th>";
							echo "<th> 狀態 </th>";
							echo "<th> 紀錄時間 </th>";
							echo "</tr>";
						}
						
						else if ($data[1] == '4')
						{
							echo "<th> 受控設備編號 </th>";
							echo "<th> 紀錄編號 </th>";
							echo "<th> IPv6 位址 </th>";
							echo "<th> 執行模式 </th>";
							echo "<th> 預定時間 </th>";
							echo "<th> 動作 </th>";
							echo "<th> 重複時間 </th>";
							echo "<th> 紀錄時間 </th>";
							echo "</tr>";
						}
						
					echo "</thead>";
					echo"<tbody";
								$rows = mysqli_query($GLOBALS['db'],$sql);
								$num = mysqli_num_rows($rows);
								for ($i = 0;$i<$num;$i++)
								{
									$row = mysqli_fetch_row($rows);
									
									$light = "SELECT * FROM light WHERE IP='$row[1]'";
									$light_data = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$light));
									
 									if ($data[1] != '4') //20 單一行程
									{
										$ymd = explode('-',$row[3]); //ymd
										$now = date('Y-m-d');
										$now_ymd = explode('-',$now);
										
										
										if ($data[1] == '1') //show 全部資料
										{
											
										}
										else if (($data[1] == '2') && ($now_ymd[0] == $ymd[0]) && ($now_ymd[1] == $ymd[1]) && ($now_ymd[2] == $ymd[2]))
										{
											
										}
										else if (($data[1] == '3') && ($now_ymd[0] == $ymd[0]) && ($now_ymd[1] == $ymd[1]))
										{
			
										}		
										else continue;
										
										
										echo "<tr>";
											echo "<td>" . $light_data[0] ."</td>";
											
											echo "<td>" . str_pad(strval($row[0]),4,"0",STR_PAD_LEFT) ."</td>";
											echo "<td>" . $row[1] ."</td>";
											if ($row[2] == 1)
												echo "<td>執行一次</td>";
											else 
												echo "<td>重複執行</td>";
											echo "<td>" . $row[3]." ".$row[4]."</td>";
											switch ($row[5])
											{
													case '1':
														echo "<td>開啟大燈</td>";
														break;
													case '2':
														echo "<td>開啟小燈</td>";
														break;
													case '3':
														echo "<td>開起紅燈</td>";
														break;
													case '4':
														echo "<td>關掉電燈</td>";
														break;
													case '5':
														echo "<td>開啟自動感應</td>";
														break;
													case '6':
														echo "<td>關閉自動感應</td>";
														break;
													case '7':
														echo "<td>開啟燈光秀</td>";
														break;
													case '8':
														echo "<td>關閉燈光秀</td>";
														break;
														
											}
											if ($row[6] == 0)
												echo "<td>尚未執行</td>";
											else 
												echo "<td>執行完畢</td>";
											echo "<td>" . $row[7] ."</td>";
											echo "</tr>";
											
										
									}
									else if ($data[1] == '4')
									{
										echo "<tr>";
											echo "<td>" . $light_data[0] ."</td>";
											
											echo "<td>" . str_pad(strval($row[0]),4,"0",STR_PAD_LEFT) ."</td>";
											echo "<td>" . $row[1] ."</td>";
											if ($row[2] == 1)
												echo "<td>執行一次</td>";
											else 
												echo "<td>重複執行</td>";
											echo "<td>" . $row[2] ."</td>";	
											switch ($row[4])
											{
													case '1':
														echo "<td>開啟大燈</td>";
														break;
													case '2':
														echo "<td>開啟小燈</td>";
														break;
													case '3':
														echo "<td>開起紅燈</td>";
														break;
													case '4':
														echo "<td>關掉電燈</td>";
														break;
													case '5':
														echo "<td>開啟自動感應</td>";
														break;
													case '6':
														echo "<td>關閉自動感應</td>";
														break;
													case '7':
														echo "<td>開啟燈光秀</td>";
														break;
													case '8':
														echo "<td>關閉燈光秀</td>";
														break;
														
											}
											$day = explode('_',$row[3]);
											$day_s = "";
											for ($j = 0; $j<count($day);$j++)
											{
												switch ($day[$j])
												{
													case '1':
														$day_s.="星期一 ";
														break;
													case '2':
														$day_s.="星期二 ";
														break;
													case '3':
														$day_s.="星期三 ";
														break;
													case '4':
														$day_s.="星期四 ";
														break;
													case '5':
														$day_s.="星期五 ";
														break;
													case '6':
														$day_s.="星期六 ";
														break;
													case '0':
														$day_s.="星期日 ";
														break;
													case '-1':
														$day_s.="每天 ";
														break;
												}
											}
											echo "<td>" . $day_s ."</td>";
											echo "<td>" . $row[5] ."</td>";
											echo "</tr>";

									}
								}
					echo "</tbody>";
					echo "</table>";
					echo "</br>";
					echo "<span id='table_page'></span>";
					echo "</div>"; 
					echo "<br/>";
			}		//==========================================
		?>
		<script>
			$("#tb1").tablepage($("#table_page"), 5);
		</script>
		
		</br>
			<table border="1" height="70" width="80%" align="center">
					<tr>
						
						<th bgcolor="#C9FFFF">
							<a href = "../index.php">
							<input type="button" value="登出" style="align;"></button>
							</a>
						</th>

					</tr>
					
					</table>
	</center>
</body>
</html>