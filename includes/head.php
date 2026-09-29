<?php
// db.php tự khởi tạo phiên an toàn + kết nối CSDL
require_once __DIR__ . '/db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $_SESSION['user_name'] ?? $_SESSION['full_name'] ?? 'Học viên';
$userAvatar = trim((string)($_SESSION['user_avatar'] ?? ''));
$userInitial = $userAvatar !== '' ? '' : mb_substr(trim($userName), 0, 1);
$userBalance = (float)($_SESSION['balance'] ?? 0);

// Xác định trang hiện tại để active menu
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>EduHub</title>

    <!-- Fonts: Fraunces (tiêu đề) + Manrope (nội dung) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons: Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>