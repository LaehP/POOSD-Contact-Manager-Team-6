<?php 
    function connectToDatabase() {
        $host = getenv('DB_HOST');
        $databaseName = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');

        $conn = new mysqli($host, $databaseName, $user, $password);
        
        return $conn;
    }
?>