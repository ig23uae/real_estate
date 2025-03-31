<?php
require_once __DIR__ . '/../auth.php';
$user = getUser();
$current = $_SERVER['REQUEST_URI'];

function isActive($path) {
    global $current;
    return str_contains($current, $path) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Real Estate Rentals</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100"> <!-- sticky footer -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
    <a class="navbar-brand" href="/index.php">🏠 Недвижимость</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav me-auto">
            <li class="nav-item">
                <a href="/index.php" class="nav-link <?= isActive('/index') ?>">Главная</a>
            </li>
            <li class="nav-item">
                <a href="/catalog.php" class="nav-link <?= isActive('/catalog') ?>">Каталог</a>
            </li>
        </ul>
        <ul class="navbar-nav">
            <?php if (!$user): ?>
                <li class="nav-item"><a href="/user/login.php" class="nav-link <?= isActive('/user/login') ?>">Войти</a></li>
                <li class="nav-item"><a href="/user/register.php" class="nav-link <?= isActive('/user/register') ?>">Регистрация</a></li>
            <?php else: ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <li class="nav-item"><a href="/admin/dashboard.php" class="nav-link <?= isActive('/admin/dashboard') ?> text-warning">Админ</a></li>
                <?php endif; ?>
                <li class="nav-item"><a href="/user/dashboard.php" class="nav-link <?= isActive('/user/dashboard') ?>">Кабинет</a></li>
                <li class="nav-item"><a href="/user/logout.php" class="nav-link text-danger">Выйти</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<main class="container mt-4 flex-grow-1"> <!-- pushes footer down -->
