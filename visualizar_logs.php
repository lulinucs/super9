<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/logs.php';

$action = $_GET['action'] ?? 'view';
$filter = $_GET['filter'] ?? '';
$lines = intval($_GET['lines'] ?? 1000);

if ($action === 'clear') {
    $logger->clearLogs();
    header('Location: visualizar_logs.php');
    exit;
}

$logs = $logger->getLogs($lines);

// Filtrar logs se necessário
if ($filter) {
    $logs = array_filter($logs, function($log) use ($filter) {
        return stripos($log, $filter) !== false;
    });
}

// Se action é get_json, retornar logs em formato JSON
if ($action === 'get_json') {
    header('Content-Type: application/json; charset=utf-8');
    
    $logsFormatados = [];
    foreach ($logs as $log) {
        // Extrair informações do log no formato: [timestamp] action | IP: ... | User-Agent: ... | User: userId | Details: details
        if (preg_match('/^\[(.*?)\] (.*?) \| IP: .*? \| User-Agent: .*? \| User: (.*?) \| Details: (.*)$/', $log, $matches)) {
            $timestamp = $matches[1];
            $action = trim($matches[2]);
            $userId = trim($matches[3]);
            $details = trim($matches[4]);
            
            $logsFormatados[] = [
                'timestamp' => $timestamp,
                'action' => $action,
                'userId' => $userId,
                'details' => $details,
                'message' => $log
            ];
        }
    }
    
    echo json_encode($logsFormatados);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logs Super9</title>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      background: #181818;
      font-family: 'Segoe UI', Arial, sans-serif;
      color: #fff;
      min-height: 100vh;
      width: 100vw;
      box-sizing: border-box;
    }
    body {
      width: 100vw;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      justify-content: flex-start;
      padding: 1em;
    }
    h1 {
      font-size: 2.5em;
      margin: 0.5em 0 1em 0;
      color: #FF3B30;
      font-weight: 900;
      text-shadow: 0 0 10px #ff3b30aa;
      text-align: center;
    }
    .controles {
      background: #222;
      border-radius: 20px;
      padding: 1.5em;
      margin-bottom: 1em;
      display: flex;
      flex-direction: column;
      gap: 1em;
      align-items: center;
    }
    .filtro-container {
      display: flex;
      gap: 1em;
      align-items: center;
      flex-wrap: wrap;
      justify-content: center;
    }
    input[type="text"], select {
      padding: 0.8em;
      border-radius: 15px;
      border: none;
      font-size: 1.1em;
      background: #181818;
      color: #fff;
      box-shadow: 0 4px 15px #0002;
      min-width: 200px;
    }
    button {
      background: linear-gradient(135deg, #FF3B30 0%, #ff6b5e 100%);
      color: #fff;
      font-size: 1.1em;
      border: none;
      border-radius: 15px;
      padding: 0.8em 1.5em;
      cursor: pointer;
      box-shadow: 0 4px 15px #ff3b30aa;
      font-weight: bold;
      transition: all 0.3s ease;
    }
    button:hover {
      background: linear-gradient(135deg, #ffb330 0%, #ffc85e 100%);
      color: #222;
      transform: translateY(-2px);
    }
    .limpar-btn {
      background: linear-gradient(135deg, #666 0%, #888 100%);
    }
    .limpar-btn:hover {
      background: linear-gradient(135deg, #888 0%, #aaa 100%);
    }
    .logs-container {
      background: #222;
      border-radius: 20px;
      padding: 1.5em;
      flex: 1;
      overflow-y: auto;
      max-height: 70vh;
    }
    .log-entry {
      background: #333;
      border-radius: 10px;
      padding: 1em;
      margin-bottom: 0.8em;
      font-family: 'Courier New', monospace;
      font-size: 0.9em;
      line-height: 1.4;
      word-break: break-all;
    }
    .log-entry:hover {
      background: #444;
    }
    .sucesso {
      border-left: 4px solid #4CAF50;
    }
    .erro {
      border-left: 4px solid #ff3b30;
    }
    .info {
      border-left: 4px solid #2196F3;
    }
    .voltar-btn {
      background: linear-gradient(135deg, #666 0%, #888 100%);
      color: #fff;
      font-size: 1.2em;
      border: none;
      border-radius: 15px;
      padding: 1em 2em;
      cursor: pointer;
      box-shadow: 0 4px 15px #0004;
      font-weight: bold;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      margin-top: 1em;
      text-align: center;
    }
    .voltar-btn:hover {
      background: linear-gradient(135deg, #888 0%, #aaa 100%);
      transform: translateY(-2px);
    }
    .stats {
      background: #333;
      border-radius: 15px;
      padding: 1em;
      margin-bottom: 1em;
      text-align: center;
    }
    @media (max-width: 480px) {
      .filtro-container {
        flex-direction: column;
      }
      input[type="text"], select {
        min-width: 90vw;
      }
      .log-entry {
        font-size: 0.8em;
      }
    }
  </style>
</head>
<body>
  <h1>📊 Logs do Sistema</h1>
  
  <div class="controles">
    <form method="GET" class="filtro-container">
      <input type="text" name="filter" placeholder="Filtrar logs..." value="<?= htmlspecialchars($filter) ?>" />
      <select name="lines">
        <option value="50" <?= $lines === 50 ? 'selected' : '' ?>>50 linhas</option>
        <option value="100" <?= $lines === 100 ? 'selected' : '' ?>>100 linhas</option>
        <option value="200" <?= $lines === 200 ? 'selected' : '' ?>>200 linhas</option>
        <option value="500" <?= $lines === 500 ? 'selected' : '' ?>>500 linhas</option>
      </select>
      <button type="submit">Filtrar</button>
    </form>
    
    <div style="display: flex; gap: 1em;">
      <a href="?action=clear" class="limpar-btn" onclick="return confirm('Tem certeza que deseja limpar todos os logs?')">Limpar Logs</a>
      <a href="admin.html" class="voltar-btn">← Voltar ao Menu</a>
    </div>
  </div>
  
  <div class="stats">
    <strong>Total de logs exibidos:</strong> <?= count($logs) ?>
    <?php if ($filter): ?>
      <br><strong>Filtro ativo:</strong> "<?= htmlspecialchars($filter) ?>"
    <?php endif; ?>
  </div>
  
  <div class="logs-container">
    <?php if (empty($logs)): ?>
      <div class="log-entry info">
        Nenhum log encontrado.
      </div>
    <?php else: ?>
      <?php foreach ($logs as $log): ?>
        <?php
        $class = 'info';
        if (strpos($log, 'SUCESSO') !== false) $class = 'sucesso';
        elseif (strpos($log, 'ERRO') !== false) $class = 'erro';
        ?>
        <div class="log-entry <?= $class ?>">
          <?= htmlspecialchars($log) ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html> 