<?php
require_once '../config.php';
require_once '../middleware.php';

redirectIfNotAuthenticated();

$user = getUser();
$property_id = $_POST['property_id'];
$check_in = $_POST['check_in'];
$check_out = $_POST['check_out'];
$price = $_POST['price'];
$guests = $_POST['guests'] ?? [];

$stmt = $pdo->prepare("INSERT INTO bookings (user_id, property_id, check_in, check_out, total_price, status) VALUES (?, ?, ?, ?, ?, 'pending')");
$stmt->execute([$user['id'], $property_id, $check_in, $check_out, $price]);

$bookingId = $pdo->lastInsertId();

// Сохраняем гостей
$guestStmt = $pdo->prepare("INSERT INTO booking_guests (booking_id, full_name, check_in, check_out) VALUES (?, ?, ?, ?)");
foreach ($guests as $guest) {
    $name = trim($guest);
    if ($name !== '') {
        $guestStmt->execute([$bookingId, $name, $check_in, $check_out]);
    }
}

header("Location: /payment.php?id=$bookingId");
exit;
