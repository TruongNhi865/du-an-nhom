<?php
require_once __DIR__ . '/includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . url('index.php'));
    exit;
}

$error = '';
$redirect = trim($_GET['redirect'] ?? '');

// Ngăn open redirect: chỉ cho phép đường dẫn nội bộ (không chứa http://, //)
if ($redirect === '' || str_starts_with($redirect, 'http') || str_starts_with($redirect, '//')) {
    $redirect = '';
}

// ---- Chống brute-force: giới hạn số lần đăng nhập sai ----
$maxAttempts = 5;          // tối đa 5 lần sai
$lockoutTime = 900;        // khóa 15 phút
$now         = time();
$failedCount = (int)($_SESSION['login_failed'] ?? 0);
$lockedUntil = (int)($_SESSION['login_locked_until'] ?? 0);
$locked      = false;

if ($failedCount >= $maxAttempts && $now < $lockedUntil) {
    $locked = true;
    $error  = 'Quá nhiều lần đăng nhập thất bại. Vui lòng thử lại sau '
            . (int)ceil(($lockedUntil - $now) / 60) . ' phút.';
} elseif ($failedCount >= $maxAttempts && $now >= $lockedUntil) {
    // Hết thời gian khóa -> reset bộ đếm
    $_SESSION['login_failed'] = 0;
    unset($_SESSION['login_locked_until']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    if (!csrf_verify()) {
        $error = 'Phiên làm việc không hợp lệ, vui lòng thử lại.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = 'Vui lòng nhập email và mật khẩu.';
        } else {
            $stmt = $pdo->prepare("SELECT id, full_name, email, password, role, balance FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Đăng nhập thành công -> xóa bộ đếm thất bại
                $_SESSION['login_failed'] = 0;
                unset($_SESSION['login_locked_until']);

                session_regenerate_id(true);

                $_SESSION['user_id']    = (int)$user['id'];
                $_SESSION['user_name']  = $user['full_name'];
                $_SESSION['full_name']  = $user['full_name'];
                $_SESSION['email']      = $user['email'];
                $_SESSION['role']       = $user['role'];
                $_SESSION['balance']    = $user['balance'];

                if ($redirect !== '') {
                    header('Location: ' . url($redirect));
                } else {
                    header('Location: ' . url('index.php'));
                }
                exit;
            } else {
                // Đếm lỗi thất bại
                $_SESSION['login_failed'] = $failedCount + 1;
                if ($_SESSION['login_failed'] >= $maxAttempts) {
                    $_SESSION['login_locked_until'] = $now + $lockoutTime;
                    $error = 'Quá nhiều lần đăng nhập thất bại. Tài khoản bị khóa trong ' . (int)($lockoutTime / 60) . ' phút.';
                } else {
                    $error = 'Email hoặc mật khẩu không đúng.';
                }
            }
        }
    }
}

$pageTitle = 'Đăng nhập';
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-box">

        <div class="auth-badge"><i class="fa-solid fa-right-to-bracket"></i></div>
        <div class="auth-eyebrow">Chào mừng bạn trở lại</div>
        <h1 class="auth-title">Đăng nhập tài khoản</h1>
        <p class="auth-subtitle">Nhập thông tin để tiếp tục hành trình học tập.</p>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Đăng ký thành công. Hãy đăng nhập ngay.</div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="loginEmail">Email</label>
                <input type="email" id="loginEmail" name="email" class="form-control" placeholder="example@gmail.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="loginPass">Mật khẩu</label>
                <input type="password" id="loginPass" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;">
                <label class="form-check" style="margin-bottom:0;">
                    <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
                </label>
            </div>

            <button type="submit" class="btn btn-accent btn-block btn-lg">Đăng nhập</button>
        </form>

        <div class="auth-footer">
            Chưa có tài khoản? <a href="<?= url('register.php') ?>" style="color:var(--accent-dark);">Đăng ký ngay</a>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>