<?php

require_once __DIR__ . '/auth.php';

function requireOwner($resourceOwnerId): void
{
    $currentUser = getUser();
    if ($currentUser['id'] !== $resourceOwnerId && !isAdmin()) {
        http_response_code(403);
        echo "Доступ запрещён: вы не владелец этого ресурса.";
        exit;
    }
}

function abortIf($condition, $message = 'Доступ запрещён', $code = 403): void
{
    if ($condition) {
        http_response_code($code);
        echo $message;
        exit;
    }
}

function redirectIfAuthenticated(): void
{
    if (isAuthenticated()) {
        $user = getUser();
        echo $user;
        $target = $user['role'] === 'admin' ? '/admin/dashboard.php' : '/user/dashboard.php';
        header("Location: $target");
        exit;
    }
}

function redirectIfNotAuthenticated(): void
{
    if (!isAuthenticated()) {
        header("Location: /user/login.php");
        exit;
    }
}

function requireAdmin(): void
{
    if (!isAuthenticated()) {
        header('Location: /user/login.php');
        exit;
    }

    $user = getUser();
    if (!isset($user['role']) || $user['role'] !== 'admin') {
        http_response_code(403);
        echo "Доступ запрещён: только для администратора.";
        exit;
    }
}


