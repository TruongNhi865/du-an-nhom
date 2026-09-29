</main>

<!-- ============ FOOTER ============ -->
<footer class="site-footer">
    <div class="footer-top">
        <div class="container footer-grid">
            <div class="footer-col footer-brand">
                <a href="<?= url('index.php') ?>" class="footer-logo">
                    <span class="logo-mark"><i class="fa-solid fa-graduation-cap"></i></span>
                    <span class="logo-text">Edu<span>Hub</span></span>
                </a>
                <p>Nền tảng học trực tuyến giúp bạn nâng cao kỹ năng mỗi ngày — từ lập trình, an toàn thông tin đến mạng máy tính.</p>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="#" aria-label="Zalo"><i class="fa-solid fa-comment-dots"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Liên kết nhanh</h4>
                <ul>
                    <li><a href="<?= url('index.php') ?>">Trang chủ</a></li>
                    <li><a href="<?= url('courses.php') ?>">Danh sách khóa học</a></li>
                    <li><a href="<?= url('user/my-courses.php') ?>">Khóa học của tôi</a></li>
                    <li><a href="<?= url('user/learning.php') ?>">Học tập</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Tài khoản</h4>
                <ul>
                    <?php if ($isLoggedIn ?? false): ?>
                        <li><a href="<?= url('user/profile.php') ?>">Hồ sơ của tôi</a></li>
                        <li><a href="<?= url('logout.php') ?>">Đăng xuất</a></li>
                    <?php else: ?>
                        <li><a href="<?= url('login.php') ?>">Đăng nhập</a></li>
                        <li><a href="<?= url('register.php') ?>">Đăng ký</a></li>
                    <?php endif; ?>
                    <li><a href="<?= url('course-detail.php?id=1') ?>">Khóa học tiêu biểu</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Liên hệ</h4>
                <ul class="footer-contact">
                    <li><i class="fa-regular fa-envelope"></i> support@eduhub.vn</li>
                    <li><i class="fa-solid fa-phone"></i> 0123 456 789</li>
                    <li><i class="fa-solid fa-location-dot"></i> 01 Võ Văn Ngân, TP. Thủ Đức</li>
                </ul>

                <div class="footer-newsletter">
                    <label for="newsletterEmail">Nhận khóa học hay mỗi tuần</label>
                    <form class="newsletter-form" action="" method="post" onsubmit="event.preventDefault();this.querySelector('input').value='';alert('Cảm ơn bạn đã đăng ký nhận tin!');">
                        <input type="email" id="newsletterEmail" placeholder="Email của bạn" required>
                        <button type="submit" aria-label="Đăng ký"><i class="fa-solid fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <p>&copy; <?= date('Y') ?> EduHub. Đồ án nhóm — All rights reserved.</p>
            <p>Thiết kế bởi Team EduHub <span class="footer-heart"><i class="fa-solid fa-heart"></i></span></p>
        </div>
    </div>
</footer>

<script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>