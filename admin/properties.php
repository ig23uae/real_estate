<?php
require_once '../config.php';
require_once '../middleware.php';
redirectIfNotAuthenticated();
requireAdmin();

$stmt = $pdo->query("
    SELECT 
        p.id, 
        p.title, 
        p.price_per_day, 
        p.is_active,
        l.city, 
        l.address
    FROM properties p
    JOIN locations l ON p.location_id = l.id
    ORDER BY p.created_at DESC
");
$properties = $stmt->fetchAll();

require_once '../templates/header.php';
?>

<h2 class="mb-4">Список объектов</h2>

<?php if (empty($properties)): ?>
    <div class="alert alert-info">Объекты не добавлены.</div>
<?php else: ?>
    <table class="table table-sm table-bordered table-hover align-middle">
        <thead class="table-light">
        <tr>
            <th>№</th>
            <th>Название</th>
            <th>Город / Адрес</th>
            <th>Цена/сутки</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($properties as $prop): ?>
            <tr>
                <td><?= $prop['id'] ?></td>
                <td><?= htmlspecialchars($prop['title']) ?></td>
                <td><?= htmlspecialchars($prop['city'] . ', ' . $prop['address']) ?></td>
                <td><?= number_format($prop['price_per_day'], 0, ',', ' ') ?> ₽</td>
                <td>
                        <span class="badge bg-<?= $prop['is_active'] ? 'success' : 'secondary' ?>">
                            <?= $prop['is_active'] ? 'Активен' : 'Выключен' ?>
                        </span>
                </td>
                <td>
                    <a href="edit_property.php?id=<?= $prop['id'] ?>" class="btn btn-sm btn-outline-primary mb-1">✏ Редактировать</a><br>
                    <a href="property_bookings.php?id=<?= $prop['id'] ?>" class="btn btn-sm btn-outline-dark">📋 Бронирования</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once '../templates/footer.php'; ?>
