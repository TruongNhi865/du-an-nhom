<?php
require_once __DIR__ . '/includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . url('index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = trim($_POST['full_name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Kiểm tra CSRF
    if (!csrf_verify()) {
        $error = 'Phiên làm việc không hợp lệ, vui lòng thử lại.';
    } elseif ($full_name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif (mb_strlen($full_name) > 100) {
        $error = 'Họ tên không được vượt quá 100 ký tự.';
    } elseif (mb_strlen($email) > 150) {
        $error = 'Email không hợp lệ.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif (mb_strlen($password) < 6 || mb_strlen($password) > 72) {
        $error = 'Mật khẩu phải có từ 6 đến 72 ký tự.';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email này đã được đăng ký.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, balance) VALUES (?, ?, ?, 'student', 0)");
            $stmt->execute([$full_name, $email, $password_hash]);

            header('Location: ' . url('login.php?registered=1'));
            exit;
        }
    }
}

$pageTitle = 'Đăng ký';
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-box">

        <div class="auth-badge"><i class="fa-solid fa-user-plus"></i></div>
        <div class="auth-eyebrow">Tạo tài khoản mới</div>
        <h1 class="auth-title">Đăng ký tài khoản</h1>
        <p class="auth-subtitle">Bắt đầu hành trình học tập chỉ với vài thao tác.</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="regName">Họ và tên</label>
                <input type="text" id="regName" name="full_name" class="form-control" placeholder="Nguyễn Văn A" maxlength="100" required autofocus>
            </div>

            <div class="form-group">
                <label for="regEmail">Email</label>
                <input type="email" id="regEmail" name="email" class="form-control" placeholder="example@gmail.com" maxlength="150" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="regPass">Mật khẩu</label>
                    <input type="password" id="regPass" name="password" class="form-control" placeholder="Ít nhất 6 ký tự" maxlength="72" required>
                </div>
                <div class="form-group">
                    <label for="regConfirm">Nhập lại mật khẩu</label>
                    <input type="password" id="regConfirm" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" maxlength="72" required>
                </div>
            </div>

            <label class="form-check">
                <input type="checkbox" required>
                Tôi đồng ý với <a href="#" style="color:var(--accent-dark);font-weight:700;">điều khoản sử dụng</a>
            </label>

            <button type="submit" class="btn btn-accent btn-block btn-lg">Đăng ký</button>
        </form>

        <div class="auth-footer">
            Đã có tài khoản? <a href="<?= url('login.php') ?>" style="color:var(--accent-dark);">Đăng nhập</a>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>