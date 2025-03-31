<?php
require_once '../config.php';
require_once '../middleware.php';
redirectIfNotAuthenticated();
requireAdmin();

$stmt = $pdo->query("
    SELECT b.*, u.full_name AS user_name, p.title AS property_title
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN properties p ON b.property_id = p.id
    ORDER BY b.check_in DESC
");
$bookings = $stmt->fetchAll();
require_once '../templates/header.php';
?>

<h2 class="mb-4">Все бронирования</h2>

<table class="table table-bordered table-hover">
    <thead class="table-light">
    <tr>
        <th>№</th>
        <th>Клиент</th>
        <th>Объект</th>
        <th>Даты</th>
        <th>Сумма</th>
        <th>Статус</th>
        <th>Документы</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($bookings as $booking): ?>
        <tr>
            <td><?= $booking['id'] ?></td>
            <td><?= htmlspecialchars($booking['user_name']) ?></td>
            <td><?= htmlspecialchars($booking['property_title']) ?></td>
            <td><?= $booking['check_in'] ?> — <?= $booking['check_out'] ?></td>
            <td><?= number_format($booking['total_price'], 2) ?> ₽</td>
            <td>
                    <span class="badge bg-<?= match($booking['status']) {
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'secondary',
                        default => 'warning'
                    } ?>">
                        <?= $booking['status'] ?>
                    </span>
            </td>
            <td>
                <a href="/functions/checkin_checkout.php?id=<?= $booking['id'] ?>&type=checkin" class="btn btn-sm btn-outline-success mb-1">Заселение</a>
                <a href="/functions/checkin_checkout.php?id=<?= $booking['id'] ?>&type=checkout" class="btn btn-sm btn-outline-danger">Выселение</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../templates/footer.php'; ?>
