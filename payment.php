<?php
require_once 'config.php';
require_once 'middleware.php';

redirectIfNotAuthenticated();

$booking_id = $_GET['id'] ?? null;

if (!$booking_id || !is_numeric($booking_id)) {
    http_response_code(400);
    echo "Неверный ID бронирования.";
    exit;
}

// Получаем данные о бронировании
$stmt = $pdo->prepare("
    SELECT b.*, p.title AS property_title
    FROM bookings b
    JOIN properties p ON b.property_id = p.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

requireOwner($booking['user_id']);

// Проверим: есть ли уже оплата
$stmt = $pdo->prepare("SELECT * FROM payments WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$payment = $stmt->fetch();

// Если оплата прошла — показать «Спасибо»
if ($payment && $payment['status'] === 'paid') {
    require_once 'templates/header.php';
    ?>

    <div class="text-center mt-5">
        <h2>✅ Спасибо, оплата прошла успешно!</h2>
        <p>Бронирование подтверждено.</p>
        <a href="functions/download_booking.php?id=<?=$booking['id']?>" class="btn btn-outline-primary mt-3">Скачать бронирование</a>
    </div>

    <?php
    require_once 'templates/footer.php';
    exit;
}

// Обработка формы оплаты
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Имитация успешной оплаты
    if (!$payment) {
        $stmt = $pdo->prepare("INSERT INTO payments (booking_id, amount, payment_method, status) VALUES (?, ?, 'card', 'paid')");
        $stmt->execute([$booking_id, $booking['total_price']]);
    } else {
        $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', payment_date = NOW() WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
    }

    // Обновим статус бронирования
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
    $stmt->execute([$booking_id]);

    // Редиректим на саму страницу
    header("Location: payment.php?id=$booking_id");
    exit;
}
?>

<?php require_once 'templates/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4">Оплата бронирования</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5>Объект: <?= htmlspecialchars($booking['property_title']) ?></h5>
            <p>Даты: с <strong><?= $booking['check_in'] ?></strong> по <strong><?= $booking['check_out'] ?></strong></p>
            <p>Сумма к оплате: <strong><?= number_format($booking['total_price'], 2) ?> ₽</strong></p>

            <form method="POST">
                <button class="btn btn-success mt-3">Оплатить</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
