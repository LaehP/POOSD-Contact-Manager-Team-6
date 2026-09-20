<?php
    // Read the request data
    $inData = getRequestInfo();

    // Check if all required fields were actually sent
    if (!is_array($inData) || !isset($inData["firstName"], $inData["lastName"], $inData["login"], $inData["password"], $inData["phone"]))
    {
        returnWithError("Missing required fields");
        exit;
    }

    $firstName = trim($inData["firstName"]);
    $lastName = trim($inData["lastName"]);
    $login = trim($inData["login"]);
    $password = $inData["password"];
    $phone = trim($inData["phone"]);

    // Check for empty fields
    if ($firstName === "" || $lastName === "" || $login === "" || $password === "" || $phone === "") 
    {
        returnWithError("All fields are required");
        exit;
    }

    // parse the .env file for database connection parameters
    $envPath = __DIR__ . '/../../.env';
    
    // check if file exists
    if (!file_exists($envPath)) {
        returnWithError("Server Configuration Error: .env file not found at " . $envPath);
        exit;
    }

    // try to parse it
    $env = @parse_ini_file($envPath);
    
    if ($env === false) {
         returnWithError("Server Configuration Error: Failed to parse .env file.");
         exit;
    }

    // check if required DB keys exist in the parsed file
    if (!isset($env['DB_HOST'], $env['DB_USER'], $env['DB_PASSWORD'], $env['DB_NAME'])) {
        returnWithError("Server Configuration Error: Missing database credentials in .env file.");
        exit;
    }

    // connect to the database
    if (!class_exists("mysqli"))
    {
        returnWithError("Server Configuration Error: PHP mysqli extension is not enabled.");
        exit;
    }

    // connection to the database using the parameters from the .env file
    try
    {
        $conn = new mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASSWORD'], $env['DB_NAME']);
    }
    catch (Throwable $exception)
    {
        returnWithError("Database connection failed: " . $exception->getMessage());
        exit;
    }
    
    if ($conn->connect_error) 
    {
        returnWithError($conn->connect_error);
    } 
    else
    { 
        // check if the login already exists
        $stmt = $conn->prepare("SELECT Login FROM Users WHERE Login = ?"); 
        if (!$stmt)
        {
            returnWithError($conn->error);
            $conn->close();
            exit;
        }
        $stmt->bind_param("s", $login);
        if (!$stmt->execute())
        {
            returnWithError($stmt->error);
            $stmt->close();
            $conn->close();
            exit;
        }
        $stmt->store_result();

        if ($stmt->num_rows > 0)
        {
            returnWithError("user already exists");
            $stmt->close();
            $conn->close();
            exit;
        }

        $stmt->close();

        // insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO Users (FirstName, LastName, Login, Password, Phone) VALUES (?, ?, ?, ?, ?)"); 
        if (!$stmt)
        {
            returnWithError($conn->error);
            $conn->close();
            exit;
        }

        $stmt->bind_param("sssss", $firstName, $lastName, $login, $password, $phone); 
        if ($stmt->execute())
        {
            returnWithInfo($firstName, $lastName, $login, $conn->insert_id);
        }
        else
        {
            returnWithError($stmt->error);
        }

        $stmt->close();
        $conn->close();
    }

    // functions

    function getRequestInfo()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    function sendResultInfoAsJson($obj)
    {
        header('Content-type: application/json');
        echo $obj;
    }

    function returnWithError($err)
    {
        sendResultInfoAsJson(json_encode([
            "ID" => 0,
            "firstName" => "",
            "lastName" => "",
            "login" => "",
            "error" => $err
        ]));
    }

    function returnWithInfo($firstName, $lastName, $login, $id)
    {
        sendResultInfoAsJson(json_encode([
            "ID" => $id,
            "firstName" => $firstName,
            "lastName" => $lastName,
            "login" => $login,
            "error" => ""
        ]));
    }
?>