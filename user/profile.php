<?php
$pageTitle = 'Hồ sơ của tôi';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';

if (!$isLoggedIn) {
    header('Location: ' . url('login.php?redirect=' . urlencode('user/profile.php')));
    exit;
}

$userId = (int)$_SESSION['user_id'];
$success = '';
$error   = '';

$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$userStmt->execute([$userId]);
$userData = $userStmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $newPass   = $_POST['new_password'] ?? '';
    $curPass   = $_POST['current_password'] ?? '';

    if (!csrf_verify()) {
        $error = 'Phiên làm việc không hợp lệ, vui lòng thử lại.';
    } elseif ($full_name === '' || $email === '') {
        $error = 'Họ tên và email không được để trống.';
    } elseif (mb_strlen($full_name) > 100) {
        $error = 'Họ tên không được vượt quá 100 ký tự.';
    } elseif (mb_strlen($email) > 150) {
        $error = 'Email không hợp lệ.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif ($newPass !== '' && (mb_strlen($newPass) < 6 || mb_strlen($newPass) > 72)) {
        $error = 'Mật khẩu mới phải có từ 6 đến 72 ký tự.';
    } elseif ($curPass === '' && $newPass !== '') {
        $error = 'Vui lòng nhập mật khẩu hiện tại để đổi mật khẩu.';
    } elseif ($curPass !== '' && !password_verify($curPass, $userData['password'])) {
        $error = 'Mật khẩu hiện tại không đúng.';
    } else {
        if ($email !== $userData['email']) {
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
            $chk->execute([$email, $userId]);
            if ($chk->fetch()) {
                $error = 'Email này đã được sử dụng bởi tài khoản khác.';
            }
        }

        if ($error === '') {
            $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?")
                ->execute([$full_name, $email, $userId]);

            if ($newPass !== '') {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
                    ->execute([$hash, $userId]);
            }

            $_SESSION['user_name'] = $full_name;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email']     = $email;
            $userData['full_name'] = $full_name;
            $userData['email']     = $email;
            $success = 'Cập nhật hồ sơ thành công!';
        }
    }
}

$enrolledCountStmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND status = 'active'");
$enrolledCountStmt->execute([$userId]);
$enrolledCount = (int)$enrolledCountStmt->fetchColumn();

$balanceStmt = $pdo->prepare("SELECT COALESCE(balance, 0) FROM users WHERE id = ?");
$balanceStmt->execute([$userId]);
$userBalance = (float)$balanceStmt->fetchColumn();
?>

<div class="profile-page">
    <div class="container">

        <div class="profile-header">
            <div class="profile-avatar">
                <?= e(mb_substr(trim($userData['full_name'] ?? ''), 0, 1)) ?>
            </div>
            <div>
                <h2 class="profile-name"><?= e($userData['full_name'] ?? '') ?></h2>
                <div class="profile-role">Học viên</div>
                <div class="profile-email"><?= e($userData['email'] ?? '') ?></div>
            </div>
            <div class="profile-stats">
                <div class="profile-stat">
                    <strong><?= $enrolledCount ?></strong>
                    <span>Khóa học</span>
                </div>
                <div class="profile-stat">
                    <strong><?= money($userBalance) ?></strong>
                    <span>Số dư</span>
                </div>
            </div>
        </div>

        <div class="profile-grid">
            <div>
                <div class="profile-section">
                    <h3>Thông tin cá nhân</h3>

                    <?php if ($success !== ''): ?>
                        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label for="profName">Họ và tên</label>
                            <input type="text" id="profName" name="full_name" class="form-control" value="<?= e($userData['full_name'] ?? '') ?>" maxlength="100" required>
                        </div>
                        <div class="form-group">
                            <label for="profEmail">Email</label>
                            <input type="email" id="profEmail" name="email" class="form-control" value="<?= e($userData['email'] ?? '') ?>" maxlength="150" required>
                        </div>

                        <h3 style="margin-top:28px;">Đổi mật khẩu <small style="color:#8a94a8;font-size:.85rem;">(để trống nếu không đổi)</small></h3>
                        <div class="form-group">
                            <label for="profCurPass">Mật khẩu hiện tại</label>
                            <input type="password" id="profCurPass" name="current_password" class="form-control" placeholder="Nhập mật khẩu hiện tại" maxlength="72">
                        </div>
                        <div class="form-group">
                            <label for="profNewPass">Mật khẩu mới</label>
                            <input type="password" id="profNewPass" name="new_password" class="form-control" placeholder="Ít nhất 6 ký tự" maxlength="72">
                        </div>

                        <button type="submit" class="btn btn-accent"><i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi</button>
                    </form>
                </div>
            </div>

            <div>
                <div class="profile-section">
                    <h3>Hành động nhanh</h3>
                    <a href="<?= url('user/my-courses.php') ?>" class="btn btn-outline btn-block" style="margin-bottom:10px;">
                        <i class="fa-solid fa-book-open"></i> Khóa học của tôi
                    </a>
                    <a href="<?= url('user/learning.php') ?>" class="btn btn-outline btn-block" style="margin-bottom:10px;">
                        <i class="fa-solid fa-graduation-cap"></i> Tiếp tục học
                    </a>
                    <a href="<?= url('courses.php') ?>" class="btn btn-accent btn-block">
                        <i class="fa-solid fa-magnifying-glass"></i> Khám phá khóa học
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
