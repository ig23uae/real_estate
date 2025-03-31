<?php
require_once '../config.php';
require_once '../middleware.php';
redirectIfNotAuthenticated();

require_once 'fpdf.php';

// Функция перекодировки текста в Windows-1251 для FPDF
function encodeText($text) {
    return iconv('UTF-8', 'windows-1251//IGNORE', $text);
}

$booking_id = $_GET['id'] ?? null;

if (!$booking_id || !is_numeric($booking_id)) {
    die("Неверный ID бронирования.");
}

// Получаем бронирование
$stmt = $pdo->prepare("
    SELECT b.*, u.full_name AS user_name, p.title AS property_title, p.price_per_day
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN properties p ON b.property_id = p.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking || $booking['user_id'] !== getUser()['id']) {
    die("Бронирование не найдено или доступ запрещён.");
}

// Получаем гостей
$stmt = $pdo->prepare("SELECT full_name FROM booking_guests WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$guests = $stmt->fetchAll();

// Получаем главное фото
$stmt = $pdo->prepare("SELECT photo_url FROM property_photos WHERE property_id = ? AND is_main = 1 LIMIT 1");
$stmt->execute([$booking['property_id']]);
$photo = $stmt->fetch();

$mainPhotoPath = $photo ? __DIR__ . '/../storage/' . $photo['photo_url'] : null;

// Создаём PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->AddFont('Arial', '', 'arial.php');
$pdf->AddFont('Arial', 'B', 'arialb.php');

// Заголовок
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, encodeText("Документ бронирования №$booking_id"), 0, 1, 'C');
$pdf->Ln(8);

// Информация о бронировании
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, encodeText("Клиент:"), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, encodeText($booking['user_name']), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, encodeText("Объект:"), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, encodeText($booking['property_title']), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, encodeText("Даты:"), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, encodeText("с " . $booking['check_in'] . " по " . $booking['check_out']), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, encodeText("Сумма:"), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, encodeText(number_format($booking['total_price'], 2) . " ₽"), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, encodeText("Статус:"), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, encodeText(ucfirst($booking['status'])), 0, 1);

$pdf->Ln(10);

// Список гостей
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, encodeText("Гости:"), 0, 1);

$pdf->SetFont('Arial', '', 12);
if (count($guests) === 0) {
    $pdf->Cell(0, 8, encodeText("Гости не указаны"), 0, 1);
} else {
    foreach ($guests as $i => $guest) {
        $pdf->Cell(0, 8, encodeText(($i + 1) . ". " . $guest['full_name']), 0, 1);
    }
}

if ($mainPhotoPath && file_exists($mainPhotoPath)) {
    $pdf->Image($mainPhotoPath, 10, $pdf->GetY(), 90); // x=10, y=текущий, ширина=90мм
    $pdf->Ln(55); // добавим отступ после фото
}


// Подпись и дата
$pdf->Ln(15);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, encodeText("Документ сформирован: " . date("d.m.Y H:i")), 0, 1);
$pdf->Ln(10);
$pdf->Cell(0, 8, encodeText("Спасибо за бронирование и доверие!"), 0, 1);

// Отправляем PDF в браузер
$pdf->Output("I", "booking_$booking_id.pdf");
