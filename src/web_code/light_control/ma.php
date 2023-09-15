<?php
	//include("../dbc_admin.php"); 
	
	/*
	if (!isset($permit))
	{
		echo "你沒有權限執行本功能！！！<p>請按正常程序登入本系統。";
		echo "</body></html>";
		exit;
	}
	*/
	session_start();

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta Name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>受控設備資料查詢</title>
		<style>
			a:hover {background-color:blue;color:white;}
			a {text-decoration:none;}
			.chi {font-family:"標楷體";font-size:24px;}
			th, button, input {font-size:24px;}
		</style>
	</head>
	
<?	
	include("../database/connect_db.php");
?>
	<body>
	
				
				<form action="" method=post>
					<table border="1" width="80%" align="center">
						<tr height = "40">
							<th colspan="5"  bgcolor="#97CBFF">
								<p style="font-size:28px">受控設備資料查詢</p>
							</th>
						</tr>
				<?php 
				 if($_SESSION['permit'] == 1)
			{
				echo 
				"<tr  bgcolor='#FCFCFC'  height = '50' >
					<th width='20%'>
						<a href='./Register_member.php'>使用者管理</a>
				       </th>
				       <th width='20%'>
						<h>受控設備管理</h>
				       </th>
					<th width='20%'>
							<a href='./reserv.php'>定時管理</a>
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
						<h>受控設備管理</h>
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
				
			}?>
					</table>
					
					
					<?php
						if(isset($_POST['add']))
						{ 
							$addName = $_POST['Name'];
							$addIP = $_POST['IP'];
							$addx = $_POST['x'];
							$addy = $_POST['y'];
							$sql = "select count(*) from light where Name = '$addName'";
							$result = mysqli_query($GLOBALS['db'],$sql) or die("Error in query: $query.". mysqli_error());
							$n = mysqli_fetch_row($result);
							$i = 0;
							if($n[0] != 0)
							{
								$i++;
								echo "<script>alert('名字重複')</script>";
								header("refresh: 0;");
							}
							mysqli_free_result($result);
							
							$sql = "select count(*) from light where IP = '$addIP'";
							$result = mysqli_query($GLOBALS['db'],$sql) or die("Error in query: $query. ". mysqli_error());
							$n = mysqli_fetch_row($result);
							if($n[0] != 0)
							{
								$i++;
								echo "<script>alert('IP 重複')</script>";
								header("refresh: 0;");
							}
							mysqli_free_result($result);
							
							if($i == 0 && $addName != NULL && $addIP != NULL)
							{
								$query = "INSERT INTO light VALUE('$addName','$addIP',0,0,$addx,$addy,NOW())"; 
								$result = mysqli_query($GLOBALS['db'],$query) or die("Error in query: $query. ". mysqli_error());
								mysqli_free_result($result);
								header("refresh: 0;");
							}
						} 
						
						if(isset($_POST['onedel']))
						{ 
							$del = $_POST['del'];
							$query = "select count(*) from light where Name = '$del'"; 
							$result = mysqli_query($GLOBALS['db'],$query) or die("Error in query: $query.". mysqli_error());
							$n = mysqli_fetch_row($result);
							if($n[0] != 0)
							{
								$query = "select IP from light where Name='$del'"; 
								$result = mysqli_query($GLOBALS['db'],$query) or die("Error in query: $query. ". mysqli_error()); 
								$del_IP = mysqli_fetch_row($result);
								mysqli_free_result($result);
								
								$query = "delete from light where Name='$del'"; 
								$result = mysqli_query($GLOBALS['db'],$query) or die("Error in query: $query. ". mysqli_error()); 
								mysqli_free_result($result);
								
							}
							mysqli_free_result($result);
							
							
						} 
					?>
					<table border="2" width="80%" align="center"><br>
						<tr bgcolor="#97CBFF">
							<th COLSPAN=6 ALIGN=CENTER>
								<font size=5>受控設備資料</font>
							</th>
						</tr>

						<tr  bgcolor="#FCFCFC">
						<th width="100">名稱</th><th width="200">Wi-SUN IPv6 位址</th><th width="240">狀態</th><th width="240"> LEDs 狀態</th>
					<?php
						$sql = "SELECT Name,IP,LIS FROM  light ORDER BY Name";
						$result = mysqli_query($GLOBALS['db'], $sql);

						$i=0;
						while(list($GET_Name[$i], $GET_IP[$i], $GET_LIS[$i]) = mysqli_fetch_row($result))
						$i ++;
						
						mysqli_free_result($result);
						for($j=0;$j<$i;$j++)
						{
							$sql = "SELECT LES FROM Time where IP='$GET_IP[$j]' ORDER BY !systime limit 1"; //IP條件 !systime 最後一筆開始排序 limit 1 第一筆
							$result = mysqli_query($GLOBALS['db'], $sql);
							while(list($GET_LES[$j]) = mysqli_fetch_row($result))
							mysqli_free_result($result);
						}

						for($j = 0; $j < $i; $j++)
						{
							echo "<tr   bgcolor=\"#FCFCFC\"><th >" .$GET_Name[$j] . "</th>";
							echo "<th >" .$GET_IP[$j] . "</th><th>";
							
							switch($GET_LIS[$j])
							{
								case 0:
									echo "閒置";
									break;
								case 1:
									echo "自動感應開啟";
									break;
								case 2:
									echo "自動感應關閉";
									break;
								case 3:
									echo "大白燈開啟";
									break;
								case 4:
									echo "小白燈開啟";
									break;
								case 5:
									echo "紅燈開啟";
									break;
								case 6:
									echo "關燈";
									break;
								case 7:
									echo "緊急狀態";
									break;
								case 8:
									echo "燈光秀開啟";
									break;
								case 9:
									echo "燈光秀關閉";
									break;
								case 10:
									echo "未連線";
									break;
							}

							echo"</th>";
							echo "<th >";
							$sql_les = "SELECT * FROM Time WHERE IP='$GET_IP[$j]' ORDER BY systime DESC";
							$row = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$sql_les));
							if($row[2] == NULL)
							{
								echo "未連線";
							}
							else
							{
								switch($row[2])
								{
									case 0:
										echo "正常";
										break;
									case 1:
										echo "白大燈異常";
										break;
									case 2:
										echo "白小燈異常";
										break;
									case 3:
										echo "白大、小燈異常";
										break;
									case 4:
										echo "紅燈異常";
										break;
									case 5:
										echo "白大燈、紅大燈異常";
										break;
									case 6:
										echo "紅大燈、白小燈異常";
										break;
									case 7:
										echo "全部異常";
										break;
									case 8:
										echo "未連線";
										break;
								}
							}
							
							echo "</th></tr>";
						}
						mysqli_close($GLOBALS['db']);
						header("refresh:30");
					?>
					</table>
					<br>
					
					<?php if($_SESSION['permit'] == 1)  : ?> 	
					
					<table border="1" height="70" width="80%" align="center">
					<tr>
						<th bgcolor="#C9FFFF">
							<a href = "./index_control.php" >
							<input type="button" value="返回操作人員功能" style="align;"></button>
							</a>
						</th>
					</tr>
					</table>
					<?php else : ?>
					<table border="1" height="70" width="80%" align="center">
					<tr>
						
						<th bgcolor="#C9FFFF">
							<a href = "./index_control.php" >
							<input type="button" value="返回操作人員功能" style="align;"></button>
							</a>
						</th>

					</tr>
					
					</table>
					<?php endif; ?>

				</form>
	</body>
</html>