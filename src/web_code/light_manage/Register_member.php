<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta Name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>使用者管理</title>
		<style>
			a:hover {background-color:blue;color:white;}
			a {text-decoration:none;}
			.chi {font-family:"標楷體";font-size:24px;}
			th, button, input {font-size:24px;}
		</style>
	</head>
<?	
	session_start();
	include("database.php");
	
?>
	<body>
				<form action="" method=post>
					<table border="1" width="80%" align="center">
					<?
						if($_SESSION['permit'] == 1)
						{
							echo "<tr height = '40'>
								<th colspan='5'  bgcolor='#97CBFF'>
									<p style='font-size:28px'>管理人員功能</p>
								</th></tr>";
							echo 
							"<tr  bgcolor='#FCFCFC' height = '50'>
								<th width='20%'>
									<p>使用者管理</p>
							       </th>
							       <th width='20%'>
									<a href='./ma.php'>受控設備管理</a>
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
							
							echo"</tr><tr  bgcolor='#FCFCFC' height = '50'>
								<th  colspan='5'>
									<button type='submit' value='1' Name='set'>新增帳號</button>
									<button type='submit' value='2' Name='set'>修改帳號</button>
									<button type='submit' value='3' Name='set'>刪除帳號</button>
								</th></tr>";
						}
						if($_SESSION['permit'] == 2)
						{
							"<tr  bgcolor='#FCFCFC' height = '50'>
								<th width='25%'>
									<p>使用者管理</p>
							       </th>
							       <th width='25%'>
									<p>受控設備管理</p>
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
							
							echo"</tr><tr  bgcolor='#FCFCFC' height = '50'>
								<th  colspan='5'>
									<button type='submit' value='1' Name='set'>新增帳號</button>
									<button type='submit' value='2' Name='set'>修改帳號</button>
									<button type='submit' value='3' Name='set'>刪除帳號</button>
									<input type='button' value='返回系統操作功能' onclick='location.href='./sys.php'' style='align;'></button>
								</th></tr>";
						}
					?>
						
					</table>
					
								
					
					<?
						if(isset($_POST['set']))
						{
							$set = $_POST['set'];
							switch($set)
							{
								case '1' : 
									echo "<table border='2' width='80%' align='center' height='10'><br><tr bgcolor='#97CBFF'>";
									echo "<th colspan='2'><font size=5>新增操作人員</font>";
									echo "</th></tr>";
									echo "<tr><th  width='20%'>登入資訊</th><th>";
									echo "帳號&nbsp&nbsp:&nbsp&nbsp<input type='text' Name='acc' size='8'/ >&nbsp&nbsp";
									echo "密碼&nbsp&nbsp:&nbsp&nbsp<input type='text' Name='pwd' size='8'/ >&nbsp&nbsp";
									echo "</th><tr><th  width='20%' >管理層級</th><th>";
									echo "<input type='radio' name='id' value='2'>一般管理人員　<input type='radio' name='id' value='3'>操作人員</th></tr>";
									echo "<tr><th colspan='2'><input type='submit' Name='add' value='提交' />";
									echo "</th></tr></table>";
									break;
								case '3' :
									echo "<table border='2' width='80%' align='center'><br><tr bgcolor='#97CBFF'>";
									echo "<th><font size=5>刪除操作人員</font>";
									echo "</th></tr>";
									echo "<tr><th>";
									echo "帳號&nbsp&nbsp:&nbsp&nbsp<input type='text' Name='acc' size='8'/ >&nbsp&nbsp";
									echo "<input type='submit' Name='del' value='提交' />";
									echo "</th></tr></table>";
									break;
									
								case '2' :
									echo "<table border='2' width='80%' align='center'><br><tr bgcolor='#97CBFF'>";
									echo "<th><font size=5>變更密碼</font>";
									echo "</th></tr>";
									echo "<tr><th>";
									echo "帳號&nbsp&nbsp:&nbsp&nbsp<input type='text' Name='acc' size='8'/ >&nbsp&nbsp";
									echo "密碼&nbsp&nbsp:&nbsp&nbsp<input type='text' Name='pwd' size='8'/ >&nbsp&nbsp";
									echo "<input type='submit' Name='alter' value='提交' />";
									echo "</th></tr></table>";

									break;
							}
						}
					?>
								
									<table border='2' width='80%' align='center'><br>
									<tr bgcolor='#97CBFF'><th COLSPAN=3 ALIGN=CENTER>
									<font size=5>資料顯示</font></th></tr>
									<tr  bgcolor='#FCFCFC'><th width='240'>帳號</th><th width='300'>身分別</th>
									<?
										
										$sql = "SELECT managename,permit FROM  users order by permit";
										$result = mysqli_query($con, $sql);

										$i=0;
										while(list($acc[$i], $id[$i]) = mysqli_fetch_row($result))
										$i ++;

										for($j = 0; $j < $i; $j++)
										{
 											
											echo "<tr   bgcolor=\"#FCFCFC\"><th>" .$acc[$j]. "</th><th>";
											switch($id[$j])
											{
												case 1:
													echo "超級管理人員";
													break;
												case 2:
													echo "一般管理人員";
													break;
												case 3:
													echo "操作人員";
													break;
											}
											
											echo "</th></tr>";
											unset($ch);
										}
										mysqli_free_result($result);
										
										echo "</table>";
									
						
						if(isset($_POST['add']))
						{ 
							$acc = $_POST['acc'];
							$pwd = $_POST['pwd'];
							$id = $_POST['id'];
							if($acc == NULL ||$pwd == NULL || $id == NULL)
							{
								echo "<script>alert('帳號和密碼不能為空');</script>";
							}
							else
							{
								$sql = "select count(*) from users where managename = '$acc'";
								$result = mysqli_query($con,$sql) or die("Error in query: $query.". mysqli_error());
								$n = mysqli_fetch_row($result);
								$i = 0;
								if($n[0] != 0)
								{
									$i++;
									echo "<script>alert('重複出現 帳號 :".$acc."');</script>";
								}
								else
								{	
									mysqli_free_result($result);
									$query = "INSERT INTO users(managename,name,managepasswd,permit,msg) VALUE('$acc','$acc',password('$pwd'),$id,'Default')"; 
									
									$result = mysqli_query($con,$query) or die("Error in query: $query. ". mysqli_error());
									echo "<script>alert('新增成功');</script>";
								}
							}
							mysqli_free_result($result);
							mysqli_close($con);	
							header("refresh: 0;");
						} 

						if(isset($_POST['del']))
						{ 
							$acc = $_POST['acc'];
							if($acc == NULL)
							{
								echo "<script>alert('帳號不能為空');</script>";
							}
							else if($acc == 'admin')
							{
								echo "<script>alert('此帳號無法刪除');</script>";
							}
							else
							{
								$query = "delete from users where managename='$acc'"; 
								$result = mysqli_query($con,$query) or die("Error in query: $query. ". mysqli_error()); 
								mysqli_free_result($result);
								echo "<script>alert('刪除成功');</script>";
							}
							mysqli_close($con); 
							header("refresh: 0;");
						} 
						
						if(isset($_POST['alter']))
						{
							$acc = $_POST['acc'];
							$pwd = $_POST['pwd'];
							$query = "update users set managepasswd = password('$pwd') where managename = '$acc'"; 
							$result = mysqli_query($con,$query) or die("Error in query: $query. ". mysqli_error()); 
							mysqli_close($con); 
							header("refresh: 0;");
						}
					?><br>
					<table border="1" height="70" width="80%" align="center">
					<tr>
						
						<th bgcolor="#C9FFFF">
							<a href = "../index.php" >
							<input type="button" value="登出" style="align;"></button>
							</a>
						</th>

					</tr>
				</form>
	</body>
</html>