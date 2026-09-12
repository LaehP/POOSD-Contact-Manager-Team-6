<?php
    // NOT COMPLETE

    // Read the database connection parameters from the environment variables
    $inData = getRequestInfo();

    $id = 0;
    $email = "";

    // Connection to the database using the parameters from the .env file
    $conn = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'), getenv('DB_NAME'));
    if($conn->connect_error)
    {
        returnWithError($conn->connect_error);
    }
    else
    {
        $stmt = $conn-> prepare("SELECT ID, email FROM Users WHERE email=? AND password=?");
        $stmt->bind_param("ss", $inData["email"], $inData["password"]);
        $stmt->execute();
        $result = $stmt->get_result();

        // check if a matching user was found
        if($row = $result->fetch_assoc())
        {
            returnWithInfo($row['email'], $row['ID']);
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
        $retValue = '{"ID":0,"email":"","error":"' . $err . '"}';
        sendResultInfoAsJson($retValue);
    }

    // indicates success
    function returnWithInfo($email, $id)
    {
        $retValue = '{"ID":' . $id . ',"email":"' . $email . '","error":""}';
        sendResultInfoAsJson($retValue);
    }

?>