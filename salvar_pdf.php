<?php
header('Content-Type: application/json; charset=utf-8');

$dir = __DIR__ . '/comprovantes';

if (!is_dir($dir)) {
    if (!mkdir($dir, 0755, true)) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'erro' => 'Falha ao criar a pasta comprovantes'
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'erro' => 'Método não permitido'
    ]);
    exit;
}

$protocolo = $_POST['protocolo'] ?? '';
$pdfBase64 = $_POST['pdf'] ?? '';

if (!$protocolo || !$pdfBase64) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'erro' => 'Dados incompletos'
    ]);
    exit;
}


$protocolo = preg_replace('/[^A-Za-z0-9\-_]/', '', $protocolo);

if (!$protocolo) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'erro' => 'Protocolo inválido'
    ]);
    exit;
}


if (strpos($pdfBase64, ',') !== false) {
    $partes = explode(',', $pdfBase64);
    $pdfBase64 = end($partes);
}


$pdfBase64 = preg_replace('/\s+/', '', $pdfBase64);

$pdfBinario = base64_decode($pdfBase64, true);

if ($pdfBinario === false) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'erro' => 'PDF base64 inválido'
    ]);
    exit;
}


if (substr($pdfBinario, 0, 4) !== '%PDF') {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'erro' => 'Arquivo enviado não parece ser um PDF'
    ]);
    exit;
}

$arquivo = $dir . '/comprovante-' . $protocolo . '.pdf';

if (file_put_contents($arquivo, $pdfBinario) === false) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'erro' => 'Falha ao salvar o PDF no servidor'
    ]);
    exit;
}

echo json_encode([
    'ok' => true,
    'arquivo' => 'comprovantes/comprovante-' . $protocolo . '.pdf'
]);
