<?php

	include("../database/connect_db.php");

	emergency_show();
	//light_error_show();
	//toupper();
	$emergency = false;
	
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="refresh" content="3">
	<?php
		//insert_command("00000006010300010005","all");
	?>
	<title>受控設備監控</title>	
	<?php //有緊急事件啟動警報聲
			//if ($check) echo "<embed src=\"voice/alert.mp3\" hidden=\"true\" autostart=\"true\" loop=\"true\" />";
	?>
	<!--
	<style>
		.chi {font-family:"標楷體";font-size:20px;}
		div, th, button, input, select{font-size:24px;}
	</style>
	-->
</head

<body>
		<center>
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;margin: auto; width:1440px; ">
			<h2 id="aaa">受控設備監控</h2>	
		</div>
		
		<br/>
		<div  style="border-width:5px;border-style:solid;border-color:blue; position: relative; margin: auto; width:1440px;">
		<br/>
		
		<table width="100%" >
		<!--style="border:3px #cccccc solid;" cellpadding="10" border='1' -->
			</tr>
				<tr>
				<td height="30px">
				</td>
			</tr>
			<tr>
				<td rowspan="11"  width="150px">
				</td>
			</tr>
			<tr>
				<td rowspan="11"  width="770px">
						<img src="../pict/map.png" width="660px" height="460px" usemap="#light" >
				</td>
			</tr>
			<tr>
				<td>
					<img src="../pict/all_ww1.gif" usemap="#icon20"  width="100px" height="60px">
				</td>
			</tr>
			<tr>
				<td  width="110px">
					<img src="../pict/all_ww3.gif" usemap="#icon21"  width="100px" height="60px">
				</td>
				<td>
					<img src="../pict/all_close_control.gif" usemap="#icon23"  width="100px" height="60px">
				</td>
			</tr>
			<tr>
				<td>
					<img src="../pict/all_alert.gif" usemap="#icon22"  width="100px" height="60px">
				</td>
			</tr>
			<tr>
				<td colspan="2" ><hr align="left" style="border: 2px solid black; background-color:black; width:45%;"/></td>
			</tr>
			<tr>
				<td width="110px">
					<img src="../pict/all_auto_on.gif" usemap="#icon24"  width="100px" height="60px">
				</td>
				<td>
					<img src="../pict/all_auto_off.gif" usemap="#icon25"  width="100px" height="60px">
				</td>
			<tr>
				<td colspan="2" ><hr align="left" style="border: 2px solid black; background-color:black; width:45%;"/></td>
			</tr>
			<tr>
				<td>
					<img src="../pict/light_show_on.gif" usemap="#icon27"  width="100px" height="60px">
				</td>
				<td>
					<img src="../pict/light_show_off.gif" usemap="#icon28"  width="100px" height="60px">
				</td>
			</tr>
			<tr>
				<td colspan="2" ><hr align="left" style="border: 2px solid black; background-color:black; width:45%;"/></td>
			</tr>
			<tr>
				<td>
					<img src="../pict/all_alert_lift.gif" usemap="#icon26"  width="100px" height="60px">
				</td>
				
				<td>
					<img src="../pict/all_reset.gif" usemap="#icon30"  width="100px" height="60px">
				</td>
			</tr>
			<tr>
				<td height="10px" > </td>
			</tr>
			<tr>						
						<td colspan="4" bgcolor="#97CBFF" align="center">
							<a href = "./index_control.php">
							<input type="button" value="返回操作人員功能" style="align;"></button>
							</a>
						</td>
			</tr>
		</table>
		
		</div>
	
		<div style="position: relative;width:1440px;">
			<?php
			
			//sql=====================================
		
			$rows_light = show_table_all_info("light");
			$num = mysqli_num_rows($rows_light);
			//========================================
			
				for ($i=0;$i<$num;$i++)
				{
					$row = mysqli_fetch_row($rows_light);
					$pict="";
					
					switch ((int)$row[2]) 
					{
						case 0: //閒置
							$pict = "../pict/all_close.gif";
							break;
						case 1: // auto on
							$pict = "../pict/auto.gif";
							break;
						case 2: // auto off
							$pict = "../pict/all_close.gif";
							break;						
						case 3: //ww3
							$pict = "../pict/ww3.gif";
							break;
						case 4: //ww1
							$pict = "../pict/ww1.gif";
							break;
						case 5: //wr3
							$pict = "../pict/wr3.gif";
							break;
						case 6: //close
							$pict = "../pict/all_close.gif";
							break;
						case 7: //7 emergency
							$pict = "../pict/alert.gif";
							$GLOBALS['emergency'] = true;
							break;
						case 10: //7 loght _error
							$pict = "../pict/fix.png";
							break;
						case 8: //8 light show on
							$pict = "../pict/show_on.gif";
							break;
						case 9: //9 light show off
							$pict = "../pict/all_close.gif";
							break;
						default:
							$pict = "../pict/all_close.gif";
					}
					echo "<img id=\"".$id."\" ";
					//echo "a;t=\"".$row[0]."\" ";
					echo "src=";
					echo "\"" . $pict ."\"";
					echo " usemap=\"#icon".$i."\"";
					echo" style=\"position: relative;";
					echo" top:".$row[4]."px; left:".$row[5]."px\" width=\"29px\" heigh=\"29px\">";
				}
				
				
				//<img src="aoff.gif" usemap="#icon0" style="position: relative; top: -540px; left:-40px">
				//<map name="icon0">
				//<area shape="default" href="./light.cgi?ACTION=6">
				//</map>				
			?>
			
			<?php
			
				//sql=====================================
				$rows_light = show_table_all_info("light");
				$num = mysqli_num_rows($rows_light);
				//========================================
				
				for ($i=0;$i<$num;$i++)
				{
					$row = mysqli_fetch_row($rows_light);
					echo "<map name=\"icon".$i."\">";
					echo "<area shape=\"default\" href=\"map_process.php?id=".$row[0]."\">";
					echo "</map>";
				}
				mysqli_close($GLOBALS['db']);
			?>
			
			<map name="icon20"> <!-- all ww1-->
				<area shape="default" href="map_process.php?id=all_ww1">
			</map>		
			
			<map name="icon21"> <!-- all ww3-->
				<area shape="default" href="map_process.php?id=all_ww3">
			</map>		
			
			<map name="icon22"> <!-- all all alert-->
				<area shape="default" href="map_process.php?id=all_alert">
			</map>		
			
			<map name="icon23"> <!-- all close control-->
				<area shape="default" href="map_process.php?id=all_close_control">
			</map>		
			
			<map name="icon24"> <!-- all auto on-->
				<area shape="default" href="map_process.php?id=all_auto_on">
			</map>		
			
			<map name="icon25"> <!-- all auto off-->
				<area shape="default" href="map_process.php?id=all_auto_off">
			</map>		
			<map name="icon26"> <!-- all auto off-->
				<area shape="default" href="map_process.php?id=all_alert_lift">
			</map>		
			<map name="icon27"> <!-- all auto off-->
				<area shape="default" href="map_process.php?id=all_light_show_on">
			</map>		
			<map name="icon28"> <!-- all auto off-->
				<area shape="default" href="map_process.php?id=all_light_show_off">
			</map>		
			<map name="icon30"> <!-- all auto off-->
				<area shape="default" href="map_process.php?id=all_reset">
			</map>		
		
		</div>
		
			<? if ($GLOBALS['emergency'] == true): ?>
				<audio autoplay="autoplay" controls="controls"loop="loop" hidden="true" preload="auto" src="../voice/alert.mp3">
				</audio>
			<? endif; ?>
			
		<div style="position: relative;width:1440px;">
		<img src="../pict/hint_1.gif" usemap="#icon8" style="position: relative; top:-615px; left:10px " width="360px" heigh="140px">
		</div>
		
	</center>
	
</body>
</html>