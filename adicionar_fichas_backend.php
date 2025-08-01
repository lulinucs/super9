<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/logs.php';

$id = $_POST['id'] ?? '';
$latas = intval($_POST['latas'] ?? 0);
$quentao = intval($_POST['quentao'] ?? 0);

logAction('ADICIONAR_FICHAS_INICIADO', "ID: $id, Latas: $latas, Quentao: $quentao");

if (!$id) {
    logAction('ADICIONAR_FICHAS_ERRO', 'ID não especificado');
    echo json_encode(['sucesso' => false, 'erro' => 'ID não especificado.']);
    exit;
}

if ($latas === 0 && $quentao === 0) {
    logAction('ADICIONAR_FICHAS_ERRO', 'Nenhuma ficha para adicionar');
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhuma ficha para adicionar.']);
    exit;
}

$client = new \Google_Client();
$client->setApplicationName('Controle Latas Clube');
$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
$client->setAuthConfig('credentials.json');
$client->setAccessType('offline');

$service = new Google_Service_Sheets($client);
$spreadsheetId = '19gtqIjcmE8UUlCMfJIZwWbMv4twgBdLxmdYLeZrbUG4';
$range = 'A2:F1000';

try {
    // Buscar dados atuais da comanda
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();
    
    $found = false;
    $rowIndex = 0;
    $currentData = null;
    
    foreach ($values as $i => $row) {
        if (isset($row[0]) && $row[0] === $id) {
            $found = true;
            $rowIndex = $i + 2; // +2 porque começamos em A2
            $currentData = [
                'nome' => $row[1] ?? 'Sem nome',
                'totalLatas' => intval($row[2] ?? 0),
                'validadas' => intval($row[3] ?? 0),
                'totalQuentao' => intval($row[4] ?? 0),
                'validados' => intval($row[5] ?? 0)
            ];
            break;
        }
    }
    
    if (!$found) {
        logAction('ADICIONAR_FICHAS_ERRO', "Comanda não encontrada: $id");
        echo json_encode(['sucesso' => false, 'erro' => 'Comanda não encontrada.']);
        exit;
    }
    
    // Calcular novos totais
    $novoTotalLatas = $currentData['totalLatas'] + $latas;
    $novoTotalQuentao = $currentData['totalQuentao'] + $quentao;
    
    // Atualizar latas (coluna C)
    if ($latas > 0) {
        $rangeLatas = "C{$rowIndex}";
        $bodyLatas = new Google_Service_Sheets_ValueRange([
            'values' => [[$novoTotalLatas]]
        ]);
        $params = ['valueInputOption' => 'RAW'];
        $service->spreadsheets_values->update($spreadsheetId, $rangeLatas, $bodyLatas, $params);
        logAction('ADICIONAR_FICHAS_LATAS', "ID: $id, Latas antigas: {$currentData['totalLatas']}, Novas: $latas, Total: $novoTotalLatas");
    }
    
    // Atualizar quentão (coluna E)
    if ($quentao > 0) {
        $rangeQuentao = "E{$rowIndex}";
        $bodyQuentao = new Google_Service_Sheets_ValueRange([
            'values' => [[$novoTotalQuentao]]
        ]);
        $params = ['valueInputOption' => 'RAW'];
        $service->spreadsheets_values->update($spreadsheetId, $rangeQuentao, $bodyQuentao, $params);
        logAction('ADICIONAR_FICHAS_QUENTAO', "ID: $id, Quentao antigo: {$currentData['totalQuentao']}, Novos: $quentao, Total: $novoTotalQuentao");
    }
    
    logAction('ADICIONAR_FICHAS_SUCESSO', "ID: $id, Nome: {$currentData['nome']}, Latas: $latas, Quentao: $quentao");
    
    // Retornar dados atualizados
    echo json_encode([
        'sucesso' => true,
        'nome' => $currentData['nome'],
        'totalLatas' => $novoTotalLatas,
        'validadas' => $currentData['validadas'],
        'totalQuentao' => $novoTotalQuentao,
        'validados' => $currentData['validados']
    ]);
    
} catch (Exception $e) {
    logAction('ADICIONAR_FICHAS_ERRO', "Erro ao adicionar fichas: " . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
} 