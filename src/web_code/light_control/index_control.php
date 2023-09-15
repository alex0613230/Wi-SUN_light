<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>操作人員功能</title>
<style>
	.chi {font-family:"標楷體";font-size:20px;}
	th, button, input {font-size:24px;}
</style>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
</head>

<body bgcolor="#FFFFFF">
<center>
<table border=5 cellspacing=0 bordercolor="#6363FF" cellpadding=10 width=50%>
<tr>
<th height=20 colspan=3><font size=7>操　作　人　員　功　能</font></th>
</tr>

<tr>
	<th width=50%;>
		<a href="./map.php"><img src="../pict/con_light.png" style="width:160px;height:150px;"></a><br>受控設備監控
	</th>
	<th>
		<a href="./ma.php"><img src="../pict/Ma.jpg" style="width:160px;height:150px;"></a><br>受控設備資料查詢
	</th>
</tr>
</table><p>


<table border=5 cellspacing=0 bordercolor="#6363FF" cellpadding=10 width=50%>
<tr>
						
<th bgcolor="#C9FFFF">
	<a href = "../index.php" >
	<input type="button" value="登出" style="align;"></button>
	</a>
</th>

</tr>


<?php
/*
	include("database/connect_db.php");
	date_default_timezone_set('Asia/Taipei');
	
	if (isset($_POST["reset"]))
	{
		
			<form action="" method="POST">
	<th bgcolor=red>
		<button type="submit" name="reset" style="width:180px;height:40px;">受控設備重置</button>
	</th>
	</form>	
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
				$sql = "INSERT INTO Command VALUE('$r','$GET_IP[$j]','0000000A01100000000102000A00',0,'$now')";
				$result = mysqli_query($GLOBALS['db'],$sql) or die("Error in query: $sql.". mysqli_error());
			}
			header("refresh: 0;");
	}
	
*/
?>

</table>
</center>
</body>
</html>
