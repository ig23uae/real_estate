<?php
require_once '../config.php';
require_once '../middleware.php';
redirectIfNotAuthenticated();
requireAdmin();

$property_id = $_GET['id'] ?? null;
if (!$property_id || !is_numeric($property_id)) {
    die("Неверный ID объекта.");
}

// Получаем текущие данные объекта
$stmt = $pdo->prepare("
    SELECT p.*, l.city, l.district, l.address
    FROM properties p
    JOIN locations l ON p.location_id = l.id
    WHERE p.id = ?
");
$stmt->execute([$property_id]);
$property = $stmt->fetch();

if (!$property) {
    die("Объект не найден.");
}

// Категории для выпадающего списка
$categories = $pdo->query("SELECT id, name FROM property_categories")->fetchAll();

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete']) && $_POST['delete'] == 1) {
        // Удаляем объект
        $pdo->prepare("DELETE FROM properties WHERE id = ?")->execute([$property_id]);

        // (опционально) Удалим location, если больше никто не использует
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE location_id = ?");
        $stmt->execute([$property['location_id']]);
        if ($stmt->fetchColumn() == 0) {
            $pdo->prepare("DELETE FROM locations WHERE id = ?")->execute([$property['location_id']]);
        }

        header("Location: dashboard.php?deleted=1");
        exit;
    }


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
        // Обновим location
        $stmt = $pdo->prepare("UPDATE locations SET city = ?, district = ?, address = ? WHERE id = ?");
        $stmt->execute([$city, $district, $address, $property['location_id']]);

        // Обновим объект
        $stmt = $pdo->prepare("UPDATE properties SET title = ?, description = ?, price_per_day = ?, category_id = ? WHERE id = ?");
        $stmt->execute([$title, $description, $price, $category_id, $property_id]);

        // Если загружено новое фото — обновим
        if (isset($_FILES['main_photo']) && $_FILES['main_photo']['error'] === 0) {
            $ext = pathinfo($_FILES['main_photo']['name'], PATHINFO_EXTENSION);
            $photoName = 'property_' . time() . '.' . $ext;
            $photoPath = 'photos/' . $photoName;
            $storagePath = __DIR__ . '/../storage/' . $photoPath;

            move_uploaded_file($_FILES['main_photo']['tmp_name'], $storagePath);

            // Сбросим предыдущий is_main
            $pdo->prepare("UPDATE property_photos SET is_main = 0 WHERE property_id = ?")->execute([$property_id]);

            // Добавим новое
            $pdo->prepare("INSERT INTO property_photos (property_id, photo_url, is_main) VALUES (?, ?, 1)")
                ->execute([$property_id, $photoPath]);
        }

        $success = true;

        // Обновим данные для повторной отрисовки формы
        header("Location: edit_property.php?id=$property_id&updated=1");
        exit;
    }
}

require_once '../templates/header.php';
?>

<div class="container">
    <h2 class="mb-4">Редактирование объекта #<?= $property_id ?></h2>

    <?php if (!empty($_GET['updated'])): ?>
        <div class="alert alert-success">Объект успешно обновлён!</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Название</label>
            <input type="text" name="title" value="<?= htmlspecialchars($property['title']) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Описание</label>
            <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($property['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Цена за сутки (₽)</label>
            <input type="number" name="price_per_day" value="<?= $property['price_per_day'] ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Категория</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Выберите --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $property['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <hr>

        <div class="mb-3">
            <label class="form-label">Город</label>
            <input type="text" name="city" value="<?= htmlspecialchars($property['city']) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Район</label>
            <input type="text" name="district" value="<?= htmlspecialchars($property['district']) ?>" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Адрес</label>
            <input type="text" name="address" value="<?= htmlspecialchars($property['address']) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Новое главное фото (опционально)</label>
            <input type="file" name="main_photo" class="form-control" accept="image/*">
        </div>

        <button class="btn btn-primary">Сохранить изменения</button>

    </form>
    <hr class="my-4">
    <form method="POST" onsubmit="return confirm('Удалить этот объект? Это действие необратимо.')">
        <input type="hidden" name="delete" value="1">
        <button type="submit" class="btn btn-outline-danger">🗑 Удалить объект</button>
    </form>
</div>

<?php require_once '../templates/footer.php'; ?>
