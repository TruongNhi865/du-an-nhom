<?php
$pageTitle = 'Khóa học của tôi';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';

if (!$isLoggedIn) {
    header('Location: ' . url('login.php?redirect=' . urlencode('user/my-courses.php')));
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT e.*, c.title, c.thumbnail, c.description, c.price, c.duration_days,
            cat.name AS category_name,
            (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lesson_count,
            e.enrolled_at, e.end_date
     FROM enrollments e
     JOIN courses c ON c.id = e.course_id
     LEFT JOIN categories cat ON cat.id = c.category_id
     WHERE e.user_id = ? AND e.status = 'active'
     ORDER BY e.enrolled_at DESC"
);
$stmt->execute([$userId]);
$myCourses = $stmt->fetchAll();
?>

<div class="my-courses-page">
    <div class="container">

        <div class="page-head">
            <span class="eyebrow">Học tập của tôi</span>
            <h1 class="page-title">Khóa học đã đăng ký</h1>
            <p class="page-sub">Quản lý và theo dõi tiến độ học tập của bạn.</p>
        </div>

        <?php if ($myCourses): ?>
            <?php foreach ($myCourses as $c):
                $hasThumb = trim((string)($c['thumbnail'] ?? ''));
                $thumbUrl = $hasThumb !== '' ? url($c['thumbnail']) : '';
                $detailUrl = url('course-detail.php?id=' . (int)$c['course_id']);
                $now = time();
                $end = strtotime($c['end_date']);
                $expired = $end < $now;
            ?>
                <div class="my-course-card">
                    <a href="<?= $detailUrl ?>">
                        <?php if ($thumbUrl !== ''): ?>
                            <img class="my-course-thumb" src="<?= e($thumbUrl) ?>" alt="<?= e($c['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="my-course-thumb" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1d2b54,#0d1428);color:var(--accent-soft);font-size:2rem;">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                        <?php endif; ?>
                    </a>
                    <div class="my-course-info">
                        <h3 class="my-course-title">
                            <a href="<?= $detailUrl ?>" style="transition:color .15s ease;"><?= e($c['title']) ?></a>
                        </h3>
                        <p class="my-course-instructor">
                            <?= e($c['category_name'] ?? 'Đa kỹ năng') ?> · <?= (int)$c['lesson_count'] ?> bài học
                            <?php if ($expired): ?>
                                <span style="color:var(--danger);font-weight:700;margin-left:8px;">Đã hết hạn</span>
                            <?php else: ?>
                                <span style="color:var(--success);font-weight:700;margin-left:8px;">Còn <?= max(1, (int)ceil(($end - $now) / 86400)) ?> ngày</span>
                            <?php endif; ?>
                        </p>
                        <?php if (!$expired): ?>
                            <a href="<?= url('user/learning.php') ?>" class="btn btn-accent btn-sm">
                                <i class="fa-solid fa-play"></i> Tiếp tục học
                            </a>
                        <?php else: ?>
                            <a href="<?= $detailUrl ?>" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-rotate-left"></i> Gia hạn
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-regular fa-folder-open"></i>
                <h3>Bạn chưa đăng ký khóa học nào</h3>
                <p>Hãy khám phá thư viện khóa học và bắt đầu hành trình học tập.</p>
                <a href="<?= url('courses.php') ?>" class="btn btn-accent"><i class="fa-solid fa-magnifying-glass"></i> Khám phá khóa học</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
