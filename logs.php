<?php
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Logger {
    private $logFile;
    
    public function __construct($logFile = 'logs/super9.log') {
        $this->logFile = $logFile;
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory() {
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    public function log($action, $details = '', $userId = '', $ip = '') {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $ip ?: $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logEntry = sprintf(
            "[%s] %s | IP: %s | User-Agent: %s | User: %s | Details: %s\n",
            $timestamp,
            $action,
            $ip,
            $userAgent,
            $userId,
            $details
        );
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public function getLogs($lines = 100) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $logs = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice(array_reverse($logs), 0, $lines);
    }
    
    public function clearLogs() {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }
}

// Instância global do logger
$logger = new Logger();

// Função helper para facilitar o uso
function logAction($action, $details = '', $userId = '') {
    global $logger;
    $logger->log($action, $details, $userId);
} 