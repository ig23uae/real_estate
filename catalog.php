<?php
require_once 'config.php';
require_once 'templates/header.php';

// Получаем список объектов
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
");
$properties = $stmt->fetchAll();
?>

    <h2 class="mb-4 text-center">Каталог недвижимости</h2>

<?php if (empty($properties)): ?>
    <div class="alert alert-info">Объекты недвижимости пока не добавлены.</div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
        <?php foreach ($properties as $property): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="/storage/<?= htmlspecialchars($property['main_photo'] ?? 'placeholder.jpg') ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Фото объекта">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($property['title']) ?></h5>
                        <p class="card-text text-truncate"><?= htmlspecialchars($property['description']) ?></p>
                        <p class="fw-bold mt-auto mb-2"><?= number_format($property['price_per_day'], 0, ',', ' ') ?> ₽ / сутки</p>
                        <a href="/property.php?id=<?= $property['id'] ?>" class="btn btn-primary">Подробнее</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
require_once 'templates/footer.php';
