<?php
$pageTitle = 'Khóa học';
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';

$q    = trim($_GET['q'] ?? '');
$cat  = (int)($_GET['category'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));

$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

$where  = ["c.status = 'published'"];
$params = [];
if ($q !== '') {
    $where[]  = "(c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($cat > 0) {
    $where[]  = "c.category_id = ?";
    $params[] = $cat;
}
$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM courses c WHERE $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$perPage = 6;
$pages   = max(1, (int)ceil($total / $perPage));
if ($page > $pages) {
    $page = $pages;
}
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    "SELECT c.*, cat.name AS category_name,
            (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lesson_count
     FROM courses c
     LEFT JOIN categories cat ON cat.id = c.category_id
     WHERE $whereSql
     ORDER BY c.created_at DESC, c.id DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$courses = $stmt->fetchAll();

$filterQuery = [];
if ($q !== '') {
    $filterQuery['q'] = $q;
}
if ($cat > 0) {
    $filterQuery['category'] = $cat;
}
$qs = http_build_query($filterQuery);
?>

<div class="courses-page">
    <div class="container">

        <div class="page-head">
            <span class="eyebrow">Thư viện kiến thức</span>
            <h1 class="page-title">Khóa học nổi bật</h1>
            <p class="page-sub">Chọn đúng khóa học, tiến xa mỗi ngày cùng EduHub.</p>
        </div>

        <form class="filter-bar" method="get">
            <div class="search-field">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" value="<?= e($q) ?>" placeholder="Tìm kiếm khóa học...">
            </div>
            <div class="select-field">
                <select name="category">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <button type="submit" class="btn btn-primary">Lọc</button>
        </form>

        <p class="result-count">Đang hiển thị <strong><?= $total ?></strong> khóa học</p>

        <?php if ($courses): ?>
            <div class="course-grid">
                <?php foreach ($courses as $c):
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
                                <span><i class="fa-regular fa-clock"></i> <?= (int)$c['duration_days'] ?> ngày truy cập</span>
                            </div>
                            <div class="course-footer">
                                <span class="course-price <?= $free ? 'free' : '' ?>"><?= priceLabel($c['price']) ?></span>
                                <a href="<?= $detailUrl ?>" class="btn btn-outline btn-sm">Xem chi tiết</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($pages > 1): ?>
                <nav class="pagination" aria-label="Phân trang">
                    <a class="page-btn" <?= $page > 1 ? 'href="' . e(url('courses.php?' . $qs . ($qs !== '' ? '&' : '') . 'page=' . ($page - 1))) . '"' : 'disabled' ?>>
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a class="page-btn <?= $i === $page ? 'active' : '' ?>" href="<?= e(url('courses.php?' . $qs . ($qs !== '' ? '&' : '') . 'page=' . $i)) ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <a class="page-btn" <?= $page < $pages ? 'href="' . e(url('courses.php?' . $qs . ($qs !== '' ? '&' : '') . 'page=' . ($page + 1))) . '"' : 'disabled' ?>>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <i class="fa-regular fa-folder-open"></i>
                <h3>Không tìm thấy khóa học nào</h3>
                <p>Hãy thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc.</p>
                <a href="<?= url('courses.php') ?>" class="btn btn-outline">Xem tất cả khóa học</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>