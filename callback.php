<?php
session_start();

// โหลดค่าการตั้งค่าจาก config.php
$config = require 'config.php';

// ตั้งค่าเซสชัน Timeout (5 นาที)
$session_timeout = 5 * 60;

// ฟังก์ชันเรียก API
function callAPI($url, $headers = [], $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    $response = curl_exec($ch);
    if ($response === false) {
        die('cURL error: ' . curl_error($ch));
    }

    curl_close($ch);
    return json_decode($response, true);
}

// ฟังก์ชันแลกเปลี่ยน Authorization Code เป็น Access Token
function getAccessToken($code, $config) {
    $token_url = 'https://api.line.me/oauth2/v2.1/token';
    $data = [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $config['redirect_uri'],
        'client_id' => $config['channel_id'],
        'client_secret' => $config['channel_secret'],
    ];

    return callAPI($token_url, [], http_build_query($data));
}

// ฟังก์ชันดึงข้อมูลผู้ใช้จาก LINE API
function getUserProfile($accessToken) {
    $user_info_url = 'https://api.line.me/v2/profile';
    $headers = [
        'Authorization: Bearer ' . $accessToken,
    ];

    return callAPI($user_info_url, $headers);
}

// รับค่าจาก URL หลังการ Redirect
$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;

// ตรวจสอบว่าได้รับ Authorization Code
if (!$code) {
    die('Authorization failed! No code received.');
}

// ตรวจสอบ CSRF ด้วย state
if (!isset($_SESSION['state']) || $_SESSION['state'] !== $state) {
    session_unset();
    session_destroy();
    die('Invalid state parameter. Possible CSRF detected.');
}

// ลบ state หลังจากตรวจสอบเสร็จ
unset($_SESSION['state']);

// แลกเปลี่ยน Authorization Code กับ Access Token
$response_data = getAccessToken($code, $config);

if (!isset($response_data['access_token'])) {
    die('Failed to get access token! Response: ' . json_encode($response_data));
}

$access_token = $response_data['access_token'];
$id_token = $response_data['id_token'] ?? null;

// ตรวจสอบและดึงข้อมูลจาก ID Token (สำหรับ Email)
$email = null;
if ($id_token) {
    $id_token_payload = explode('.', $id_token);
    if (count($id_token_payload) === 3) {
        $decoded_payload = json_decode(base64_decode($id_token_payload[1]), true);
        $email = $decoded_payload['email'] ?? 'N/A';
    }
}

// ดึงข้อมูลผู้ใช้จาก LINE Profile API
$user_data = getUserProfile($access_token);

if (!isset($user_data['userId'])) {
    die('Failed to get user profile! Response: ' . json_encode($user_data));
}

// บันทึกสถานะการล็อกอินในเซสชัน
$_SESSION['logged_in'] = true;
$_SESSION['login_time'] = time();
$_SESSION['display_name'] = htmlspecialchars($user_data['displayName']);
$_SESSION['user_id'] = htmlspecialchars($user_data['userId']);
$_SESSION['picture_url'] = htmlspecialchars($user_data['pictureUrl'] ?? '');
$_SESSION['email'] = $email;

// Redirect ไปหน้า dashboard.php หลังล็อกอินสำเร็จ
header('Location: dashboard.php');
exit;
