<?php
// midtrans_create.php — bikin transaksi Snap di Midtrans, kembalikan token buat popup pembayaran
require 'config.php';
require 'includes/auth.php';
require 'includes/midtrans_config.php';
requireLogin();

header('Content-Type: application/json');

if (!midtransConfigured()) {
    http_response_code(400);
    echo json_encode(['error' => 'Midtrans belum dikonfigurasi. Isi Server Key & Client Key di includes/midtrans_config.php']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$total = (int)($input['total'] ?? 0);

if ($total <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Total belanja tidak valid.']);
    exit;
}

$orderId = 'AMELS-' . date('YmdHis') . '-' . rand(100, 999);

$payload = [
    'transaction_details' => [
        'order_id' => $orderId,
        'gross_amount' => $total,
    ],
    'credit_card' => ['secure' => true],
];

$ch = curl_init(midtransSnapUrl());
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
    ],
]);
$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menghubungi Midtrans: ' . $curlError]);
    exit;
}

$data = json_decode($response, true);
if (isset($data['token'])) {
    echo json_encode(['token' => $data['token'], 'order_id' => $orderId]);
} else {
    http_response_code(500);
    echo json_encode(['error' => $data['error_messages'][0] ?? 'Gagal membuat transaksi Midtrans.']);
}
