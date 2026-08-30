<?php
session_start();
require_once "includes/db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($full_name === "" || $email === "" || $password === "" || $confirm_password === "") {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ.";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Email này đã được đăng ký.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, balance) VALUES (?, ?, ?, 'student', 0)");
            $stmt->execute([$full_name, $email, $password_hash]);

            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Secure Course</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-container">
        <a href="index.php" class="logo">Secure Course</a>
        <nav class="main-nav">
            <a href="index.php">Trang chủ</a>
            <a href="courses.php">Khóa học</a>
            <a href="login.php">Đăng nhập</a>
        </nav>
    </div>
</header>

<main class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-icon"></div>
            <h1>Tạo tài khoản</h1>
            <p class="auth-description">Đăng ký để bắt đầu học tập.</p>

            <?php if ($error !== ""): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
                <br>
                <a href="login.php" class="auth-button">Đăng nhập ngay</a>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="full_name" placeholder="Nguyễn Văn A" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="example@gmail.com" required>
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <input type="password" name="password" placeholder="Ít nhất 6 ký tự" required>
                    </div>

                    <div class="form-group">
                        <label>Nhập lại mật khẩu</label>
                        <input type="password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="auth-button">Đăng ký</button>
                </form>
            <?php endif; ?>

            <div class="auth-footer">
                Đã có tài khoản? <a href="login.php">Đăng nhập</a>
            </div>
        </div>
    </div>
</main>

</body>
</html>