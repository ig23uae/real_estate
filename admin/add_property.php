<?php
require_once '../config.php';
require_once '../middleware.php';
redirectIfNotAuthenticated();
requireAdmin();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price_per_day'] ?? 0;
    $category_id = $_POST['category_id'] ?? null;
    $city = $_POST['city'] ?? '';
    $district = $_POST['district'] ?? '';
    $address = $_POST['address'] ?? '';

    if (!$title || !$price || !$category_id || !$city || !$address) {
        $errors[] = "Заполните все обязательные поля.";
    }

    if (empty($errors)) {
        // 1. Сохраняем локацию
        $stmt = $pdo->prepare("INSERT INTO locations (city, district, address) VALUES (?, ?, ?)");
        $stmt->execute([$city, $district, $address]);
        $location_id = $pdo->lastInsertId();

        // 2. Сохраняем апартамент
        $stmt = $pdo->prepare("
            INSERT INTO properties (title, description, price_per_day, category_id, location_id, owner_id, is_active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $title,
            $description,
            $price,
            $category_id,
            $location_id,
            getUser()['id'] // админ как владелец
        ]);
        $property_id = $pdo->lastInsertId();
        // 3. Загрузка фото
        if (isset($_FILES['main_photo']) && $_FILES['main_photo']['error'] === 0) {
            $ext = pathinfo($_FILES['main_photo']['name'], PATHINFO_EXTENSION);
            $photoName = 'property_' . time() . '.' . $ext;
            $photoPath = 'photos/' . $photoName;
            $storagePath = __DIR__ . '/../storage/' . $photoPath;

            move_uploaded_file($_FILES['main_photo']['tmp_name'], $storagePath);

            $stmt = $pdo->prepare("INSERT INTO property_photos (property_id, photo_url, is_main) VALUES (?, ?, 1)");
            $stmt->execute([$property_id, $photoPath]);
        }

        $success = true;
    }
}

// Получаем категории для выпадающего списка
$categories = $pdo->query("SELECT id, name FROM property_categories")->fetchAll();

require_once '../templates/header.php';
?>

<div class="container">
    <h2 class="mb-4">Добавление нового объекта</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">Объект успешно добавлен!</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?= implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mb-4">
    <div class="mb-3">
            <label class="form-label">Название</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Описание</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Цена за сутки (₽)</label>
            <input type="number" name="price_per_day" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Категория</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Выберите --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <hr>

        <div class="mb-3">
            <label class="form-label">Город</label>
            <input type="text" name="city" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Район</label>
            <input type="text" name="district" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Адрес</label>
            <input type="text" name="address" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Фото объекта (главное)</label>
            <input type="file" name="main_photo" class="form-control" accept="image/*" required>
        </div>

        <button class="btn btn-primary">Сохранить объект</button>
    </form>
</div>

<?php require_once '../templates/footer.php'; ?>
