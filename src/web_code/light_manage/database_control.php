<?php
	include("../database/connect_db.php");
	date_default_timezone_set('Asia/Taipei');
	
	if (isset($_POST["select_mode"])){
		$select=$_POST["select_mode"];
		$sql = "";
		if ($select == '1')
			$sql = "TRUNCATE TABLE Command";
		else if ($select == '2')
			$sql = "TRUNCATE TABLE time";
		
		mysqli_query($GLOBALS['db'],$sql);
		$msg = "已清空 ".($select=='1'?"command":"time")."資料表";
		echo "<script>";
		echo "alert(\"".$msg."\")";
		echo "</script>";
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">	
	<title>smart light 資料庫管理 </title>	
	</head>
<body>
	<center>
			<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;">
			<h1> 資料庫管理</h1>
			
			</div>
			</br>
					<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;">
		
			<form name="form1" id="mode2" method="post" >
			選擇要清空的表格
				<select name="select_mode" id="select_mode">
					<option value="1">command</option>
					<option value="2">time</option>
				</select>
				<input type="submit" name="Submit" value="Select" onclick="return(confirm('確任清空該資料表？'))"/>
			</form>
		</div>
		
				</br>
			<table border="1" height="70" width="1440" align="center">
				<tr>						
					<th bgcolor="#97CBFF" width="500">
						<a href = "../light_manage/ma.php">
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
	</center>
</body>
</html>