<?php
    $inData = getRequestInfo();

    if (!is_array($inData) || !isset($inData["firstName"], $inData["lastName"], $inData["login"], $inData["password"]))
    {
        returnWithError("Missing required fields");
        exit;
    }

    $firstName = trim($inData["firstName"]);
    $lastName = trim($inData["lastName"]);
    $email = trim($inData["login"]);
    $password = $inData["password"];

    if ($firstName === "" || $lastName === "" || $email === "" || $password === "") // Check for empty fields
    {
        returnWithError("All fields are required");
        exit;
    }

    //Manually parse the .env file
    $envPath = __DIR__ . '/../../.env';
    if (!file_exists($envPath)) {
        returnWithError("Configuration file missing");
        exit;
    }
    $env = parse_ini_file($envPath);

    $conn = new mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASSWORD'], $env['DB_NAME']);
    if ($conn->connect_error) 
    {
        returnWithError($conn->connect_error);
    } 
    else
    { 
        $stmt = $conn->prepare("SELECT Login FROM Users WHERE Login = ?"); 
        if (!$stmt)
        {
            returnWithError($conn->error);
            $conn->close();
            exit;
        }
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) // Check if the login already exists in the database
        {
            returnWithError("user already exists");
            $conn->close();
            exit;
        }

        // Insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO Users (FirstName, LastName, Login, Password) VALUES (?, ?, ?, ?)"); 
        if (!$stmt)
        {
            returnWithError($conn->error);
            $conn->close();
            exit;
        }

        $stmt->bind_param("ssss", $firstName, $lastName, $login, $password); // Bind the parameters to the prepared statement
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

    // functions:

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