<?php
require_once 'apiCaller.php';
require_once 'config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usrlogin']) && $_SESSION['usrlogin'] != '') {
	header ("Location: /study/index.php");
	exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accountid = $_POST['accountid'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? $_POST['remember'] : '';
	
	$apiCaller = new ApiCaller($baseAPI, 'token');
	$response = $apiCaller->call('POST', '/api/SApiService/Login', ['account_id' => $accountid, 'account_password' => $password, 'req_ip' => $_SERVER['REMOTE_ADDR']]);
	//var_dump($response);
	$data = $response['body'];    
    if (is_string($data)) {
        $data = json_decode($data, true);
    }	
	
	$success = isset($data['success']) ? $data['success'] : false;
	if($success){
		$ID = $data['user_id'];
		$_SESSION['usrlogin'] = $ID;		
		$_SESSION["usrdatav"] = $data;
		
		header("Location: /study/index.php");
        exit();
		
		//var_dump($data);
		//var_dump($ID);
		//var_dump($response);
	}
	else
		$error = "AccountID hoặc mật khẩu không đúng!";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
	<link rel="icon" type="image/ico" href="https://gamefam.org/favicon.ico">
    <link rel="stylesheet" href="/Assets/css/bootstrap-4.3.1.min.css">
	<link rel="stylesheet" href="/Assets/css/font-awesome-5.15.3.css">
    <style>
        body {
            background: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
        }
        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .form-signin .form-control {
            position: relative;
            box-sizing: border-box;
            height: auto;
            padding: 10px;
            font-size: 16px;
        }
        .form-signin .form-control:focus {
            z-index: 2;
        }
        .form-signin input[type="text"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }
        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: all 0.3s;
        }
        .logo {
            width: 100px;
            height: 100px;
        }
    </style>
</head>
<body class="text-center">
    <form class="form-signin" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <!--
		<img class="mb-4" src="https://getbootstrap.com/docs/4.3/assets/brand/bootstrap-solid.svg" alt="" width="72" height="72">
		-->
		<svg class="logo" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
			<rect width="100" height="100" rx="15" fill="#007bff"/>
			<text x="50" y="75" font-size="70" font-weight="bold" font-family="Arial, sans-serif" fill="white" text-anchor="middle">E</text>
		</svg>
        <h1 class="h3 mb-3 font-weight-normal">Đăng nhập</h1>
        <?php
        if (!empty($error)) {
            echo "<div class='alert alert-danger'>{$error}</div>";
        }
        ?>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
            </div>
            <input type="text" class="form-control" id="inputAccountId" name="accountid" placeholder="AccountID" required autofocus>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <input type="password" class="form-control" id="inputPassword" name="password" placeholder="Mật khẩu" required>
        </div>
        <div class="text-right mb-3">
            <a href="https://gamefam.org/register/pwforgot.php" class="text-muted">Quên mật khẩu?</a>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Đăng nhập</button>
        <div class="mt-3">
            <p>Chưa có tài khoản? <a href="https://gamefam.org/register/index.php">Đăng ký ngay</a></p>
        </div>        
        <p class="mt-5 mb-3 text-muted">&copy; 2024</p>
    </form>

    <script src="/Assets/js/jquery-3.3.1.min.js"></script>
	<script src="/Assets/js/popper-1.14.7.min.js"></script>
	<script src="/Assets/js/bootstrap-4.3.1.min.js"></script>
</body>
</html>