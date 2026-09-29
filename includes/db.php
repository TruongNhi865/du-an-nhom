<?php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);

    session_name('eduhub_session');
    session_start();
}

/* ========================================================= *
 * 2. KẾT NỐI CƠ SỞ DỮ LIỆU (PDO + Prepared Statement)
 * ========================================================= */
$host = "localhost";
$dbname = "secure_course";
$username = "root";
$password = "";

try {

    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Chống SQL injection bằng Prepared Statement thật (không emulate)
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    die("Không thể kết nối cơ sở dữ liệu.");
}

/* ========================================================= *
 * 3. HẰNG SỐ & HELPER DÙNG CHUNG
 * ========================================================= */

if (!defined('BASE_URL')) {
    define('BASE_URL', '/du-an-nhom');
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        if ($path === '' || $path[0] === '/') {
            return BASE_URL . $path;
        }
        return BASE_URL . '/' . $path;
    }
}

if (!function_exists('e')) {
    // Chống XSS: escape mọi dữ liệu trước khi xuất ra HTML
    function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('money')) {
    function money($amount): string
    {
        return number_format((float)$amount, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('priceLabel')) {
    function priceLabel($amount): string
    {
        return (float)$amount <= 0 ? 'Miễn phí' : money($amount);
    }
}


//  *  BẢO VỆ CSRF (Chống giả mạo yêu cầu)
//  *    - csrf_token(): sinh & lưu token vào phiên
//  *    - csrf_field(): xuất hidden input vào form
//  *    - csrf_verify(): kiểm tra token khi nhận POST

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        return is_string($token)
            && $token !== ''
            && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}