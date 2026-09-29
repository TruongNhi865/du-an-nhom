<?php
$pageTitle = 'Học tập';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';

if (!$isLoggedIn) {
    header('Location: ' . url('login.php?redirect=' . urlencode('user/learning.php')));
    exit;
}

$userId = (int)$_SESSION['user_id'];
$courseId  = (int)($_GET['course_id'] ?? 0);
$lessonId  = (int)($_GET['lesson_id'] ?? 0);

$enrollStmt = $pdo->prepare(
    "SELECT e.*, c.title AS course_title
     FROM enrollments e
     JOIN courses c ON c.id = e.course_id
     WHERE e.user_id = ? AND e.status = 'active'
     ORDER BY e.enrolled_at DESC"
);
$enrollStmt->execute([$userId]);
$enrolledCourses = $enrollStmt->fetchAll();

if (!$enrolledCourses) {
    ?>
    <div class="container" style="padding:80px 24px;text-align:center;">
        <i class="fa-regular fa-folder-open" style="font-size:3rem;color:#cad1dd;margin-bottom:16px;display:block;"></i>
        <h1>Chưa có khóa học nào</h1>
        <p style="color:#8a94a8;margin-bottom:24px;">Bạn chưa đăng ký khóa học nào. Hãy khám phá thư viện khóa học để bắt đầu.</p>
        <a href="<?= url('courses.php') ?>" class="btn btn-accent"><i class="fa-solid fa-magnifying-glass"></i> Khám phá khóa học</a>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

if ($courseId === 0) {
    $courseId = (int)$enrolledCourses[0]['course_id'];
}

$courseTitle = '';
$currentLesson = null;

$lessonStmt = $pdo->prepare(
    "SELECT l.*, c.title AS course_title
     FROM lessons l
     JOIN courses c ON c.id = l.course_id
     WHERE l.course_id = ?
     ORDER BY l.lesson_order ASC, l.id ASC"
);
$lessonStmt->execute([$courseId]);
$lessonsAll = $lessonStmt->fetchAll();
$courseTitle = $lessonsAll ? $lessonsAll[0]['course_title'] : '';

if ($lessonId > 0) {
    foreach ($lessonsAll as $l) {
        if ((int)$l['id'] === $lessonId) {
            $currentLesson = $l;
            break;
        }
    }
}
if (!$currentLesson && $lessonsAll) {
    $currentLesson = $lessonsAll[0];
}
?>

<div class="learning-layout">
    <div class="learning-content">
        <?php if ($currentLesson): ?>
            <?php
            // Kiểm tra & bảo vệ URL video: chỉ chấp nhận YouTube hoặc http/https
            $rawVideo = trim((string)($currentLesson['video_url'] ?? ''));
            $embedUrl = '';
            if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})#', $rawVideo, $m)) {
                $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
            } elseif ($rawVideo !== '') {
                $parts = parse_url($rawVideo);
                if ($parts && isset($parts['scheme']) && in_array(strtolower($parts['scheme']), ['http', 'https'])) {
                    $embedUrl = $rawVideo;
                }
            }
            ?>
            <?php if ($embedUrl !== ''): ?>
                <div class="video-wrapper">
                    <iframe src="<?= e($embedUrl) ?>" allowfullscreen></iframe>
                </div>
            <?php elseif ($currentLesson['file_path']): ?>
                <div class="video-wrapper" style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px;">
                    <i class="fa-solid fa-file-pdf" style="font-size:3rem;color:var(--accent);"></i>
                    <a href="<?= e(url($currentLesson['file_path'])) ?>" class="btn btn-accent" target="_blank" rel="noopener"><i class="fa-solid fa-download"></i> Tải tài liệu bài học</a>
                </div>
            <?php else: ?>
                <div class="video-wrapper" style="display:flex;align-items:center;justify-content:center;">
                    <div style="text-align:center;">
                        <i class="fa-regular fa-file-lines" style="font-size:3rem;color:var(--ink-faint);margin-bottom:12px;display:block;"></i>
                        <p style="color:var(--ink-faint);">Bài học lý thuyết — xem nội dung bên dưới.</p>
                    </div>
                </div>
            <?php endif; ?>

            <h1 class="lesson-heading"><?= e($currentLesson['title']) ?></h1>
            <p class="lesson-course-name"><?= e($courseTitle) ?></p>

            <?php if ($currentLesson['content']): ?>
                <div class="detail-section" style="border-top:none;margin-top:0;padding-top:0;">
                    <h2>Nội dung bài học</h2>
                    <p><?= nl2br(e($currentLesson['content'])) ?></p>
                </div>
            <?php endif; ?>

            <div class="lesson-nav-actions">
                <?php
                $currentIndex = array_search($currentLesson, $lessonsAll);
                $prevLesson = $currentIndex > 0 ? $lessonsAll[$currentIndex - 1] : null;
                $nextLesson = $currentIndex < count($lessonsAll) - 1 ? $lessonsAll[$currentIndex + 1] : null;
                ?>
                <?php if ($prevLesson): ?>
                    <a href="<?= url('user/learning.php?course_id=' . $courseId . '&lesson_id=' . (int)$prevLesson['id']) ?>" class="btn btn-outline">
                        <i class="fa-solid fa-chevron-left"></i> Bài trước
                    </a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
                <?php if ($nextLesson): ?>
                    <a href="<?= url('user/learning.php?course_id=' . $courseId . '&lesson_id=' . (int)$nextLesson['id']) ?>" class="btn btn-accent">
                        Bài tiếp theo <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:60px 0;">
                <i class="fa-regular fa-circle-question" style="font-size:3rem;color:#cad1dd;margin-bottom:12px;display:block;"></i>
                <h2>Không tìm thấy bài học</h2>
                <p style="color:#8a94a8;">Khóa học này chưa có bài học.</p>
            </div>
        <?php endif; ?>
    </div>

    <aside class="learning-sidebar">
        <h4><?= e($courseTitle) ?></h4>
        <?php foreach ($lessonsAll as $idx => $l): ?>
            <a href="<?= url('user/learning.php?course_id=' . $courseId . '&lesson_id=' . (int)$l['id']) ?>"
               class="lesson-list-item <?= ($currentLesson && (int)$l['id'] === (int)$currentLesson['id']) ? 'active' : '' ?>">
                <span class="lesson-check">
                    <i class="fa-regular fa-circle"></i>
                </span>
                <span style="flex:1;min-width:0;">
                    <?= e($l['title']) ?>
                </span>
                <?php if ($l['video_url']): ?>
                    <span class="lesson-duration"><i class="fa-solid fa-video"></i></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </aside>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
