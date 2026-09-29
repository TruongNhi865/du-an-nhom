<?php
$pageTitle = 'Chi tiết khóa học';
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);

$courseStmt = $pdo->prepare(
    "SELECT c.*, cat.name AS category_name, cat.slug AS category_slug,
            (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lesson_count
     FROM courses c
     LEFT JOIN categories cat ON cat.id = c.category_id
     WHERE c.id = ? AND c.status = 'published'
     LIMIT 1"
);
$courseStmt->execute([$id]);
$course = $courseStmt->fetch();

if (!$course) {
    ?>
    <div class="container" style="padding:80px 24px;text-align:center;">
        <i class="fa-regular fa-circle-question" style="font-size:3rem;color:#cad1dd;margin-bottom:16px;display:block;"></i>
        <h1>Không tìm thấy khóa học</h1>
        <p style="color:#8a94a8;margin-bottom:24px;">Khóa học không tồn tại hoặc đã bị ẩn.</p>
        <a href="<?= url('courses.php') ?>" class="btn btn-primary">Quay lại danh sách</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$lessonStmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY lesson_order ASC, id ASC");
$lessonStmt->execute([$id]);
$lessons = $lessonStmt->fetchAll();

$enrolled = false;
if ($isLoggedIn) {
    $enStmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'active' LIMIT 1");
    $enStmt->execute([(int)$_SESSION['user_id'], $id]);
    $enrolled = (bool)$enStmt->fetchColumn();
}

$flash = ['type' => null, 'text' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    if (!csrf_verify()) {
        $flash = ['type' => 'error', 'text' => 'Phiên làm việc không hợp lệ, vui lòng thử lại.'];
    } elseif (!$isLoggedIn) {
        header('Location: ' . url('login.php?redirect=' . urlencode('course-detail.php?id=' . $id)));
        exit;
    } elseif ($enrolled) {
        $flash = ['type' => 'success', 'text' => 'Bạn đã đăng ký khóa học này rồi.'];
    } else {
        $userId = (int)$_SESSION['user_id'];
        $price  = (float)$course['price'];

        // Đọc số dư mới nhất từ DB (không tin dữ liệu trong session)
        $balStmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? LIMIT 1");
        $balStmt->execute([$userId]);
        $balance = (float)$balStmt->fetchColumn();

        if ($balance < $price) {
            $flash = [
                'type' => 'error',
                'text' => 'Số dư không đủ. Bạn cần nạp thêm ' . money($price - $balance) . '.',
            ];
        } else {
            try {
                $pdo->beginTransaction();

                $now  = date('Y-m-d H:i:s');
                $end  = date('Y-m-d H:i:s', strtotime('+' . (int)$course['duration_days'] . ' days'));

                $upd = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                $upd->execute([$price, $userId]);

                $en = $pdo->prepare(
                    "INSERT INTO enrollments (user_id, course_id, enrolled_at, start_date, end_date, status)
                     VALUES (?, ?, ?, ?, ?, 'active')"
                );
                $en->execute([$userId, $id, $now, $now, $end]);

                $tx = $pdo->prepare(
                    "INSERT INTO transactions (user_id, type, amount, description)
                     VALUES (?, 'purchase', ?, ?)"
                );
                $tx->execute([$userId, $price, 'Mua khóa học: ' . $course['title']]);

                $pdo->commit();

                $_SESSION['balance'] = $balance - $price;
                $enrolled = true;
                $flash = ['type' => 'success', 'text' => 'Đăng ký thành công! Chúc bạn học vui vẻ.'];
            } catch (Exception $ex) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $flash = ['type' => 'error', 'text' => 'Có lỗi xảy ra, vui lòng thử lại.'];
            }
        }
    }
}

$hasThumb  = trim((string)($course['thumbnail'] ?? ''));
$thumbUrl  = $hasThumb !== '' ? url($course['thumbnail']) : '';
$free      = (float)$course['price'] <= 0;
$isFirst   = true;
?>

<div class="detail-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Đường dẫn">
            <a href="<?= url('index.php') ?>">Trang chủ</a>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="<?= url('courses.php') ?>">Khóa học</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span class="current"><?= e($course['title']) ?></span>
        </nav>

        <div class="detail-hero-meta">
            <span class="chip chip-chip"><i class="fa-solid fa-layer-group"></i> <?= e($course['category_name'] ?? 'Đa kỹ năng') ?></span>
            <span class="chip"><i class="fa-regular fa-file-lines"></i> <?= (int)$course['lesson_count'] ?> bài học</span>
            <span class="chip"><i class="fa-regular fa-clock"></i> Truy cập <?= (int)$course['duration_days'] ?> ngày</span>
            <?php if ($free): ?>
                <span class="chip"><i class="fa-solid fa-gift"></i> Miễn phí</span>
            <?php endif; ?>
        </div>

        <h1><?= e($course['title']) ?></h1>
        <p class="detail-hero-desc"><?= e(mb_strimwidth((string)$course['description'], 0, 240, '…')) ?></p>
    </div>
</div>

<div class="container detail-layout">
    <div class="detail-main">

        <div class="detail-media <?= $thumbUrl === '' ? 'detail-media--ph' : '' ?>">
            <?php if ($thumbUrl !== ''): ?>
                <img src="<?= e($thumbUrl) ?>" alt="<?= e($course['title']) ?>">
            <?php else: ?>
                <i class="fa-solid fa-graduation-cap"></i>
            <?php endif; ?>
        </div>

        <div class="highlight-box">
            <h3><i class="fa-solid fa-star-of-life"></i> Bạn sẽ học được</h3>
            <ul class="highlight-grid">
                <li><i class="fa-solid fa-circle-check"></i> Kiến thức nền tảng chuyên sâu, thực chiến</li>
                <li><i class="fa-solid fa-circle-check"></i> <?= (int)$course['lesson_count'] ?> bài học được thiết kế lộ trình</li>
                <li><i class="fa-solid fa-circle-check"></i> Thực hành theo dự án thực tế</li>
                <li><i class="fa-solid fa-circle-check"></i> Hỗ trợ giải đáp từ giảng viên</li>
            </ul>
        </div>

        <section class="detail-section">
            <h2>Giới thiệu khóa học</h2>
            <p><?= e((string)$course['description']) ?></p>
        </section>

        <section class="detail-section">
            <h2>Nội dung chương trình <small style="color:#8a94a8;font-size:.9rem;">(<?= count($lessons) ?> bài học)</small></h2>
            <?php if ($lessons): ?>
                <div class="lesson-list <?= $enrolled ? 'enrolled' : '' ?>">
                    <?php foreach ($lessons as $lesson): ?>
                        <div class="lesson-item <?= $isFirst ? 'is-open' : '' ?>">
                            <span class="lesson-num"><?= $isFirst ? '<i class="fa-solid fa-play"></i>' : (int)$lesson['lesson_order'] ?></span>
                            <div class="lesson-info">
                                <div class="lesson-title"><?= e($lesson['title']) ?></div>
                                <div class="lesson-duration">
                                    <?php if ($lesson['video_url']): ?>
                                        <i class="fa-solid fa-video"></i> Video bài giảng
                                    <?php elseif ($lesson['file_path']): ?>
                                        <i class="fa-solid fa-file"></i> Tài liệu bài học
                                    <?php else: ?>
                                        <i class="fa-regular fa-file-lines"></i> Bài học lý thuyết
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="lesson-state">
                                <?php if ($lesson['video_url']): ?>
                                    <i class="fa-solid fa-circle-play"></i>
                                <?php else: ?>
                                    <i class="fa-regular fa-eye"></i>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color:#8a94a8;">Khóa học đang được cập nhật nội dung.</p>
            <?php endif; ?>
        </section>

    </div>

    <aside class="detail-side">
        <div class="side-box">

            <?php if ($flash['type']): ?>
                <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
                    <?= e($flash['text']) ?>
                </div>
            <?php endif; ?>

            <div class="price-wrap">
                <span class="price-now <?= $free ? 'free' : '' ?>"><?= priceLabel($course['price']) ?></span>
                <?php if (!$free): ?>
                    <span class="price-old"><?= money($course['price'] * 1.35) ?></span>
                <?php endif; ?>
            </div>

            <?php if (!$free): ?>
                <span class="side-save">Tiết kiệm 35% dịp khai giảng</span>
            <?php endif; ?>

            <?php if ($enrolled): ?>
                <div class="enrolled-box"><i class="fa-solid fa-circle-check"></i> Bạn đã đăng ký khóa học này</div>
                <a href="<?= url('user/learning.php') ?>" class="btn btn-accent btn-block btn-lg">
                    <i class="fa-solid fa-play"></i> Học ngay
                </a>
            <?php elseif ($isLoggedIn): ?>
                <div class="balance-note">
                    <span>Số dư tài khoản</span>
                    <strong><?= money($_SESSION['balance'] ?? 0) ?></strong>
                </div>
                <form method="post">
                    <?= csrf_field() ?>
                    <button type="submit" name="enroll" value="1" class="btn btn-accent btn-block btn-lg">
                        <i class="fa-solid fa-cart-shopping"></i> Đăng ký ngay
                    </button>
                </form>
            <?php else: ?>
                <a href="<?= url('login.php?redirect=' . urlencode('course-detail.php?id=' . $id)) ?>" class="btn btn-accent btn-block btn-lg">
                    <i class="fa-solid fa-user-plus"></i> Đăng nhập để đăng ký
                </a>
            <?php endif; ?>

            <ul class="include-list">
                <li><i class="fa-solid fa-video"></i> <?= (int)$course['lesson_count'] ?> bài giảng chi tiết</li>
                <li><i class="fa-regular fa-clock"></i> Quyền truy cập <?= (int)$course['duration_days'] ?> ngày</li>
                <li><i class="fa-solid fa-mobile-screen"></i> Học trên mọi thiết bị</li>
                <li><i class="fa-solid fa-headset"></i> Hỗ trợ trực tiếp trên lớp</li>
                <li><i class="fa-solid fa-certificate"></i> Chứng chỉ hoàn thành</li>
            </ul>

            <div class="side-note">
                <span><i class="fa-solid fa-rotate-left"></i> Hoàn tiền 7 ngày</span>
                <span><i class="fa-solid fa-lock"></i> Thanh toán an toàn</span>
            </div>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>