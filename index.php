<?php
// Подключение к базе
require_once 'config.php';
require_once 'auth.php';
require_once 'templates/header.php';

$stmt = $pdo->query("
    SELECT 
        p.id, 
        p.title, 
        p.description, 
        p.price_per_day, 
        (SELECT photo_url FROM property_photos WHERE property_id = p.id AND is_main = 1 LIMIT 1) AS main_photo
    FROM properties p
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
    LIMIT 6
");
$properties = $stmt->fetchAll();
?>

<section class="py-5">
    <h2 class="text-center mb-4">Актуальные предложения</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($properties as $property): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="/storage/<?= htmlspecialchars($property['main_photo'] ?? 'https://via.placeholder.com/600x400?text=Нет+фото') ?>" class="card-img-top" alt="Фото объекта">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($property['title']) ?></h5>
                        <p class="card-text text-truncate"><?= htmlspecialchars($property['description']) ?></p>
                        <p class="fw-bold">от <?= number_format($property['price_per_day'], 0, ',', ' ') ?> ₽/сутки</p>
                        <a href="property.php?id=<?= $property['id'] ?>" class="btn btn-primary">Подробнее</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once 'templates/footer.php'; ?>