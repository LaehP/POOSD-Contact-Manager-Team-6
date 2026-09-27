<?php
    // read the request data
    $inData = getRequestInfo();

    // check if the required fields are present in the request data
    if (!isset($inData["login"]) || !isset($inData["password"]))
    {
        returnWithError("Missing required fields: login and password");
        exit;
    }

    // check for empty fields
    if ($inData["login"] === "" || $inData["password"] === "") 
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

    $id = 0;
    $fistName = "";
    $lastName = "";

    // connect to the database
    if (!class_exists("mysqli"))
    {
        returnWithError("Server Configuration Error: PHP mysqli extension is not enabled.");
        exit;
    }

    // Connection to the database using the parameters from the .env file
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
        // columns names in user table
        $stmt = $conn->prepare("SELECT ID, FirstName, LastName FROM Users WHERE Login=? AND Password=?");

        // bind the parameters to the prepared statement
        $stmt->bind_param("ss", $inData["login"], $inData["password"]);
        $stmt->execute();
        $result = $stmt->get_result();


        if($row = $result->fetch_assoc())
        {
            returnWithInfo($row['FirstName'], $row['LastName'], $inData["login"], $row['ID']);
        }
        else
        {
            returnWithError("login or password incorrect");
        }
        
        $stmt->close();
        $conn->close();
    }

    // functions:

    // read request data from the input stream and decode the JSON body
    function getRequestInfo()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    // send the response data as JSON to the client
    function sendResultInfoAsJson($obj)
    {
        header('Content-type: application/json');
        echo $obj;
    }

    // indicates failure
    function returnWithError($err)
    {
        $retValue = '{"ID":0, "firstName":"", "lastName":"", "error":"' . $err . '"}';
        sendResultInfoAsJson($retValue);
    }

    // indicates success
    function returnWithInfo($firstName, $lastName, $login, $id)
    {
        $retValue = '{"ID":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","login":"' . $login . '","error":""}';
        sendResultInfoAsJson($retValue);
    }

?>