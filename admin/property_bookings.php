<?php
require_once '../config.php';
require_once '../middleware.php';
redirectIfNotAuthenticated();
requireAdmin();

$property_id = $_GET['id'] ?? null;
if (!$property_id || !is_numeric($property_id)) {
    die("Неверный ID объекта.");
}

// Получаем объект
$stmt = $pdo->prepare("SELECT title FROM properties WHERE id = ?");
$stmt->execute([$property_id]);
$property = $stmt->fetch();

if (!$property) {
    die("Объект не найден.");
}

// Получаем бронирования
$stmt = $pdo->prepare("
    SELECT b.*, u.full_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.property_id = ?
    ORDER BY b.check_in DESC
");
$stmt->execute([$property_id]);
$bookings = $stmt->fetchAll();

require_once '../templates/header.php';
?>

<h2 class="mb-4">Бронирования по объекту: <?= htmlspecialchars($property['title']) ?></h2>

<a href="properties.php" class="btn btn-sm btn-outline-secondary mb-3">← Назад к списку объектов</a>

<?php if (empty($bookings)): ?>
    <div class="alert alert-info">Нет бронирований по данному объекту.</div>
<?php else: ?>
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light">
        <tr>
            <th>Клиент</th>
            <th>Заезд</th>
            <th>Выезд</th>
            <th>Сумма</th>
            <th>Статус</th>
            <th>PDF</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['full_name']) ?></td>
                <td><?= $b['check_in'] ?></td>
                <td><?= $b['check_out'] ?></td>
                <td><?= number_format($b['total_price'], 2) ?> ₽</td>
                <td>
                        <span class="badge bg-<?= match($b['status']) {
                            'confirmed' => 'success',
                            'cancelled' => 'danger',
                            'completed' => 'secondary',
                            default => 'warning'
                        } ?>">
                            <?= $b['status'] ?>
                        </span>
                </td>
                <td>
                    <a href="/functions/checkin_checkout.php?id=<?= $b['id'] ?>&type=checkin" class="btn btn-sm btn-outline-success">Заселение</a>
                    <a href="/functions/checkin_checkout.php?id=<?= $b['id'] ?>&type=checkout" class="btn btn-sm btn-outline-danger">Выселение</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once '../templates/footer.php'; ?>
