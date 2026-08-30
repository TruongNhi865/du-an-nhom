<?php
session_start();
require_once "includes/db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Vui lòng nhập email và mật khẩu.";
    } else {
        $stmt = $pdo->prepare("SELECT id, full_name, email, password, role, balance FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["balance"] = $user["balance"];

            header("Location: index.php");
            exit;
        } else {
            $error = "Email hoặc mật khẩu không đúng.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Secure Course</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-container">
        <a href="index.php" class="logo">Secure Course</a>
        <nav class="main-nav">
            <a href="index.php">Trang chủ</a>
            <a href="courses.php">Khóa học</a>
            <a href="register.php">Đăng ký</a>
        </nav>
    </div>
</header>

<main class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-icon"></div>
            <h1>Đăng nhập</h1>
            <p class="auth-description">Chào mừng bạn quay trở lại.</p>

            <?php if (isset($_GET["registered"])): ?>
                <div class="alert alert-success">
                    Đăng ký thành công. Hãy đăng nhập.
                </div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="example@gmail.com" required>
                </div>

                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" placeholder="Nhập mật khẩu" required>
                </div>

                <button type="submit" class="auth-button">Đăng nhập</button>
            </form>

            <div class="auth-footer">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </div>
        </div>
    </div>
</main>

</body>
</html>