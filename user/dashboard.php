<?php
require_once '../config.php';
require_once '../auth.php';

require_once '../middleware.php';

redirectIfNotAuthenticated();

if (isAdmin()) {
    header("Location: /admin/dashboard.php");
    exit;
}

$user = getUser();

// Получаем заказы пользователя
$stmt = $pdo->prepare("
    SELECT 
        bookings.id,
        bookings.check_in,
        bookings.check_out,
        bookings.total_price,
        bookings.status,
        properties.title AS property_title
    FROM bookings
    JOIN properties ON bookings.property_id = properties.id
    WHERE bookings.user_id = ?
    ORDER BY bookings.check_in DESC
");
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();

require_once '../templates/header.php';
?>
<h2 class="mb-4">Здравствуйте, <?= htmlspecialchars($user['full_name']) ?>!</h2>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Ваши бронирования</h4>
    <a href="logout.php" class="btn btn-outline-danger">Выйти</a>
</div>

<?php if (empty($bookings)): ?>
    <div class="alert alert-info">У вас пока нет бронирований.</div>
<?php else: ?>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
        <tr>
            <th>Объект</th>
            <th>Заезд</th>
            <th>Выезд</th>
            <th>Сумма</th>
            <th>Статус</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $booking): ?>
            <?php
            $badgeClass = match ($booking['status']) {
                'confirmed' => 'success',
                'cancelled' => 'danger',
                'completed' => 'secondary',
                default => 'warning'
            };
            ?>
            <tr>
                <td><?= htmlspecialchars($booking['property_title']) ?></td>
                <td><?= htmlspecialchars($booking['check_in']) ?></td>
                <td><?= htmlspecialchars($booking['check_out']) ?></td>
                <td><?= number_format($booking['total_price'], 2) ?> ₽</td>
                <td>
                    <span class="badge bg-<?= $badgeClass ?>"><?= $booking['status'] ?></span>
                    <?php if ($booking['status'] === 'confirmed'): ?>
                        <br>
                        <a href="/functions/download_booking.php?id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                            Скачать документ
                        </a>
                    <?php endif; ?>
                </td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php require_once '../templates/footer.php'; ?>
