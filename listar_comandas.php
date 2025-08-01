<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/logs.php';

logAction('LISTAR_COMANDAS_INICIADO');

$client = new \Google_Client();
$client->setApplicationName('Controle Latas Clube');
$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
$client->setAuthConfig('credentials.json');
$client->setAccessType('offline');

$service = new Google_Service_Sheets($client);
$spreadsheetId = '19gtqIjcmE8UUlCMfJIZwWbMv4twgBdLxmdYLeZrbUG4';
$range = 'A2:F1000';

try {
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();
    
    $comandas = [];
    foreach ($values as $row) {
        if (isset($row[0]) && !empty($row[0])) {
            $comandas[] = [
                'id' => $row[0],
                'nome' => $row[1] ?? 'Sem nome',
                'totalLatas' => intval($row[2] ?? 0),
                'validadas' => intval($row[3] ?? 0),
                'totalQuentao' => intval($row[4] ?? 0),
                'validados' => intval($row[5] ?? 0)
            ];
        }
    }
    
    logAction('LISTAR_COMANDAS_SUCESSO', "Total de comandas encontradas: " . count($comandas));
    echo json_encode(['sucesso' => true, 'comandas' => $comandas]);
} catch (Exception $e) {
    logAction('LISTAR_COMANDAS_ERRO', "Erro ao listar comandas: " . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
} 