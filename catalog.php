<?php
require_once 'config.php';
require_once 'templates/header.php';

// Получаем список категорий (для фильтра)
$categories = $pdo->query("SELECT id, name FROM property_categories")->fetchAll();

// Подготовка SQL и параметров
$sql = "
    SELECT 
        p.id,
        p.title,
        p.description,
        p.price_per_day,
        p.category_id,
        (SELECT photo_url FROM property_photos WHERE property_id = p.id AND is_main = 1 LIMIT 1) AS main_photo,
        (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.property_id = p.id) AS avg_rating
    FROM properties p
    WHERE p.is_active = 1
";

$params = [];
$conditions = [];

// Фильтрация по категории
if (!empty($_GET['category_id'])) {
    $conditions[] = "p.category_id = ?";
    $params[] = $_GET['category_id'];
}

// Фильтрация по цене
if (!empty($_GET['max_price'])) {
    $conditions[] = "p.price_per_day <= ?";
    $params[] = $_GET['max_price'];
}

if ($conditions) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

// HAVING для фильтрации по рейтингу
if (!empty($_GET['rating'])) {
    $sql .= " HAVING avg_rating >= ?";
    $params[] = $_GET['rating'];
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();

$categoryId = $_GET['category_id'] ?? null;
?>

<h2 class="mb-4 text-center">Каталог недвижимости</h2>

<!-- 🔍 Форма фильтра -->
<form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">Тип недвижимости</label>
        <select name="category_id" class="form-select">
            <option value="">Все типы</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $categoryId == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Макс. цена (₽)</label>
        <input type="number" name="max_price" class="form-control" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
    </div>

    <div class="col-md-3">
        <label class="form-label">Минимальный рейтинг</label>
        <select name="rating" class="form-select">
            <option value="">Любой</option>
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>" <?= $_GET['rating'] == $i ? 'selected' : '' ?>>от <?= $i ?> ★</option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-outline-primary w-100">Применить фильтр</button>
    </div>
</form>

<!-- 🏡 Карточки -->
<?php if (empty($properties)): ?>
    <div class="alert alert-info">Объекты недвижимости не найдены по выбранным фильтрам.</div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
        <?php foreach ($properties as $property): ?>
            <div class="col">
                <div class="card h-100 shadow-sm position-relative">
                    <!-- ⭐️ Рейтинг -->
                    <?php if ($property['avg_rating']): ?>
                        <div class="position-absolute top-0 end-0 bg-warning text-dark px-2 py-1 rounded-start" style="z-index: 2;">
                            ★ <?= $property['avg_rating'] ?>/5
                        </div>
                    <?php endif; ?>

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

<?php require_once 'templates/footer.php'; ?>
