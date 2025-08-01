<?php
header('Content-Type: application/json');

$state_file = __DIR__ . '/placar_state.json';
$history_file = __DIR__ . '/placar_history.json';

function ler_json($file, $default = []) {
    if (!file_exists($file)) return $default;
    $data = file_get_contents($file);
    return json_decode($data, true) ?: $default;
}

function salvar_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_state') {
    $state = ler_json($state_file, [
        'running' => false,
        'start_time' => null,
        'tempo_total' => 12*60,
        'paused' => false,
        'pause_time' => null,
        'gols' => ['norte' => 0, 'sul' => 0],
        'nome_norte' => 'NORTE',
        'nome_sul' => 'SUL',
        'jogadores_norte' => ["Jogador 1", "Jogador 2", "Jogador 3"],
        'jogadores_sul' => ["Jogador 1", "Jogador 2", "Jogador 3"],
    ]);
    // Cálculo do tempo restante
    $tempo_total = intval($state['tempo_total']);
    $running = !!($state['running'] ?? false);
    $paused = !!($state['paused'] ?? false);
    $start_time = $state['start_time'] ? intval($state['start_time']) : null;
    $pause_time = $state['pause_time'] ? intval($state['pause_time']) : null;
    // Correção: se não estiver rodando, start_time e pause_time devem ser null
    if (!$running) {
        $start_time = null;
        $pause_time = null;
        $state['start_time'] = null;
        $state['pause_time'] = null;
    }
    if (!$paused) {
        $pause_time = null;
        $state['pause_time'] = null;
    }
    if (!$start_time || $start_time < 1000000000) { // timestamp inválido
        $timer = $tempo_total;
    } else if ($running && !$paused) {
        $elapsed = time() - $start_time;
        $timer = max(0, $tempo_total - $elapsed);
    } else if ($running && $paused && $pause_time) {
        $elapsed = $pause_time - $start_time;
        $timer = max(0, $tempo_total - $elapsed);
    } else {
        $timer = $tempo_total;
    }
    $state['timer'] = $timer;
    echo json_encode($state);
    exit;
}

if ($action === 'set_state') {
    $state = ler_json($state_file, []);
    $state['running'] = !!($_POST['running'] ?? false);
    if ($state['running']) {
        if (!isset($_POST['start_time']) || !$_POST['start_time']) {
            $state['start_time'] = time();
        } else {
            $state['start_time'] = intval($_POST['start_time']);
        }
    } else {
        $state['start_time'] = null;
    }
    $state['tempo_total'] = intval($_POST['tempo_total'] ?? 12*60);
    $state['paused'] = !!($_POST['paused'] ?? false);
    $state['pause_time'] = $_POST['pause_time'] ? intval($_POST['pause_time']) : null;
    $state['gols'] = [
        'norte' => intval($_POST['gols_norte'] ?? 0),
        'sul' => intval($_POST['gols_sul'] ?? 0),
    ];
    $state['nome_norte'] = $_POST['nome_norte'] ?? 'NORTE';
    $state['nome_sul'] = $_POST['nome_sul'] ?? 'SUL';
    $state['jogadores_norte'] = isset($_POST['jogadores_norte']) ? json_decode($_POST['jogadores_norte'], true) : ["Jogador 1", "Jogador 2", "Jogador 3"];
    $state['jogadores_sul'] = isset($_POST['jogadores_sul']) ? json_decode($_POST['jogadores_sul'], true) : ["Jogador 1", "Jogador 2", "Jogador 3"];
    salvar_json($state_file, $state);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'save_game') {
    $history = ler_json($history_file, []);
    $game = [
        'data' => date('Y-m-d H:i:s'),
        'nome_norte' => $_POST['nome_norte'] ?? 'NORTE',
        'nome_sul' => $_POST['nome_sul'] ?? 'SUL',
        'gols_norte' => intval($_POST['gols_norte'] ?? 0),
        'gols_sul' => intval($_POST['gols_sul'] ?? 0),
        'tempo_total' => intval($_POST['tempo_total'] ?? 12*60),
        'jogadores_norte' => isset($_POST['jogadores_norte']) ? json_decode($_POST['jogadores_norte'], true) : ["Jogador 1", "Jogador 2", "Jogador 3"],
        'jogadores_sul' => isset($_POST['jogadores_sul']) ? json_decode($_POST['jogadores_sul'], true) : ["Jogador 1", "Jogador 2", "Jogador 3"],
    ];
    $history[] = $game;
    salvar_json($history_file, $history);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'get_history') {
    $history = ler_json($history_file, []);
    echo json_encode($history);
    exit;
}

echo json_encode(['error' => 'Ação inválida']); 