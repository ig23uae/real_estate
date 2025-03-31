<?php
require_once '../config.php';
require_once '../middleware.php';
redirectIfNotAuthenticated();
requireAdmin();
require_once 'fpdf.php';

// Кодировка текста
function encodeText($text) {
    return iconv('UTF-8', 'windows-1251//IGNORE', $text);
}

$booking_id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'checkin'; // default to checkin

if (!$booking_id || !in_array($type, ['checkin', 'checkout'])) {
    die("Неверные параметры.");
}

// Получаем данные брони
$stmt = $pdo->prepare("
    SELECT b.*, u.full_name AS user_name, p.title AS property_title, l.address, l.city
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN properties p ON b.property_id = p.id
    JOIN locations l ON p.location_id = l.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    die("Бронирование не найдено.");
}

// Гости
$stmt = $pdo->prepare("SELECT full_name FROM booking_guests WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$guests = $stmt->fetchAll();

$actionTitle = $type === 'checkin' ? "Акт заселения" : "Акт выселения";
$dateTitle = $type === 'checkin' ? $booking['check_in'] : $booking['check_out'];

// Создание PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->AddFont('Arial', '', 'arial.php');
$pdf->AddFont('Arial', 'B', 'arialb.php');

// Заголовок
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, encodeText($actionTitle), 0, 1, 'C');
$pdf->Ln(5);

// Общая информация
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, encodeText("Дата:"), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, encodeText($dateTitle), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, encodeText("Клиент:"), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, encodeText($booking['user_name']), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, encodeText("Объект:"), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, encodeText($booking['property_title']), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, encodeText("Адрес:"), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, encodeText($booking['address'] . ", " . $booking['city']), 0, 1);

$pdf->Ln(8);

// Гости
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, encodeText("Список гостей:"), 0, 1);
$pdf->SetFont('Arial', '', 12);
foreach ($guests as $i => $guest) {
    $pdf->Cell(0, 8, encodeText(($i + 1) . ". " . $guest['full_name']), 0, 1);
}

$pdf->Ln(15);

// Подписи
$pdf->Cell(0, 8, encodeText("Подпись клиента: ___________________________"), 0, 1);
$pdf->Cell(0, 8, encodeText("Подпись представителя: ______________________"), 0, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 8, encodeText("Документ сгенерирован: " . date('d.m.Y H:i')), 0, 1);

$pdf->Output("I", "{$type}_booking_{$booking_id}.pdf");
