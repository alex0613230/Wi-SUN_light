<?php
	session_start();
	include("../database/connect_db.php");
	date_default_timezone_set('Asia/Taipei');
	$id=$_GET['id'];
	
	if (isset($_POST["select_mode"])){
		$select=$_POST["select_mode"];
		//echo $select;
		$GLOBALS['id'] = $select;
	}
	
	if (isset($_POST["select_eme_num"])){  
		$select=$_POST["select_eme_num"];
		$sql = "SELECT * FROM EME WHERE id=$select";
		$row = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql));
		$sql = "SELECT * FROM light WHERE IP='".$row[1]."'";
		$row_light= mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql));
		$GLOBALS['id'] = $row_light[0];		
		
		
		insert_command("0000000A01100000000102000800",$row_light[1]); //解除警報 command
		
		
		$old_sts = $row_light[3];//恢復警報前狀態
		$sql_re_sts = "UPDATE light SET LIS='$row_light[3]',LIS_OLD=NULL WHERE Name='".$row_light[0]."'";
		//echo $sql_re_sts;
		mysqli_query($GLOBALS['db'],$sql_re_sts);
		
	
		$now = date('Y-m-d H:i:s');
		$sql = "UPDATE EME SET flag=1,protime='$now'  WHERE id=$select"; //警報 flag=1 
		mysqli_query($GLOBALS['db'],$sql);
		
		
		sleep(1);
		
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
			default:
				insert_command("0000000A01100000000102000600","pass"); //有 emergency、燈號異常的跳過
		}	
		
	}
	
	
	
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">	
	<title>警報管理 </title>

	<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js" ></script> 
	<script src = "../light_manage/jquery-tablepage-1.0.js" ></script>

	<style>
		.chi {font-family:"標楷體";font-size:20px;}
		div, th, button, input, select{font-size:24px;}
	</style>

</head>
<body>
	<center>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;margin: auto; width:80%;">
		<h3> 管理人員功能</h3>
		
		
		<table border="1" width="100%" align="center">
					
					     <?php 
				 if($_SESSION['permit'] == 1)
			{
				echo 
				"<tr  bgcolor='#FCFCFC'  height = '50' >
					<th width='20%'>
						<a href='../light_manage/Register_member.php'>使用者管理</a>
				       </th>
				       <th width='20%'>
						<a href='../light_manage/ma.php'>受控設備管理</a>
				       </th>
					<th width='20%'>
							<a href='../light_manage/reserv.php'>定時管理</a>
				       </th>
				       <th widt4h='20%'>
						<a href='../light_manage/time.php'>統計管理</a>
				       </th>
				       <th width='20%'>
						<h>警報管理</h> 
				       </th>
				</tr>";
			}
			if($_SESSION['permit'] == 2)
			{
				echo "<tr  bgcolor='#FCFCFC' height = '50'>
					<th width='25%'>
						<a href='../light_manage/ma.php'>受控設備管理</a>
				       </th>
				       <th width='25%'>
						<a href='../light_manage/reserv.php'>定時管理</a>
				       </th>
				       <th width='25%'>
						<a href='../light_manage/time.php'>統計管理</a>
				       </th>
				       <th width='25%'>
						<h>警報管理</h> 
				       </th>
				</tr>";
			}?>
						
		</table>
		</div>
		
		
		<br/>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF; width:80%;">
		
			<form name="form2" id="mode2" method="post" >
			選擇要顯示的警報狀態
				<select name="select_mode" id="select_mode">
					<option value="1">警報中</option>
					<option value="2">全部資料</option>
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
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px; width:80%;">
		<?php
			if (($GLOBALS['id'] == '1')) echo "<h3> 警報中資料 </h1>";
			else if (($GLOBALS['id'] == '2')) echo "<h3> 全部警報資料 </h1>";
			else echo "<h3>".$GLOBALS['id']."受控設備警報資料</h1>";
		?>
		<table id="tb1" border="1" width="80%" align="center" >
		<thead>
			<tr>
				<th> 受控設備編號 </th>
				<th> 事件編號 </th>
				<th> IPv6 位址 </th>
				<th> 警報狀態 </th>
				<th> 發生時間 </th>
				<th> 處理完成時間 </th>
			</tr>  
		</thead>
		<tbody>
		<?php
			
			//echo $GLOBALs['id'];
			
			$rows = alert_show($GLOBALS['id']);
			$num = mysqli_num_rows($rows);
			for ($i=0;$i<$num;$i++)
			{
				
				$row = mysqli_fetch_row($rows);
				$sql_cmd = "SELECT * FROM light WHERE IP='".$row[1]."'";
				$row_light = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql_cmd));
				echo "<tr>";
				echo "<td>" . $row_light[0] ."</td>";
				echo "<td>" . str_pad(strval($row[0]),4,"0",STR_PAD_LEFT) ."</td>";
				echo "<td>" . $row[1] ."</td>";
				if ($row[2] == 0 )	echo "<th bgcolor=\"red\"><font color=\"white\">警報未解除</font></td>";
				else echo "<th bgcolor=\"green\"><font color=\"white\">已解除警報</font></td>";
				echo "<td>" . $row[3] ."</td>";
				echo "<td>" . $row[4] ."</td>";
				echo "</tr>";
				
				
			}
			

			
			
			
		?>
		
		</tbody>
		</table>
		</br>
		<span id='table_page'></span>
		</div>
		
		<script>
			$("#tb1").tablepage($("#table_page"), 10);
		</script>
		
		</br>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF; width:80%;">
		
			<form name="form1" id="mode1" method="post" action="alert_lift.php">
				選擇要解除警報的事件編號
				<select name="select_eme_num" id="select_eme_num">
					<?php
						
						$sql = "";
						if ($GLOBALS['id'] == '1') $sql = "SELECT * FROM EME WHERE flag=0";
						else 
						{
							$sql_cmd = "SELECT * FROM light WHERE Name='".$GLOBALS['id']."'";
							$row = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql_cmd));
							$sql = "SELECT * FROM EME WHERE IP='".$row[1]."' AND flag=0";
							
						}
						//echo $sql;
						$rows = mysqli_query($GLOBALS['db'],$sql);
						$num = mysqli_num_rows($rows);
						for ($i=0;$i<$num;$i++)
						{
							$row = mysqli_fetch_row($rows);
							echo "<option value=\"".$row[0]."\">".str_pad(strval($row[0]),4,"0",STR_PAD_LEFT)."</option>";
						}
					?>
				</select>
				<input type="submit" name="Submit1" value="解除" onclick="return(confirm('確任解除？'))">
			</form>
		</div>
		
		
		</br>
			<table border="1" height="70" width="80%" align="center">
					<tr>
						
						<th bgcolor="#C9FFFF">
							<a href = "../index.php" >
							<input type="button" value="登出" style="align;"></button>
							</a>
						</th>

					</tr>
					
					</table>
	</center
</body>
</html>