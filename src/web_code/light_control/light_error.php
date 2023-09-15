<?php
	include("../database/connect_db.php");
	date_default_timezone_set('Asia/Taipei');
	$id=$_GET['id'];
	echo $id;
	
	if (isset($_POST["select_mode"])){
		$select=$_POST["select_mode"];
		//echo $select;
		$GLOBALS['id'] = $select;
	}
	
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">	
	<title>smart light 異常管理 </title>	

</head>
<body>
	<center>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;">
		<h1> 異常管理</h1>
		</div>
		<br/>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;">
		
			<form name="form2" id="mode2" method="post" >
			選擇要顯示的路燈
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
				<input type="submit" name="Submit" value="Select" />
			</form>
		</div>
		</br>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;">
		<?php
			if ($GLOBALS['id'] == '1') echo "<h3>路燈狀態資料</h3>";
			else echo "<h3>".$GLOBALS['id']."路燈狀態資料</h1>";
		?>
		<table border="1" width="1440" align="center" >
		<thead>
			<tr>
				<th> 路燈編號 </th>
				<th> 事件編號 </th>
				<th> IP位置 </th>
				<th> 3W 白燈 </th>
				<th> 3W 紅燈 </th>
				<th> 1W 白燈 </th>
				<th> 紀錄時間 </th>
			</tr>  
		</thead>
		<tbody>
		<?php
			
			//echo $GLOBALs['id'];
			
			$rows = show_table_all_info("light");
			$num = mysqli_num_rows($rows);
			
			if ($GLOBALS['id'] == '1')
			{

				for ($i=0;$i<$num;$i++)
				{
					$row = mysqli_fetch_row($rows);
					$sql_cmd = "SELECT * FROM Time WHERE IP='".$row[1]."' AND LES>=0 ORDER BY systime DESC";
					//echo $sql_cmd;
					//echo "<br/>"; 
					$data_num = 0;
					$data_num = mysqli_num_rows(mysqli_query($GLOBALS['db'],$sql_cmd));
					$data = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql_cmd));
					if ($data_num <=0) continue;
					
					echo "<tr>";
					echo "<td>" . $row[0] ."</td>";
					echo "<td>" . str_pad(strval($data[0]),4,"0",STR_PAD_LEFT) ."</td>";
					echo "<td>" . $data[1] ."</td>";
					
					if ($data[2] == 0)
					{
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						//continue;
					}
					else if ($data[2] == 1)
					{
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						
					}
					else if ($data[2] == 2)
					{
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
					}
					else if ($data[2] == 3)
					{
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
					}
					else if ($data[2] == 4)
					{
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
					}
					else if ($data[2] == 5)
					{
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
					}
					else if ($data[2] == 6)
					{
						echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
					}
					else if ($data[2] == 4)
					{
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
					}
					else
					{
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
					}
					echo "<td>" . $data[6] ."</td>";
					echo "</tr>";
										
					
				}
			}
			else
			{
				
				$sql = "SELECT * FROM light WHERE Name='".$GLOBALS['id']."'";
				//echo $sql;
				$light = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql));
				
				$sql_cmd = "SELECT * FROM Time WHERE IP='".$light[1]."' AND LES>=0 ORDER BY systime DESC";
				//echo $sql_cmd;
				$data_num = 0;
				$data_num = mysqli_num_rows(mysqli_query($GLOBALS['db'],$sql_cmd));
				$data = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql_cmd));
				
					if ($data_num >0 )
					{
					
						echo "<tr>";
						echo "<td>" . $light[0] ."</td>";
						echo "<td>" . str_pad(strval($data[0]),4,"0",STR_PAD_LEFT) ."</td>";
						echo "<td>" . $data[1] ."</td>";
						
						if ($data[2] == 0)
						{
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
							//continue;
						}
						else if ($data[2] == 1)
						{
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
							
						}
						else if ($data[2] == 2)
						{
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						}
						else if ($data[2] == 3)
						{
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						}
						else if ($data[2] == 4)
						{
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						}
						else if ($data[2] == 5)
						{
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
						}
						else if ($data[2] == 6)
						{
							echo "<th bgcolor=\"green\"><font color=\"white\">正常</font></td>";
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						}
						else if ($data[2] == 4)
						{
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						}
						else
						{
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
							echo "<th bgcolor=\"red\"><font color=\"white\">異常</font></td>";
						}
						echo "<td>" . $data[6] ."</td>";
						echo "</tr>";
					}
				
			}
			

			
			
			
		?>
		</tbody>
		</table>
		</div>
		
		
		</br>
			<table border="1" height="70" width="1440" align="center">
				<tr>						
					<th bgcolor="#97CBFF" width="500">
						<a href = "../light_control/map.php">
						<input type="button" value="路燈燈控制頁面" style="align;"></button>
						</a>
					</th>
					
					<th bgcolor="#97CBFF" width="500">
						<a href = "../index.php">
						<input type="button" value="返回主頁" style="align;"></button>
						</a>
					</th>
				</tr>
		</table>
	</center
</body>
</html>