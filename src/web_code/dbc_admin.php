<?
	// 檢查是否輸入驗證資料
	if ((! isset($_SERVER['PHP_AUTH_USER'])) or (! isset($_SERVER['PHP_AUTH_PW'])))
	{
		header("WWW-Authenticate: Basic realm=\"Job Status Database\"");
		header("HTTP/1.0 401 Unauthorized");
		echo "您未輸入「使用者名稱」或「密碼」...";
		exit;
	}
	
	$PHP_AUTH_USER = $_SERVER['PHP_AUTH_USER'];
	$PHP_AUTH_PW = $_SERVER['PHP_AUTH_PW'];

	// 驗證資料
	$username = "light";
	$password = "light";
	$database = "light";
	$table = "users";
	$jobtable = "user_job";
	
	$link = @mysqli_connect("localhost", $username, $password, $database) or exit();
	
	// 檢查管理者身份
	$str = "select count(*) from $table where BINARY managename = '$PHP_AUTH_USER' and managepasswd = password('$PHP_AUTH_PW')";
	$result = @mysqli_query($link, $str);

	$a = getdate();
	$dd = $a["year"] . "/" . $a["mon"] . "/" . $a["mday"];
	$dd = $dd . " " . $a["hours"] . ":" . $a["minutes"] . ":" . $a["seconds"];

	if (!$result)
	{
		if (($PHP_AUTH_USER == "admin") and ($PHP_AUTH_PW == "admin"))
		{
			// create the table
			$str = "drop table if exists $table";
			$res = mysqli_query($link, $str);
			$str = "create table $table (
				managename char(20) not null,
				name char(20) not null,
				managepasswd char(50) not null,
				permit int unsigned not null,
				msg char(60) not null,
				logindate char(24),
				cell char(15),
				email char(50)
			)";
			$re1 = mysqli_query($link, $str);


			$str = "insert into $table values ('$PHP_AUTH_USER', 'admin', password('$PHP_AUTH_PW'), 1, 'Default', '$dd', '', '')";
			$re2 = mysqli_query($link, $str);
			$str = "insert into $table values ('user', 'admin', password('user'), 3, 'Default', '$dd', '', '')";
			$re2 = mysqli_query($link, $str);
			$str = "select count(*) from $table where managename = '$PHP_AUTH_USER' and managepasswd = password('$PHP_AUTH_PW')";
			$result = @mysqli_query($link, $str);
		}
		else
		{
			header("WWW-Authenticate: Basic realm=\"Job Status Database\"");
			header("HTTP/1.0 401 Unauthorized");
			echo "未輸入「使用者名稱」或「密碼」錯誤 ...";
			exit;
		}
	}

	list($copp) = mysqli_fetch_row($result);
	if ($copp != 1)
	{
		header("WWW-Authenticate: Basic realm=\"Job Status Database\"");
		header("HTTP/1.0 401 Unauthorized");
		echo "「使用者名稱」或「密碼」錯誤 ...";
		echo($copp);
		exit;
	}
	mysqli_free_result($result);

	// 讀取權限與上一次登入時間
	$str = "select permit, logindate from $table where BINARY managename = '$PHP_AUTH_USER' and managepasswd = password('$PHP_AUTH_PW')";
	$result = mysqli_query($link, $str);
	list($permit, $lastlogin) = mysqli_fetch_row($result);
	mysqli_free_result($result);
	
	// 修改登入時間
	$str = "update $table set logindate='$dd' where BINARY managename = '$PHP_AUTH_USER' and managepasswd = password('$PHP_AUTH_PW')";
	$result = mysqli_query($link, $str);
?>
