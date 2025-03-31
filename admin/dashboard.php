<?php
require_once '../config.php';
require_once '../middleware.php';
redirectIfNotAuthenticated();
requireAdmin();

// Получаем ближайшие бронирования (по дате заезда/выезда)
$stmt = $pdo->query("
    SELECT b.id, u.full_name AS user_name, p.title AS property_title, b.check_in, b.check_out, b.status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN properties p ON b.property_id = p.id
    WHERE b.status = 'confirmed'
    ORDER BY b.check_in ASC
    LIMIT 10
");
$bookings = $stmt->fetchAll();

require_once '../templates/header.php';
?>

<h2 class="mb-4">Панель администратора</h2>
<?php if (!empty($_GET['deleted'])): ?>
    <div class="alert alert-success">Объект успешно удалён.</div>
<?php endif; ?>
<div class="row mb-4">
    <div class="col-md-3">
        <a href="bookings.php" class="btn btn-outline-primary w-100">📋 Все бронирования</a>
    </div>
    <div class="col-md-3">
        <a href="payments.php" class="btn btn-outline-success w-100">💳 Все оплаты</a>
    </div>
    <div class="col-md-3">
        <a href="add_property.php" class="btn btn-outline-secondary w-100">➕ Добавить апартамент</a>
    </div>
    <div class="col-md-3">
        <a href="properties.php" class="btn btn-outline-dark w-100">🏡 Все апартаменты</a>
    </div>
</div>

<h4 class="mb-3">Ближайшие заселения / выселения</h4>

<?php if (empty($bookings)): ?>
    <div class="alert alert-info">Нет активных бронирований.</div>
<?php else: ?>
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
        <tr>
            <th>Клиент</th>
            <th>Объект</th>
            <th>Заезд</th>
            <th>Выезд</th>
            <th>Документы</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['user_name']) ?></td>
                <td><?= htmlspecialchars($b['property_title']) ?></td>
                <td><?= $b['check_in'] ?></td>
                <td><?= $b['check_out'] ?></td>
                <td>
                    <a href="/functions/checkin_checkout.php?id=<?= $b['id'] ?>&type=checkin" class="btn btn-sm btn-outline-success mb-1">Заселение</a>
                    <a href="/functions/checkin_checkout?id=<?= $b['id'] ?>&type=checkout" class="btn btn-sm btn-outline-danger">Выселение</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once '../templates/footer.php'; ?>
