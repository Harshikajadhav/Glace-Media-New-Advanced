<?php
$adminAssetPrefix = $adminAssetPrefix ?? '';
$adminPageTitle = $adminPageTitle ?? 'Dashboard';
$adminSiteHref = $adminSiteHref ?? '../index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminPageTitle, ENT_QUOTES, 'UTF-8') ?> | Glace Media Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($adminAssetPrefix, ENT_QUOTES, 'UTF-8') ?>style.css">
    <script src="../cursor.js" defer></script>
</head>
<body>
<div class="app">
    <main class="main">
        <header class="topbar">
            <button class="mobile-menu" id="mobileMenu" type="button">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="breadcrumb">
                <span>Admin</span>
                <i class="fa-solid fa-chevron-right"></i>
                <strong id="breadcrumbTitle"><?= htmlspecialchars($adminPageTitle, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="topbar-actions">
                <a class="view-site" href="<?= htmlspecialchars($adminSiteHref, ENT_QUOTES, 'UTF-8') ?>">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    View Website
                </a>
            </div>
        </header>
