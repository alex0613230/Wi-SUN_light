<?php
	include("../database/connect_db.php");
	date_default_timezone_set('Asia/Taipei');
	$id="";
	$data_font="";
	$sselect_time="";
	
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
		$GLOBALS['data_font'] = $select1;
	}
	
	
	if (isset($_POST["select_mode2"])) 
	{  
		$select2=$_POST["select_mode2"];
		$GLOBALS['select_time'] = $select2;
	}
	
	
	if (isset($_POST["select_mode4"]))
	{
		$select=$_POST["select_mode4"];
		$select1=$_POST["select_mode5"];
		
		$da = explode('_',$select1);
		$select .= "_".$da[2];
		
		$GLOBALS['select_time'] = $select;
		
		//echo $select."</br>";
		//echo $select1;
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
	<title>smart light 統計資料 </title>	
	
	<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js" ></script> 
	<script src = "jquery-tablepage-1.0.js" ></script> 
	
	<style>
		.chi {font-family:"標楷體";font-size:20px;}
		div, th, button, input, select{font-size:24px;}
	</style>
	
	
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawChart);
	<?php
	
		$data = explode('_',$GLOBALS['select_time']);
		$sql = "";
		if ($data[0] == '1') $sql = "SELECT * FROM Time"; //all
		else //single
		{
			$light = "SELECT * FROM light WHERE Name='$data[0]'";
			$ip = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$light));
			$sql = "SELECT * FROM Time WHERE IP='$ip[1]'";
		}
		//echo"sql:".$sql;
		$mon = array(0,0,0,0,0,0,0,0,0,0,0,0);
		
		for ($i=0;$i<12;$i++) //0~12
		{
			$rows = mysqli_query($GLOBALS['db'],$sql);
			$num = mysqli_num_rows($rows);
			
			$ww1 = array(0,0,0);
			$ww3 = array(0,0,0);
			$wr3 = array(0,0,0);
			$total = array(0,0,0);
			
			for ($j=0;$j<$num;$j++)
			{
				$row = mysqli_fetch_row($rows);
				$data_time = explode(' ',$row[6]); //資料的 systime
				$ymd = explode('-',$data_time[0]); 
				
				if ($ymd[0] == $data[2]) //check year
				{
					if ((int)$ymd[1] == ($i+1))
					{						
						$row3 = explode(':',$row[3]); //www3
						$row4 = explode(':',$row[4]); //ww1
						$row5 = explode(':',$row[5]); //wr3
						
						$ww1[0] +=(int)$row4[0];
						$ww1[1] +=(int)$row4[1];
						$ww1[2] +=(int)$row4[2];
							
						$ww3[0] +=(int)$row3[0];
						$ww3[1] +=(int)$row3[1];
						$ww3[2] +=(int)$row3[2];
							
						$wr3[0] +=(int)$row5[0];
						$wr3[1] +=(int)$row5[1];
						$wr3[2] +=(int)$row5[2];
						
					}
				}				
			}
			$total[0] = $ww1[0] + $ww3[0] + $wr3[0];
			$total[1] = $ww1[1] + $ww3[1] + $wr3[1];
			$total[2] = $ww1[2] + $ww3[2] + $wr3[2];
			$month_da = $total[0] + ($total[1]/60)+($total[2]/60/60);
			$month_da = round($month_da,2);
			$mon[$i] = $month_da;
			//echo $data[2]."年".$i."月monda = ".$month_da."</br>";
		}
		echo "function drawChart() {";
			echo "var data = google.visualization.arrayToDataTable([";
			echo "['month', 'hour'],";
			for ($i=0;$i<12;$i++) //0~12
			{
				if ($i!=11)
					echo "['".($i+1)."', ".$mon[$i]."],";
				else
					echo "['".($i+1)."', ".$mon[$i]."]";
			}
		echo "]);";
	?>
	/*
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Year', 'Sales', 'Expenses'],
          ['2013',  1000,      400],
          ['2014',  1170,      460],
          ['2015',  660,       1120],
          ['2016',  1030,      540]
        ]);
	*/
        var options = {
          //title: 'Company Performance',
		  <?php 
			$data = explode('_',$GLOBALS['select_time']);
			if ($data[0] == '1') echo "title: '全部受控設備".$data[2]."年每月總開啟時間分布圖',";
			else echo "title: '".$data[0]."受控設備".$data[2]."年每月總開啟時間分布圖',";
		  ?>
          hAxis: {title: 'month',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
	</script>
</head>
<body>
	<center>
	
		<div style="border-width:5px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF; width:1440px;">
		<h3>統計管理</h3>		
		<table border="1" width="1440px" align="center">
				
					      <tr  bgcolor="#FCFCFC" height = "50">
					       <th width="360">
							<a href="./ma.php">受控設備管理</a>
					       </th>
						   	<th width="360">
								<a href="./reserv.php">定時管理</a>
					       </th>
					       <th width="360">
							<p>統計管理</p>
					       </th>
					       <th width="360">
							<a href="../light_control/alert_lift.php">警報管理</a> 
					       </th>
					      </tr>
						
		</table>
		</div>
		
		
		
		<?php //show data
			if (!empty($GLOBALS['select_time']))
			{
				$data = explode('_',$GLOBALS['select_time']);
				
				//預先撈資料
				$sql = "";
				if ($data[0] == '1') $sql = "SELECT * FROM Time ORDER BY systime DESC" ; //all
				else //single
				{
					$light = "SELECT * FROM light WHERE Name='$data[0]'";
					$ip = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$light));
					$sql = "SELECT * FROM Time WHERE IP='$ip[1]' ORDER BY systime DESC";
				}
				//echo"sql:".$sql;
				$rows = mysqli_query($GLOBALS['db'],$sql);
				$num = mysqli_num_rows($rows);
				
				$ww1 = array(0,0,0);
				$ww3 = array(0,0,0);
				$wr3 = array(0,0,0);
				$total = array(0,0,0);
				
				if ($data[1] == '1') //年資料
				{
					
					for ($i=0;$i<$num;$i++)
					{
						$row = mysqli_fetch_row($rows);
						
						$data_time = explode(' ',$row[6]); //資料的 systime
						$ymd = explode('-',$data_time[0]); 
						//echo "data[2]".$data[2];
						if ($ymd[0] == $data[2])//check year
						{
							//echo $row[0]."/".$row[1]."/".$row[3]."/".$row[4]."/".$row[5]."/".$row[6];
							//echo "</br>";
							$row3 = explode(':',$row[3]); //www3
							$row4 = explode(':',$row[4]); //ww1
							$row5 = explode(':',$row[5]); //wr3
							
							$ww1[0] +=(int)$row4[0];
							$ww1[1] +=(int)$row4[1];
							$ww1[2] +=(int)$row4[2];
							
							$ww3[0] +=(int)$row3[0];
							$ww3[1] +=(int)$row3[1];
							$ww3[2] +=(int)$row3[2];
							
							$wr3[0] +=(int)$row5[0];
							$wr3[1] +=(int)$row5[1];
							$wr3[2] +=(int)$row5[2];
						}
						
						//echo "row[0]:".$row[0]."row[6]:".$row[6];
						//echo "</br>";
					}
					
					/*
					echo "ww1:".$ww1[0].":".$ww1[1].":".$ww1[2];
					echo "</br>";
					echo "ww3:".$ww3[0].":".$ww3[1].":".$ww3[2];
					echo "</br>";
					echo "wr.:".$wr3[0].":".$wr3[1].":".$wr3[2];
					echo "</br>";
					*/
					
					$total[0] = $ww1[0] + $ww3[0] + $wr3[0];
					$total[1] = $ww1[1] + $ww3[1] + $wr3[1];
					$total[2] = $ww1[2] + $ww3[2] + $wr3[2];
					
					$ww1 = time_trans($ww1);
					$ww3 = time_trans($ww3);
					$wr3 = time_trans($wr3);
					$total = time_trans($total);
					
					/*
					echo "ww1:".$ww1[0].":".$ww1[1].":".$ww1[2];
					echo "</br>";
					echo "ww3:".$ww3[0].":".$ww3[1].":".$ww3[2];
					echo "</br>";
					echo "wr.:".$wr3[0].":".$wr3[1].":".$wr3[2];
					echo "</br>";
					*/
					
				
				}
				else if ($data[1] == '2') // 月資料
				{
					for ($i=0;$i<$num;$i++)
					{
						$row = mysqli_fetch_row($rows);
						
						$data_time = explode(' ',$row[6]); //資料的 systime
						$ymd = explode('-',$data_time[0]); //選擇的 y
						//echo "data[2]".$data[2];
						if (($ymd[0] == $data[2]) && ($ymd[1] == $data[3]))//check year and month
						{
							//echo $row[0]."/".$row[1]."/".$row[3]."/".$row[4]."/".$row[5]."/".$row[6];
							//echo "</br>";
							$row3 = explode(':',$row[3]); //www3
							$row4 = explode(':',$row[4]); //ww1
							$row5 = explode(':',$row[5]); //wr3
							
							$ww1[0] +=(int)$row4[0];
							$ww1[1] +=(int)$row4[1];
							$ww1[2] +=(int)$row4[2];
							
							$ww3[0] +=(int)$row3[0];
							$ww3[1] +=(int)$row3[1];
							$ww3[2] +=(int)$row3[2];
							
							$wr3[0] +=(int)$row5[0];
							$wr3[1] +=(int)$row5[1];
							$wr3[2] +=(int)$row5[2];
						}
						
					}
					$total[0] = $ww1[0] + $ww3[0] + $wr3[0];
					$total[1] = $ww1[1] + $ww3[1] + $wr3[1];
					$total[2] = $ww1[2] + $ww3[2] + $wr3[2];
					
					$ww1 = time_trans($ww1);
					$ww3 = time_trans($ww3);
					$wr3 = time_trans($wr3);
					$total = time_trans($total);
				}
					echo "</br>";
					
					if (($data[1] == '1') && (!empty($GLOBALS['select_time'])))
					{
						echo "<div style=\"border-width:3px;border-style:solid;border-color:blue;padding:5px;background-color:#97CBFF;width:1440px;\">";
						echo "<div id=\"chart_div\"></div>";
						echo "</div>";
					}
					
					echo "</br>";
					
				//show table==========================================
					echo "<div style=\"border-width:5px;border-style:solid;border-color:blue;padding:5px;width:1440px;\">";
					
					//data="id_年or月資料_選則時間
					if ($data[0] == '1' && $data[1] == '1') echo "<h3>".$data[2]."年全部受控設備統計資料</h1>";
					else  if ($data[0] != '1' && $data[1] == '1')echo "<h3>".$data[2]."年".$data[0]."受控設備統計資料</h3>";
					else if ($data[0] == '1' && $data[1] == '2')
					{
						echo "<h3>".$data[2]."年".$data[3]."月全部統計資料"."</h3>";
					}
					else if ($data[0] != '1' && $data[1] == '2')
					{
						echo "<h3>".$data[0]."受控設備".$data[2]."年".$data[3]."月統計資料"."</h3>";
					}
					
				echo "<table border=\"1\" width=\"1440\" align=\"center\" >";
					echo "<thead>";
						echo "<tr>";
							echo "<th>小燈總開啟時間</th>";
							echo "<th>大燈總開啟時間</th>";
							echo "<th>紅燈總開啟時間 </th>";
							echo "<th>總和開啟時間</th>";
						echo "</tr>";
					echo "</thead>";
					echo"<tbody>";
							echo "<tr>";
							echo "<td>".str_pad(strval($ww1[0]),2,"0",STR_PAD_LEFT).":".str_pad(strval($ww1[1]),2,"0",STR_PAD_LEFT).":".str_pad(strval($ww1[2]),2,"0",STR_PAD_LEFT) ."</td>";
							
							echo "<td>".str_pad(strval($ww3[0]),2,"0",STR_PAD_LEFT).":".str_pad(strval($ww3[1]),2,"0",STR_PAD_LEFT).":".str_pad(strval($ww3[2]),2,"0",STR_PAD_LEFT) ."</td>";
							
							echo "<td>".str_pad(strval($wr3[0]),2,"0",STR_PAD_LEFT).":".str_pad(strval($wr3[1]),2,"0",STR_PAD_LEFT).":".str_pad(strval($wr3[2]),2,"0",STR_PAD_LEFT) ."</td>";
							
							echo "<td>".str_pad(strval($total[0]),2,"0",STR_PAD_LEFT).":".str_pad(strval($total[1]),2,"0",STR_PAD_LEFT).":".str_pad(strval($total[2]),2,"0",STR_PAD_LEFT) ."</td>";
							echo "</tr>";
					echo"</tbody>";
					echo"</table>";
					echo "</br>";
					echo "<table id=\"tb1\" border=\"1\" width=\"1440\" align=\"center\" >";
					
					echo "<thead>";
						echo "<tr>";
							echo "<th> 受控設備編號 </th>";
							echo "<th> 紀錄編號 </th>";
							echo "<th> IPv6 位址 </th>";
							echo "<th> 大燈總開啟時間 </th>";
							echo "<th> 小燈總開啟時間 </th>";
							echo "<th> 警示燈總開啟時間 </th>";
							echo "<th> 紀錄時間 </th>";
						echo "</tr>";
					echo "</thead>";
					echo"<tbody";
								$rows = mysqli_query($GLOBALS['db'],$sql);
								$num = mysqli_num_rows($rows);
								for ($i = 0;$i<$num;$i++)
								{
									$row = mysqli_fetch_row($rows);
									
									$light = "SELECT * FROM light WHERE IP='$row[1]'";
									$light_data = mysqli_fetch_row(mysqli_query($GLOBALS['db'],$light));
									
									$data_time = explode(' ',$row[6]); //資料的 systime
									$ymd = explode('-',$data_time[0]); //systime 的 ymd
 									if ($data[1] == '1') //年資料
									{
										if ($ymd[0] == $data[2]) //只見檢查年
										{
											if (($row[3] == "00:00:00") && ($row[4] == "00:00:00") && ($row[5] == "00:00:00")) continue;
											echo "<tr>";
											echo "<td>" . $light_data[0] ."</td>";
											
											echo "<td>" . str_pad(strval($row[0]),4,"0",STR_PAD_LEFT) ."</td>";
											echo "<td>" . $row[1] ."</td>";
											echo "<td>" . $row[3] ."</td>";
											echo "<td>" . $row[4] ."</td>";			
											echo "<td>" . $row[5] ."</td>";
											echo "<td>" . $row[6] ."</td>";
											echo "</tr>";
										}
									}
									else if ($data[1] == '2')
									{
										//echo "ymd:".$ymd[0]."-".$ymd[1];
										//echo "data".$data[2]."-".$data[3];
										if (($ymd[0] == $data[2]) && ($ymd[1] == $data[3])) //只見檢查年
										{
											if (($row[3] == "00:00:00") && ($row[4] == "00:00:00") && ($row[5] == "00:00:00")) continue;
											echo "<tr>";
											echo "<td>" . $light_data[0] ."</td>";
											
											echo "<td>" . str_pad(strval($row[0]),4,"0",STR_PAD_LEFT) ."</td>";
											echo "<td>" . $row[1] ."</td>";
											echo "<td>" . $row[3] ."</td>";
											echo "<td>" . $row[4] ."</td>";			
											echo "<td>" . $row[5] ."</td>";
											echo "<td>" . $row[6] ."</td>";
											echo "</tr>";
										}
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
			$("#tb1").tablepage($("#table_page"), 10);
		</script>
		
		
			<table border="1" height="70" width="1440" align="center">
				<tr>						
					<th bgcolor="#97CBFF" width="500">
						
						<a href = "../index.php">
						<input type="button" value="登出" style="align;"></button>
						</a>
					</th>
				</tr>
		</table>
	</center
</body>
</html>