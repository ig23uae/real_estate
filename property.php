<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'templates/header.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo "<div class='alert alert-danger'>Объект не найден.</div>";
    require_once 'templates/footer.php';
    exit;
}

// Получаем данные по объекту
$stmt = $pdo->prepare("
    SELECT p.*, l.city, l.district, l.address, c.name as category
    FROM properties p
    JOIN locations l ON p.location_id = l.id
    JOIN property_categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$property = $stmt->fetch();

if (!$property) {
    echo "<div class='alert alert-warning'>Объект не найден.</div>";
    require_once 'templates/footer.php';
    exit;
}

// Фото
$stmt = $pdo->prepare("SELECT photo_url FROM property_photos WHERE property_id = ?");
$stmt->execute([$id]);
$photos = $stmt->fetchAll();
?>

<h2><?= htmlspecialchars($property['title']) ?></h2>

<div class="row">
    <div class="col-md-7">
        <?php if ($photos): ?>
            <div id="photoCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                <div class="carousel-inner rounded shadow">
                    <?php foreach ($photos as $i => $photo): ?>
                        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                            <img src="storage/<?= htmlspecialchars($photo['photo_url']) ?>" class="d-block w-100" style="max-height: 500px; object-fit: cover;" alt="Фото объекта">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($photos) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#photoCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark rounded" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#photoCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark rounded" aria-hidden="true"></span>
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <img src="https://via.placeholder.com/800x500?text=Нет+фото" class="img-fluid rounded mb-3" alt="Нет фото">
        <?php endif; ?>
    </div>

    <div class="col-md-5">
        <h5 class="mb-3">Описание</h5>
        <p><?= nl2br(htmlspecialchars($property['description'])) ?></p>
        <ul class="list-group mb-3">
            <li class="list-group-item"><strong>Город:</strong> <?= $property['city'] ?></li>
            <li class="list-group-item"><strong>Район:</strong> <?= $property['district'] ?></li>
            <li class="list-group-item"><strong>Адрес:</strong> <?= $property['address'] ?></li>
            <li class="list-group-item"><strong>Категория:</strong> <?= $property['category'] ?></li>
            <li class="list-group-item"><strong>Цена:</strong> <?= number_format($property['price_per_day'], 0, ',', ' ') ?> ₽ / сутки</li>
        </ul>
        <button class="btn btn-success w-100 mt-3" data-bs-toggle="modal" data-bs-target="#bookingModal">Забронировать</button>
    </div>
</div>

<!-- Модальное окно -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="bookingForm" method="POST" action="functions/booking_confirm.php">
            <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
            <div class="modal-header">
                <h5 class="modal-title">Бронирование</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="check_in" class="form-label">Дата заезда</label>
                    <input type="date" name="check_in" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="check_out" class="form-label">Дата выезда</label>
                    <input type="date" name="check_out" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Гости</label>
                    <div id="guestsContainer">
                        <div class="guest-entry mb-2">
                            <label>Гость 1</label>
                            <input type="text" name="guests[]" class="form-control mb-2" required placeholder="ФИО гостя">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" id="addGuest" class="btn btn-outline-success btn-sm">+ Добавить гостя</button>
                        <button type="button" id="removeGuest" class="btn btn-outline-danger btn-sm">– Удалить</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Итого</label>
                    <input type="text" id="totalPrice" class="form-control" readonly>
                </div>
                <input type="hidden" name="price" id="calculatedPrice">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Перейти к оплате</button>
            </div>
        </form>
    </div>
</div>
<script>
    let guestCount = 1;

    document.getElementById('addGuest').addEventListener('click', () => {
        guestCount++;
        const container = document.getElementById('guestsContainer');

        const div = document.createElement('div');
        div.classList.add('guest-entry', 'mb-2');
        div.innerHTML = `
        <label>Гость ${guestCount}</label>
        <input type="text" name="guests[]" class="form-control mb-2" required placeholder="ФИО гостя">
    `;
        container.appendChild(div);
    });

    document.getElementById('removeGuest').addEventListener('click', () => {
        if (guestCount > 1) {
            const container = document.getElementById('guestsContainer');
            container.lastElementChild.remove();
            guestCount--;
        }
    });
</script>

<script>
    const pricePerDay = <?= (int)$property['price_per_day'] ?>;
    const checkIn = document.querySelector('input[name="check_in"]');
    const checkOut = document.querySelector('input[name="check_out"]');
    const totalPrice = document.getElementById('totalPrice');
    const hiddenPrice = document.getElementById('calculatedPrice');

    function calculateTotal() {
        const inDate = new Date(checkIn.value);
        const outDate = new Date(checkOut.value);
        if (inDate && outDate && outDate > inDate) {
            const days = (outDate - inDate) / (1000 * 60 * 60 * 24);
            const total = days * pricePerDay;
            totalPrice.value = `${total} ₽ за ${days} ночей`;
            hiddenPrice.value = total;
        } else {
            totalPrice.value = '';
            hiddenPrice.value = '';
        }
    }

    checkIn.addEventListener('change', calculateTotal);
    checkOut.addEventListener('change', calculateTotal);
</script>

<?php require_once 'templates/footer.php'; ?>
