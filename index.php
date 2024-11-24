<?php
// โหลดค่าการตั้งค่าจาก config.php
$config = require 'config.php';

// สร้าง URL สำหรับ Login ด้วย LINE
$client_id = $config['channel_id'];
$redirect_uri = urlencode($config['redirect_uri']);
$state = bin2hex(random_bytes(16)); // สุ่ม State เพื่อป้องกัน CSRF
$scope = 'profile openid email'; // ขอบเขตการเข้าถึงข้อมูล

// สร้างลิงก์สำหรับ Login
$login_url = "https://access.line.me/oauth2/v2.1/authorize?response_type=code"
    . "&client_id=$client_id"
    . "&redirect_uri=$redirect_uri"
    . "&state=$state"
    . "&scope=$scope";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LINE Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .line-login-btn {
            background-color: #00c300;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            padding: 15px 20px;
            border-radius: 8px;
            display: inline-block;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .line-login-btn:hover {
            background-color: #009900;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center bg-success text-white">
                        <h3>Login with LINE</h3>
                    </div>
                    <div class="card-body text-center">
                        <p class="mb-4">Click the button below to log in with your LINE account.</p>
                        <a href="<?php echo $login_url; ?>" class="line-login-btn">
                            Login with LINE
                        </a>
                    </div>
                    <div class="card-footer text-center">
                        <p class="text-muted">Powered by LINE Login API</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>