<?php
class JsonHandler {
    private $basePath;
    
    public function __construct() {
        $this->basePath = dirname(__DIR__) . '/data/';
        $this->initializeFiles();
    }
    
    private function initializeFiles() {
        $files = ['empleados.json', 'asistencias.json', 'escaneos.json'];
        foreach ($files as $file) {
            $filePath = $this->basePath . $file;
            if (!file_exists($filePath)) {
                file_put_contents($filePath, json_encode([]));
            }
        }
    }
    
    public function readData($file) {
        $filePath = $this->basePath . $file . '.json';
        $jsonData = file_get_contents($filePath);
        return json_decode($jsonData, true) ?? [];
    }
    
    public function writeData($file, $data) {
        $filePath = $this->basePath . $file . '.json';
        return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    public function getNextId($file) {
        $data = $this->readData($file);
        $maxId = 0;
        foreach ($data as $item) {
            if (isset($item['id']) && $item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
        return $maxId + 1;
    }
}

$jsonHandler = new JsonHandler();
?> 