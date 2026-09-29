<!-- ============ TOPBAR (đồng bộ trên mọi trang, cả trang đăng nhập) ============ -->
<div class="topbar">
    <div class="container topbar-inner">
        <div class="topbar-info">
            <span><i class="fa-solid fa-phone"></i> 0123 456 789</span>
            <span><i class="fa-regular fa-envelope"></i> support@eduhub.vn</span>
            <span class="topbar-location"><i class="fa-solid fa-location-dot"></i> Việt Nam</span>
        </div>
        <div class="topbar-actions">
            <?php if ($isLoggedIn): ?>
                <span class="topbar-balance"><i class="fa-solid fa-wallet"></i> Số dư: <strong><?= money($userBalance) ?></strong></span>
                <span class="topbar-greet"><i class="fa-regular fa-circle-user"></i> Xin chào, <?= e($userName) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ============ NAVBAR / HEADER CHÍNH ============ -->
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= url('index.php') ?>" class="logo">
            <span class="logo-mark"><i class="fa-solid fa-graduation-cap"></i></span>
            <span class="logo-text">Edu<span>Hub</span></span>
        </a>

        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="<?= url('index.php') ?>" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Trang chủ</a></li>
                <li><a href="<?= url('courses.php') ?>" class="<?= $currentPage === 'courses.php' ? 'active' : '' ?>">Khóa học</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="<?= url('user/my-courses.php') ?>" class="<?= $currentPage === 'my-courses.php' ? 'active' : '' ?>">Khóa học của tôi</a></li>
                    <li><a href="<?= url('user/learning.php') ?>" class="<?= $currentPage === 'learning.php' ? 'active' : '' ?>">Học tập</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="header-actions">
            <?php if ($isLoggedIn): ?>
                <div class="user-menu" id="userMenu">
                    <button type="button" class="user-trigger" aria-haspopup="true" aria-expanded="false">
                        <span class="user-avatar">
                            <?php if ($userAvatar !== ''): ?>
                                <img src="<?= e($userAvatar) ?>" alt="Avatar">
                            <?php else: ?>
                                <?= e($userInitial) ?>
                            <?php endif; ?>
                        </span>
                        <span class="user-name-text"><?= e($userName) ?></span>
                        <i class="fa-solid fa-chevron-down user-caret"></i>
                    </button>

                    <div class="user-dropdown">
                        <div class="dropdown-header">
                            <strong><?= e($userName) ?></strong>
                            <span><?= e($_SESSION['email'] ?? '') ?></span>
                        </div>
                        <a href="<?= url('user/profile.php') ?>"><i class="fa-regular fa-circle-user"></i> Hồ sơ của tôi</a>
                        <a href="<?= url('user/my-courses.php') ?>"><i class="fa-solid fa-book-open"></i> Khóa học của tôi</a>
                        <a href="<?= url('user/learning.php') ?>"><i class="fa-solid fa-graduation-cap"></i> Tiếp tục học</a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= url('logout.php') ?>" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= url('login.php') ?>" class="btn btn-ghost">Đăng nhập</a>
                <a href="<?= url('register.php') ?>" class="btn btn-accent">Đăng ký</a>
            <?php endif; ?>
        </div>

        <button class="mobile-toggle" id="mobileToggle" aria-label="Mở menu" aria-expanded="false">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</header>

<main class="site-main">