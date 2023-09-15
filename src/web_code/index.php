
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>智慧校園照明系統</title>
<style>
	.chi {font-family:"標楷體";font-size:20px;}
	th, button, input {font-size:24px;}
</style>
</head>

<body bgcolor="#FFFFFF">
<form action="" method=post>
<?php
	session_start();
	session_unset();
	if (isset($_POST['member']))
	{
		$acc = $_POST['acc'];
		$pwd = $_POST['pwd'];
		if($acc == NULL || $pwd == NULL)
		{
			echo "<script>alert('請輸入帳號密碼')</script>";
		}
		else
		{
			//include("database.php");
			include("database/connect_db.php");
			$sql = "select count(*) from users where BINARY managename = '$acc'";
			
			//$sql = "select count(*) from account where acc = '$acc'";
			$result = mysqli_query($GLOBALS['db'], $sql) or die("Error in query: $query.". mysqli_error());
			$n = mysqli_fetch_row($result);
			if($n[0] != 1)
			{
				echo "<script>alert('使用者帳號或密碼輸入錯誤')</script>";
			}
			else
			{
				mysqli_free_result($result);
				$sql = "select count('$acc') from users where managepasswd = password('$pwd')";
				
				//$sql = "select count('$acc') from account where pwd = '$pwd'";
				$result = mysqli_query($GLOBALS['db'], $sql) or die("Error in query: $query.". mysqli_error());
				$n = mysqli_fetch_row($result);
				if($n[0] != 1)
				{
					echo "<script>alert('使用者帳號或密碼輸入錯誤')</script>";
				}
				else
				{
					mysqli_free_result($result);
					$_SESSION['user'] = $acc;
					
					$sql = "select permit from users where BINARY managename = '$acc'";
							
					//$sql = "select priority from account where acc = '$acc'";
					$result = mysqli_query($GLOBALS['db'], $sql) or die("Error in query: $query.". mysqli_error());
					$h = mysqli_fetch_row($result);
					$_SESSION['priority'] = $h[0];
					mysqli_free_result($result);
					
					if($h[0] == 3)
					{
						$_SESSION['permit'] = (int)$h[0];
						//header('Location:./user.php');
						header('Location:./light_control/index_control.php');
					}
					else if($h[0] == 1 )
					{
						$_SESSION['permit'] = (int)$h[0];
						//header('Location:./project/pwd/sys.php');
						header('Location:./light_manage/Register_member.php');
					}
					else if($h[0] == 2 )
					{
						$_SESSION['permit'] = (int)$h[0];
						//header('Location:./project/pwd/sys.php');
						header('Location:./light_manage/ma.php');
					}
				}
			}
			
			mysqli_close($con);
		}

	}
?>
<center>
<table border=5 cellspacing=0 bordercolor="#6363FF" cellpadding=10 width=50%>
<tr>
	<th colspan='2'>
	智　慧　校　園　照　明　系　統
	</th>
</tr>
<tr>

<th>
	<img src="./pict/Light.png" style="width:160px;height:150px;">
</th>
	<th>
	帳號 : <input type=text name="acc" style="width:300px;height:10dpx;" maxlength="20"><br><br>
	密碼 : <input type=password name="pwd" style="width:300px;height:10dpx;" maxlength="20">
	</th>
</tr>
<tr>
	<th colspan='2'>
	<input type="submit" Name="member" value="提交" /> 
	</th>
</tr>
</table><p>

</center>

</form></body>
</html>
