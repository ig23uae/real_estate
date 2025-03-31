<?php
function isAuthenticated(): bool
{
    return isset($_SESSION['user']);
}

function getUser() {
    return $_SESSION['user'] ?? null;
}

function isAdmin(): bool
{
    return isAuthenticated() && $_SESSION['user']['role'] === 'admin';
}
