<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/logs.php';

$id = $_POST['id'] ?? '';
$nome = $_POST['nome'] ?? '';
$latas = intval($_POST['latas'] ?? 0);
$quentao = intval($_POST['quentao'] ?? 0);

logAction('ABRIR_COMANDA_INICIADO', "ID: $id, Nome: $nome, Latas: $latas, Quentao: $quentao");

if (!$id || !$nome) {
    logAction('ABRIR_COMANDA_ERRO', 'Campos obrigatórios não preenchidos');
    echo json_encode(['sucesso' => false, 'erro' => 'Campos obrigatórios não preenchidos.']);
    exit;
}

$link = "https://lulinucs.duckdns.org/super9/?id=" . urlencode($id);

$client = new \Google_Client();
$client->setApplicationName('Controle Latas Clube');
$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
$client->setAuthConfig('credentials.json');
$client->setAccessType('offline');

$service = new Google_Service_Sheets($client);
$spreadsheetId = '19gtqIjcmE8UUlCMfJIZwWbMv4twgBdLxmdYLeZrbUG4';
$range = 'A2:F1000';

// Verifica se já existe o ID
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();
foreach ($values as $row) {
    if (isset($row[0]) && $row[0] === $id) {
        logAction('ABRIR_COMANDA_ERRO', "ID já cadastrado: $id");
        echo json_encode(['sucesso' => false, 'erro' => 'ID já cadastrado!']);
        exit;
    }
}

// Encontra a próxima linha vazia
$nextRow = count($values) + 2; // +2 porque começamos em A2
$rangeToUpdate = "A{$nextRow}:F{$nextRow}";

// Monta a nova linha
$newRow = [$id, $nome, $latas, 0, $quentao, 0];
$body = new Google_Service_Sheets_ValueRange([
    'values' => [$newRow]
]);
$params = ['valueInputOption' => 'RAW'];

try {
    $service->spreadsheets_values->update(
        $spreadsheetId,
        $rangeToUpdate,
        $body,
        $params
    );
    logAction('ABRIR_COMANDA_SUCESSO', "Comanda criada - ID: $id, Nome: $nome, Latas: $latas, Quentao: $quentao, Linha: $nextRow");
    echo json_encode(['sucesso' => true, 'link' => $link]);
} catch (Exception $e) {
    logAction('ABRIR_COMANDA_ERRO', "Erro ao inserir na planilha: " . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
} 