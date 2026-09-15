<?php
    // NOT COMPLETE

    // Read the database connection parameters from the environment variables
    $inData = getRequestInfo();

    // check if the required fields are present in the request data
    if (!isset($inData["login"]) || !isset($inData["password"]))
    {
        returnWithError("Missing required fields: login and password");
        exit;
    }

    $id = 0;
    $fistName = "";
    $lastName = "";

    // parse the .env file to get the database connection parameters /backend/api/ to /html/
    $envPath = __DIR__ . '/../../.env';
    if (!file_exists($envPath)) {
        returnWithError("Configuration file missing");
        exit;
    }
    $env = parse_ini_file($envPath);

    // Connection to the database using the parameters from the .env file
     $conn = new mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASSWORD'], $env['DB_NAME']);
    if($conn->connect_error)
    {
        returnWithError($conn->connect_error);
    }
    else
    {
        // columns names in user table
        stmt = $conn->prepare("SELECT ID, FirstName, LastName FROM Users WHERE Login=? AND Password=?");

        // bind the parameters to the prepared statement
        $stmt->bind_param("ss", $inData["login"], $inData["password"]);
        $stmt->execute();
        $result = $stmt->get_result();

        // check if a matching user was found
        if($row = $result->fetch_assoc())
        {
            returnWithInfo($row['FirstName'], $row['LastName'], $inData["login"], $row['ID']);
        }
        else
        {
            returnWithError("No Records Found");
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