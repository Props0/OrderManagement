<?php
require 'excelreader.php';

$controller = new ExcelController();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ler o JSON enviado no corpo da requisição
    $jsonInput = file_get_contents('php://input');
    $decodedInput = json_decode($jsonInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados JSON inválidos.']);
        exit;
    }

    // Validação mínima dos dados
    if (!isset($decodedInput['dados']) || !is_array($decodedInput['dados'])) {
        http_response_code(400);
        echo json_encode(['error' => 'O formato esperado é {"dados": {...}}.']);
        exit;
    }

    // Escrever os dados no Excel
    $result = $controller->writeToExcel( $decodedInput['dados']);

    if (isset($result['error'])) {
        http_response_code(500);
        echo json_encode(['error' => $result['error']]);
    } else {
        echo json_encode(['message' => 'Dados escritos com sucesso!']);
    }
    exit;
}

// Obter os dados para a visualização
$data = $controller->generateReport();

// Verificar se houve erros ao gerar o relatório
if (isset($data['error'])) {
    http_response_code(500);
    echo json_encode(['error' => $data['error']]);
    exit;
}

echo json_encode(['dados' => $data]);