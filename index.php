<?php
$pageTitle = 'Trang chủ';
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';

$featuredCats = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name LIMIT 6")->fetchAll();

$featuredCourses = $pdo->query(
    "SELECT c.*, cat.name AS category_name,
            (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lesson_count
     FROM courses c
     LEFT JOIN categories cat ON cat.id = c.category_id
     WHERE c.status = 'published'
     ORDER BY c.created_at DESC
     LIMIT 6"
)->fetchAll();

$totalCourses   = (int)$pdo->query("SELECT COUNT(*) FROM courses WHERE status='published'")->fetchColumn();
$totalStudents  = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalLessons   = (int)$pdo->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
?>

<!-- ============ HERO ============ -->
<section class="hero">
    <div class="container hero-inner">
        <div class="hero-eyebrow">NỀN TẢNG HỌC TRỰC TUYẾN</div>
        <h1>Nâng cao kỹ năng mỗi ngày cùng <em>EduHub</em></h1>
        <p class="hero-lead">Khám phá hàng trăm khóa học chất lượng cao — từ lập trình, an toàn thông tin đến mạng máy tính. Học theo lộ trình, thực chiến dự án thật.</p>

        <form class="hero-search" action="<?= url('courses.php') ?>" method="get">
            <input type="text" name="q" placeholder="Bạn muốn học gì hôm nay?">
            <button type="submit" class="btn btn-accent"><i class="fa-solid fa-magnifying-glass"></i> Tìm khóa học</button>
        </form>

        <div class="hero-stats">
            <div class="hero-stat">
                <strong><?= number_format($totalCourses) ?>+</strong>
                <span>Khóa học</span>
            </div>
            <div class="hero-stat">
                <strong><?= number_format($totalStudents) ?>+</strong>
                <span>Học viên</span>
            </div>
            <div class="hero-stat">
                <strong><?= number_format($totalLessons) ?>+</strong>
                <span>Bài học</span>
            </div>
        </div>
    </div>
</section>

<!-- ============ DANH MỤC ============ -->
<?php if ($featuredCats): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Danh mục nổi bật</span>
                <h2 class="section-title">Chủ đề học tập</h2>
                <p class="section-sub">Lĩnh vực hot nhất, được yêu thích nhất trên EduHub.</p>
            </div>
        </div>
        <div class="cats-grid">
            <?php
            $catIcons = [
                'Lập trình'    => 'fa-solid fa-code',
                'An ninh mạng' => 'fa-solid fa-shield-halved',
                'Mạng máy tính'=> 'fa-solid fa-network-wired',
                'Thiết kế'     => 'fa-solid fa-palette',
                'Marketing'    => 'fa-solid fa-bullhorn',
                'Kinh doanh'   => 'fa-solid fa-chart-line',
            ];
            foreach ($featuredCats as $cat):
                $icon = $catIcons[$cat['name']] ?? 'fa-solid fa-book';
            ?>
                <a href="<?= url('courses.php?category=' . (int)$cat['id']) ?>" class="cat-card">
                    <div class="cat-icon"><i class="<?= $icon ?>"></i></div>
                    <div>
                        <h3><?= e($cat['name']) ?></h3>
                        <p>Xem khóa học →</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ KHÓA HỌC NỔI BẬT ============ -->
<?php if ($featuredCourses): ?>
<section class="section" style="background:var(--surface);">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Được yêu thích nhất</span>
                <h2 class="section-title">Khóa học nổi bật</h2>
                <p class="section-sub">Lộ trình học bài bản, thực chiến.</p>
            </div>
            <a href="<?= url('courses.php') ?>" class="section-link">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="course-grid">
            <?php foreach ($featuredCourses as $c):
                $hasThumb  = trim((string)($c['thumbnail'] ?? ''));
                $thumbUrl  = $hasThumb !== '' ? url($c['thumbnail']) : '';
                $detailUrl = url('course-detail.php?id=' . (int)$c['id']);
                $free      = (float)$c['price'] <= 0;
            ?>
                <article class="course-card">
                    <a href="<?= $detailUrl ?>" class="course-media">
                        <?php if ($thumbUrl !== ''): ?>
                            <img src="<?= e($thumbUrl) ?>" alt="<?= e($c['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="course-media--ph"><i class="fa-solid fa-graduation-cap"></i></div>
                        <?php endif; ?>
                        <span class="course-badge"><?= $free ? 'Miễn phí' : 'Học ngay' ?></span>
                    </a>
                    <div class="course-body">
                        <span class="course-category"><?= e($c['category_name'] ?? 'Đa kỹ năng') ?></span>
                        <h3 class="course-title"><a href="<?= $detailUrl ?>"><?= e($c['title']) ?></a></h3>
                        <p class="course-desc"><?= e(mb_strimwidth((string)($c['description'] ?? ''), 0, 110, '…')) ?></p>
                        <div class="course-meta">
                            <span><i class="fa-regular fa-file-lines"></i> <?= (int)$c['lesson_count'] ?> bài học</span>
                            <span><i class="fa-regular fa-clock"></i> <?= (int)$c['duration_days'] ?> ngày</span>
                        </div>
                        <div class="course-footer">
                            <span class="course-price <?= $free ? 'free' : '' ?>"><?= priceLabel($c['price']) ?></span>
                            <a href="<?= $detailUrl ?>" class="btn btn-outline btn-sm">Xem chi tiết</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ CTA ============ -->
<section class="cta-band">
    <div class="container">
        <h2>Bắt đầu hành trình học tập ngay hôm nay</h2>
        <p>Tham gia EduHub để trải nghiệm nền tảng học tập hiện đại, nội dung chất lượng và cộng đồng hỗ trợ tận tâm.</p>
        <div class="cta-buttons">
            <?php if ($isLoggedIn): ?>
                <a href="<?= url('courses.php') ?>" class="btn btn-accent btn-lg"><i class="fa-solid fa-rocket"></i> Khám phá khóa học</a>
            <?php else: ?>
                <a href="<?= url('register.php') ?>" class="btn btn-accent btn-lg"><i class="fa-solid fa-user-plus"></i> Đăng ký miễn phí</a>
                <a href="<?= url('courses.php') ?>" class="btn btn-outline btn-lg" style="color:#fff;border-color:rgba(255,255,255,0.3);"><i class="fa-solid fa-graduation-cap"></i> Xem khóa học</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>