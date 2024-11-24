<?php
session_start();

// ตรวจสอบว่ามีข้อความแจ้งเตือนหรือไม่
$message = $_SESSION['message'] ?? null;
if ($message) {
    unset($_SESSION['message']); // ลบข้อความหลังจากแสดงผล
}

// โหลดค่าการตั้งค่าจาก config.php
$config = require 'config.php';

// สร้าง State เพื่อป้องกัน CSRF
$state = bin2hex(random_bytes(16)); // สุ่ม State
$_SESSION['state'] = $state; // บันทึก State ในเซสชัน

// สร้าง URL สำหรับ Login ด้วย LINE
$client_id = $config['channel_id'];
$redirect_uri = urlencode($config['redirect_uri']);
$scope = 'profile openid email'; // ขอบเขตการเข้าถึงข้อมูล
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

    <!-- Custom CSS -->
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

        .card-header {
            background-color: #00c300;
            color: white;
        }

        .card-footer {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <!-- แจ้งเตือนหากมีข้อความ -->
        <?php if ($message): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header text-center">
                        <h3>Welcome to LINE Login</h3>
                    </div>
                    <div class="card-body text-center">
                        <p class="mb-4">Click the button below to log in with your LINE account.</p>
                        <a href="<?php echo $login_url; ?>" class="line-login-btn">
                            Login with LINE
                        </a>
                    </div>
                    <div class="card-footer text-center">
                        <p class="text-muted mb-0">Powered by LINE Login API</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
