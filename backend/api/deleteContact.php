<?php
    // Delete a contact from the database

    // Get the request data
    $inData = getRequestInfo();

    // Check for empty fields
    if ($contactFirstName === "" || $contactLastName === "" || $userId === "") 
    {
        returnWithError("All fields are required");
        exit;
    }

    //// parse the .env file for database connection parameters
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

    $contactFirstName = $inData["contactFirstName"];
    $contactLastName = $inData["contactLastName"];
    $userId = $inData["userId"];

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
        // search for the contact in the database and gather the contact information to be deleted
        $contactSearch = $conn->prepare("SELECT * FROM CONTACTS WHERE ContactFirstName = ? AND ContactLastName = ? AND UserID = ?");
        $contactSearch->bind_param("si", $contactFirstName, $contactLastName, $userId);
        $contactSearch->execute();
        $contactResult = $contactSearch->get_result();

        if ($contactResult->num_rows === 0) {
            returnWithError("No contact found with the provided ContactFirstName, ContactLastName, and UserID.");
        }

        // remove the contact from the database
        $contactDeletion = $conn->prepare("DELETE FROM CONTACTS WHERE ContactFirstName = ? AND ContactLastName = ? AND UserID = ?");
        $contactDeletion->bind_param("ssii", $contactFirstName, $contactLastName, $userId);
    
        if ($contactDeletion->execute()) 
        {
            // check if a row was actually deleted
            if ($contactDeletion->affected_rows > 0) 
            {
                returnWithError("");
            } 
            else 
            {
                returnWithError("No contact found with the provided ContactFirstName, ContactLastName, and UserID.");
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