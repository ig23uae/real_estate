<?php
require_once '../config.php';
require_once '../middleware.php';
redirectIfNotAuthenticated();
requireAdmin();

$stmt = $pdo->query("
    SELECT 
        pay.id,
        pay.booking_id,
        pay.amount,
        pay.payment_date,
        pay.payment_method,
        pay.status,
        u.full_name AS user_name,
        p.title AS property_title
    FROM payments pay
    JOIN bookings b ON pay.booking_id = b.id
    JOIN users u ON b.user_id = u.id
    JOIN properties p ON b.property_id = p.id
    ORDER BY pay.payment_date DESC
");

$payments = $stmt->fetchAll();

require_once '../templates/header.php';
?>

<h2 class="mb-4">Список оплат</h2>

<?php if (empty($payments)): ?>
    <div class="alert alert-info">Оплат пока нет.</div>
<?php else: ?>
    <table class="table table-bordered table-hover table-sm">
        <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Клиент</th>
            <th>Объект</th>
            <th>Сумма</th>
            <th>Дата</th>
            <th>Метод</th>
            <th>Статус</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['user_name']) ?></td>
                <td><?= htmlspecialchars($p['property_title']) ?></td>
                <td><?= number_format($p['amount'], 2) ?> ₽</td>
                <td><?= date('d.m.Y H:i', strtotime($p['payment_date'])) ?></td>
                <td><?= ucfirst($p['payment_method']) ?></td>
                <td>
                        <span class="badge bg-<?= match($p['status']) {
                            'paid' => 'success',
                            'pending' => 'warning',
                            'failed' => 'danger',
                            default => 'secondary'
                        } ?>">
                            <?= ucfirst($p['status']) ?>
                        </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once '../templates/footer.php'; ?>
