<?php 
    function connectToDatabase() {
        $envPath = __DIR__ . '/../../.env';
        $config = parse_ini_file($envPath);

        if ($config === false) {
            die("Unable to load environment configuration.");
        }

        $conn = new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PASSWORD'], $config['DB_NAME']);

        return $conn;
    }
?>