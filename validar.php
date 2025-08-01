
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/logs.php';

$id = $_GET['id'] ?? '';
$ts = $_GET['ts'] ?? '';
$tipo = $_GET['tipo'] ?? 'cerveja';
$hash = $_GET['hash'] ?? '';
$secret = 'segredo_compartilhado';
$expected = hash('sha256', $id . $ts . $tipo . $secret);

logAction('VALIDAR_QR_INICIADO', "ID: $id, Tipo: $tipo, Timestamp: $ts");

if ($hash !== $expected) {
    logAction('VALIDAR_QR_ERRO', "Hash inválido - ID: $id, Tipo: $tipo, Hash recebido: $hash, Hash esperado: $expected");
    $foto = './pics/nofoto.png';
    $gif = gif_aleatorio('./gifs/erro');
    mostrar_pagina($foto, 'Desconhecido', '❌ Hash inválido!', 'QR Code inválido ou adulterado.', $gif);
}
if (abs(time() - intval($ts)) > 30) {
    logAction('VALIDAR_QR_ERRO', "QR Code expirado - ID: $id, Tipo: $tipo, Timestamp: $ts, Tempo atual: " . time());
    $foto = './pics/nofoto.png';
    $gif = gif_aleatorio('./gifs/erro');
    mostrar_pagina($foto, 'Desconhecido', '⚠️ QR Code expirado!', 'Tenta gerar um novo QR Code.', $gif);
}

$client = new \Google_Client();
$client->setApplicationName('Controle Latas Clube');
$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
$client->setAuthConfig('credentials.json');
$client->setAccessType('offline');

$service = new Google_Service_Sheets($client);
$spreadsheetId = '19gtqIjcmE8UUlCMfJIZwWbMv4twgBdLxmdYLeZrbUG4';
$range = 'A2:F100';

$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();

$found = false;
function gif_aleatorio($pasta) {
    $arquivos = glob($pasta . '/*.gif');
    if (!$arquivos || count($arquivos) == 0) return null;
    return $arquivos[array_rand($arquivos)];
}

function mostrar_pagina($foto, $nome, $mensagem, $detalhe, $gif = null, $corFundo = '#222', $corTexto = '#fff') {
    $gifTag = $gif ? "<img class='gif' src='$gif' alt='gif' />" : "";
    echo "<!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
      <title>Validação Super9</title>
      <style>
        body {
          background: linear-gradient(135deg, #ff3b30 0%, #222 100%);
          color: $corTexto;
          font-family: 'Segoe UI', 'Comic Sans MS', cursive, sans-serif;
          margin: 0;
          padding: 0;
          min-height: 100vh;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
        }
        .caixa {
          background: rgba(0,0,0,0.7);
          border-radius: 24px;
          box-shadow: 0 0 32px #ff3b3088;
          padding: 2em 1em 2em 1em;
          margin: 2em 0.5em;
          max-width: 95vw;
          text-align: center;
        }
        .foto {
          width: 120px;
          height: 120px;
          border-radius: 50%;
          border: 5px solid #ff3b30;
          object-fit: cover;
          margin-bottom: 1em;
          background: #fff;
          box-shadow: 0 0 20px #ff3b30aa;
        }
        .nome {
          font-size: 2em;
          font-weight: bold;
          margin-bottom: 0.3em;
          text-shadow: 0 0 8px #ff3b30cc;
        }
        .mensagem {
          font-size: 2.2em;
          margin-bottom: 0.5em;
          font-family: 'Comic Sans MS', cursive, sans-serif;
        }
        .detalhe {
          font-size: 1.3em;
          margin-bottom: 1em;
        }
        .gif {
          width: 100%;
          max-width: 320px;
          border-radius: 18px;
          margin: 1em auto 0.5em auto;
          box-shadow: 0 0 16px #ff3b30cc;
          display: block;
        }
        .zoeira {
          font-size: 1.1em;
          color: #ff3b30;
          margin-top: 1.5em;
          font-family: 'Comic Sans MS', cursive, sans-serif;
        }
        @media (max-width: 600px) {
          .caixa { padding: 1em 0.2em; }
          .foto { width: 90px; height: 90px; }
          .nome { font-size: 1.3em; }
          .mensagem { font-size: 1.5em; }
          .gif { max-width: 95vw; }
        }
      </style>
    </head>
    <body>
      <div class='caixa'>
        <img class='foto' src='$foto' alt='Foto de $nome'>
        <div class='nome'>$nome</div>
        <div class='mensagem'>$mensagem</div>
        <div class='detalhe'>$detalhe</div>
        $gifTag
        <div class='zoeira'>Vai com calma, campeão!</div>
      </div>
    </body>
    </html>";
    exit;
}

foreach ($values as $i => $row) {
    if (isset($row[0]) && $row[0] === $id) {
        $nome = $row[1] ?? 'Sem nome';
        $foto = "./pics/{$id}.png";
        if (!file_exists($foto)) $foto = './pics/nofoto.png';
        if ($tipo === 'cerveja') {
            $total = intval($row[2] ?? 0);
            $validadas = intval($row[3] ?? 0);
            if ($validadas >= $total) {
                logAction('VALIDAR_QR_ERRO', "Limite de latas atingido - ID: $id, Nome: $nome, Validadas: $validadas, Total: $total");
                $gif = gif_aleatorio('./gifs/erro');
                mostrar_pagina($foto, $nome, '🚫 Limite de latas!', "Você já retirou <b>$validadas de $total</b> latas.", $gif);
            }
            $validadas++;
            $rangeToUpdate = "D" . ($i + 2);
            $body = new Google_Service_Sheets_ValueRange([
                'values' => [[$validadas]]
            ]);
            $params = ['valueInputOption' => 'RAW'];
            $service->spreadsheets_values->update($spreadsheetId, $rangeToUpdate, $body, $params);
            logAction('VALIDAR_QR_LATA_SUCESSO', "Lata validada - ID: $id, Nome: $nome, Validadas: $validadas, Total: $total");
            $gif = gif_aleatorio('./gifs/beer');
            mostrar_pagina($foto, $nome, '✅ Lata entregue!', "Você já retirou <b>$validadas de $total</b> latas.", $gif);
        } else if ($tipo === 'quentao') {
            $total = intval($row[4] ?? 0);
            $validados = intval($row[5] ?? 0);
            if ($validados >= $total) {
                logAction('VALIDAR_QR_ERRO', "Limite de quentão atingido - ID: $id, Nome: $nome, Validados: $validados, Total: $total");
                $gif = gif_aleatorio('./gifs/erro');
                mostrar_pagina($foto, $nome, '🚫 Limite de quentão!', "Você já retirou <b>$validados de $total</b> quentões.", $gif);
            }
            $validados++;
            $rangeToUpdate = "F" . ($i + 2);
            $body = new Google_Service_Sheets_ValueRange([
                'values' => [[$validados]]
            ]);
            $params = ['valueInputOption' => 'RAW'];
            $service->spreadsheets_values->update($spreadsheetId, $rangeToUpdate, $body, $params);
            logAction('VALIDAR_QR_QUENTAO_SUCESSO', "Quentão validado - ID: $id, Nome: $nome, Validados: $validados, Total: $total");
            $gif = gif_aleatorio('./gifs/wine');
            mostrar_pagina($foto, $nome, '✅ Quentão entregue!', "Você já retirou <b>$validados de $total</b> quentões.", $gif);
        } else {
            logAction('VALIDAR_QR_ERRO', "Tipo inválido - ID: $id, Tipo: $tipo");
            $gif = gif_aleatorio('./gifs/erro');
            mostrar_pagina($foto, $nome, '❓ Tipo inválido!', 'Chama o admin!', $gif);
        }
        $found = true;
        break;
    }
}

if (!$found) {
    logAction('VALIDAR_QR_ERRO', "ID não encontrado: $id");
    $foto = './pics/nofoto.png';
    $gif = gif_aleatorio('./gifs/erro');
    mostrar_pagina($foto, 'Desconhecido', '❓ ID não encontrado!', 'Confere aí, fera!', $gif);
}
?>
