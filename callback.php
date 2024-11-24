<?php
// โหลดค่าการตั้งค่าจาก config.php
$config = require 'config.php';

// รับค่าจาก URL หลังการ Redirect
$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;

// ตรวจสอบว่ามี code ส่งกลับมาหรือไม่
if (!$code) {
    die('Authorization failed! No code received.');
}

// แลกเปลี่ยน Authorization Code กับ Access Token
$token_url = 'https://api.line.me/oauth2/v2.1/token';

$data = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $config['redirect_uri'],
    'client_id' => $config['channel_id'],
    'client_secret' => $config['channel_secret'],
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$response_data = json_decode($response, true);

if (!isset($response_data['access_token'])) {
    die('Failed to get access token! Response: ' . $response);
}

$access_token = $response_data['access_token'];
$id_token = $response_data['id_token'] ?? null;

// ตรวจสอบและดึงข้อมูลจาก ID Token (สำหรับ Email)
$email = null;
if ($id_token) {
    $id_token_payload = explode('.', $id_token);
    if (count($id_token_payload) === 3) {
        $decoded_payload = json_decode(base64_decode($id_token_payload[1]), true);
        $email = $decoded_payload['email'] ?? 'N/A'; // ดึง Email จาก Payload
    }
}

// ดึงข้อมูลผู้ใช้จาก LINE Profile API
$user_info_url = 'https://api.line.me/v2/profile';
$headers = [
    'Authorization: Bearer ' . $access_token,
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $user_info_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$user_response = curl_exec($ch);
curl_close($ch);

$user_data = json_decode($user_response, true);

// ตรวจสอบข้อมูลผู้ใช้
if (!isset($user_data['userId'])) {
    die('Failed to get user profile! Response: ' . $user_response);
}

// ข้อมูลผู้ใช้
$displayName = htmlspecialchars($user_data['displayName']);
$userId = htmlspecialchars($user_data['userId']);
$pictureUrl = htmlspecialchars($user_data['pictureUrl'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LINE Login - Callback</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 500px;">
            <div class="card-header text-center bg-success text-white">
                <h3>Welcome, <?php echo $displayName; ?>!</h3>
            </div>
            <div class="card-body text-center">
                <?php if ($pictureUrl): ?>
                    <img src="<?php echo $pictureUrl; ?>" alt="Profile Picture" class="img-thumbnail mb-3" style="max-width: 150px;">
                <?php endif; ?>
                <p><strong>LINE User ID:</strong> <?php echo $userId; ?></p>
                <p><strong>Email:</strong> <?php echo $email ? $email : 'Not provided'; ?></p>
                <p class="text-muted">Thank you for logging in with LINE.</p>
            </div>
            <div class="card-footer text-center">
                <a href="index.php" class="btn btn-primary">Back to Home</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
