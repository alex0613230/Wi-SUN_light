<?php
	include("../database/connect_db.php");
	date_default_timezone_set('Asia/Taipei');
	$id="";
	$data_font="";
	$select_time="";
	$day;
	
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
		
		$GLOBALS['data_font'] = $select1;
	}
	
	
	if (isset($_POST["select_mode3"]))
	{
		$select=$_POST["select_mode3"];
		$select1=$_POST["select_mode4"];
		$select2=$_POST["select_mode5"];
		$day = $_POST["day"];
		$data = $_POST["data"]; 
		
		
		//echo "select:".$select."</br>";
		//echo "day:".$select1."</br>";
		//print_r($day);
		//echo "</br>";
		//echo "data:".$data."</br>";
		
		$s1 = explode('_',$select);
		$s2 = explode('_',$select1);
		$hms = str_pad(strval($s1[2]),2,"0",STR_PAD_LEFT) .":" .str_pad(strval($s2[2]),2,"0",STR_PAD_LEFT) . ":00";
		//$select .= "_".$da[2];
		//print_r($s1);
		//print_r($s2);
		//insert to table
		$sql = "";
		
		
		if ($s1[0] == '1')
		{
			$sql = "SELECT * FROM light";
			//$GLOBALS['select_time'] = $data."_".$select;
		}
		else if ($s1[0] != '1')	
		{
			$sql = "SELECT * FROM light WHERE Name='$s1[0]'";
			//$GLOBALS['select_time'] = $select;
		}
		
		//echo $sql;
		$rows = mysqli_query($GLOBALS['db'],$sql);
		$num = mysqli_num_rows($rows);
		
		for ($i = 0;$i<$num;$i++)
		{
			$row = mysqli_fetch_row($rows);
			$now = date('Y-m-d H:i:s');
			if ($s1[1] == '1') //single
			{
				if ((int)$row[2] == 10) continue;
				$sql_insert = "INSERT INTO reserv_single (id,IP,mode,ymd,hms,op,flag,systime) VALUES(NULL,'$row[1]',1,'$data','$hms',$select2,0,'$now')";
				mysqli_query($GLOBALS['db'],$sql_insert);
				//echo $sql_insert."</br>";
			}
			else if ($s1[1] == '2') //repeite
			{
				if ((int)$row[2] == 10) continue;
				$str = implode("_",$day);
				$sql_insert = "INSERT INTO reserv_repeite (id,IP,hms,repeite,op,systime) VALUES(NULL,'$row[1]','$hms','$str',$select2,'$now')";
				mysqli_query($GLOBALS['db'],$sql_insert);
				//echo $sql_insert."</br>";
			}
			
		}
		
		
		
		
		
		
		

		
		//.$GLOBALS['select_time'] = $select;
		
		//echo $select."</br>";
		//echo $select1;
	}
	//echo "id=".$id."</br>";
	//echo "select=".$data_font."</br>";
	//echo "select=".$GLOBALS['select_time']."</br>";
	//print_r($day);
	//echo "data_font:".$GLOBALS['data_font'];
	
	
	
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">	
	<title>定時管理 </title>
	<script type="text/javascript">
		function display_alert()
		{
			alert("新增成功")
		}
	</script>

	<style>
		.chi {font-family:"標楷體";font-size:20px;}
		div, th, button, input, select{font-size:24px;}
	</style>
</head>
<body>
	<center>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;width:1440px;">
		<h3>新增定時開關</h3>
		</div>
		<br/>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;width:1440px;">
		
			<form name="form2" id="mode2" method="post" >
			選擇受控設備
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
				
			選擇定時類型
				<select name="select_mode1" id="select_mode1">
					<option value="1">執行一次</option>
					<option value="2">重複執行</option>
				</select>
				<input type="submit" name="Submit" value="Select" />
			</form>
		</div>
		</br>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;width:1440px;">
		
		<form name="form3" id="mode3" method="post" >
			<?php 
				if ($GLOBALS['data_font'] == '1') //單一時間
				{
					echo "選擇日期";
					echo "<input id=\"date\" type=\"date\" name=\"data\" id=\"data\" value=".date("Y-m-d").">";
					echo "選擇時";
					echo "<select name=\"select_mode3\" id=\"select_mode3\">";
						for ($i = 0;$i<23;$i++)
						{
							echo "<option value=\"".$GLOBALS['id']."_".$GLOBALS['data_font']."_".($i+1)."\">";
							echo ($i+1);
							echo "</option>";
						}
					echo "</select>";
					echo "選擇分";
					echo "<select name=\"select_mode4\" id=\"select_mode4\">";
						for ($i = 0;$i<60;$i++)
						{
							echo "<option value=\"".$GLOBALS['id']."_".$GLOBALS['data_font']."_".($i)."\">";
							echo ($i);
							echo "</option>";
						}
					echo "</select>";
					
					echo "選擇操作";
					echo "<select name=\"select_mode5\" id=\"select_mode5\">";
						echo "<option value=\"1\">開啟大燈</option>";
						echo "<option value=\"2\">開啟小燈</option>";
						echo "<option value=\"3\">開啟警示燈</option>";
						echo "<option value=\"4\">關閉燈具</option>";
						echo "<option value=\"5\">開啟自動感應</option>";
						echo "<option value=\"6\">關閉自動感應</option>";
						echo "<option value=\"7\">開啟燈光秀</option>";
						echo "<option value=\"8\">關閉燈光秀</option>";
					echo "</select>";
				}
				else if ($GLOBALS['data_font'] == '2') //重複
				{
					echo "選擇日期";
					echo "選擇時";
					echo "<select name=\"select_mode3\" id=\"select_mode3\">";
						for ($i = 0;$i<23;$i++)
						{
							echo "<option value=\"".$GLOBALS['id']."_".$GLOBALS['data_font']."_".($i+1)."\">";
							echo ($i+1);
							echo "</option>";
						}
					echo "</select>";
					echo "選擇分";
					echo "<select name=\"select_mode4\" id=\"select_mode4\">";
						for ($i = 0;$i<60;$i++)
						{
							echo "<option value=\"".$GLOBALS['id']."_".$GLOBALS['data_font']."_".($i)."\">";
							echo ($i);
							echo "</option>";
						}
					echo "</select>";
					
					echo "選擇操作";
					echo "<select name=\"select_mode5\" id=\"select_mode5\">";
						echo "<option value=\"1\">開啟大燈</option>";
						echo "<option value=\"2\">開啟小燈</option>";
						echo "<option value=\"3\">開啟警示燈</option>";
						echo "<option value=\"4\">關閉燈具</option>";
						echo "<option value=\"5\">開啟自動感應</option>";
						echo "<option value=\"6\">關閉自動感應</option>";
						echo "<option value=\"7\">開啟燈光秀</option>";
						echo "<option value=\"8\">關閉燈光秀</option>";
					echo "</select>";
					
					echo "</br>";
					echo "<input type=\"checkbox\" name=\"day[]\" value=\"-1\"><label>每天</label>";
					echo "<input type=\"checkbox\" name=\"day[]\" value=\"1\"><label>星期一</label>";
					echo "<input type=\"checkbox\" name=\"day[]\" value=\"2\"><label>星期二</label>";
					echo "<input type=\"checkbox\" name=\"day[]\" value=\"3\"><label>星期三</label>";
					echo "<input type=\"checkbox\" name=\"day[]\" value=\"4\"><label>星期四</label>";
					echo "<input type=\"checkbox\" name=\"day[]\" value=\"5\"><label>星期五</label>";
					echo "<input type=\"checkbox\" name=\"day[]\" value=\"6\"><label>星期六</label>";
					echo "<input type=\"checkbox\" name=\"day[]\" value=\"0\"><label>星期日</label>";
				}
					
			?>
			
				<input type="submit" name="Submit" onclick="display_alert()" value="Select" />
		</form>
		</div>
		
		</br>
			<table border="1" height="70" width="1440" align="center">
				<tr>						
					<th bgcolor="#97CBFF" width="500">
						<a href = "../light_manage/reserv.php">
						<input type="button" value="定時管理頁面" style="align;"></button>
						</a>
					</th>
					
					
				</tr>
		</table>
	</center
</body>
</html>