<?php
    // Delete a contact from the database

    // Get the request data
    $inData = getRequestInfo();

    $userId = $inData['userId'] ?? '';
    $id = $inData['id'] ?? '';

    // Check for empty fields
    if ($id === "" || $userId === "") 
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
        // Search for the contact by its ID and owning user.
        $contactSearch = $conn->prepare("SELECT ID FROM Contacts WHERE ID = ? AND UserID = ?");
        $contactSearch->bind_param("ii", $id, $userId);
        $contactSearch->execute();
        $contactResult = $contactSearch->get_result();

        if ($contactResult->num_rows === 0) {
            returnWithError("No contact found with the provided ID and UserID.");
            exit;
        }

        // remove the contact from the database
        $contactDeletion = $conn->prepare("DELETE FROM Contacts WHERE ID = ? AND UserID = ?");
        $contactDeletion->bind_param("ii", $id, $userId);
    
        if ($contactDeletion->execute()) 
        {
            // check if a row was actually deleted
            if ($contactDeletion->affected_rows > 0) 
            {
                returnWithError("");
            } 
            else 
            {
                returnWithError("No contact found with the provided ID and UserID.");
            }
        } 
        else 
        {
            returnWithError("Failed to delete contact: " . $contactDeletion->error);
        }

        $contactDeletion->close();
        $conn->close();

    }

    // functions:

    function getRequestInfo()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    function sendResultInfoAsJson( $obj )
    {
        header('Content-type: application/json');
        echo $obj;
    }

    function returnWithError( $err )
    {
        $retValue = '{"error":"' . $err . '"}';
        sendResultInfoAsJson( $retValue );
    }

?>