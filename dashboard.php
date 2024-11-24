<?php
session_start();

// ตั้งค่า Timeout (15 นาที)
$session_timeout = 15 * 60; // 15 นาที = 900 วินาที

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['message'] = 'Please log in to access your dashboard.';
    header('Location: index.php');
    exit;
}

// ตรวจสอบ Timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    session_unset();
    session_destroy();
    $_SESSION['message'] = 'Your session has expired due to inactivity. Please log in again.';
    header('Location: index.php');
    exit;
}

// อัปเดตเวลาการใช้งานล่าสุด
$_SESSION['last_activity'] = time();

// ดึงข้อมูลผู้ใช้จากเซสชัน
$displayName = $_SESSION['display_name'] ?? 'Unknown';
$userId = $_SESSION['user_id'] ?? 'Unknown';
$pictureUrl = $_SESSION['picture_url'] ?? null;
$email = $_SESSION['email'] ?? 'Not provided';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-header text-center bg-primary text-white">
                <h3>Welcome to Your Dashboard</h3>
            </div>
            <div class="card-body text-center">
                <?php if ($pictureUrl): ?>
                    <img src="<?php echo $pictureUrl; ?>" alt="Profile Picture" class="img-thumbnail mb-3" style="max-width: 150px;">
                <?php endif; ?>
                <h4><?php echo htmlspecialchars($displayName); ?></h4>
                <p><strong>LINE User ID:</strong> <?php echo htmlspecialchars($userId); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            </div>
            <div class="card-footer text-center">
                <a href="logout.php" class="btn btn-danger">Log Out</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
