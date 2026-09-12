<?php
    // NOT COMPLETE: This file is incomplete and does not create a new account. It only checks if the email already exists in the database and returns an error if it does. The code to insert a new user into the database is missing.

    $inData = getRequestInfo();

    $firstName = $inData["firstName"];
    $lastName = $inData["lastName"];
    $email = $inData["email"];
    $password = $inData["password"];

    $conn = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'), getenv('DB_NAME'));
    if ($conn->connect_error) 
    {
        returnWithError($conn->connect_error);
    } 
    else
    { 
        $stmt = $conn->prepare("SELECT email FROM Users WHERE email = ?"); // check if a user with the same email already exists
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) 
        {
            returnWithError("Email already exists");
            $stmt->close();
            $conn->close();
            exit;
        }
        $stmt->close();
        $conn->close();
        returnWithError("Failed to create account");
        exit;
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
        $retValue = '{"ID":0,"firstName":"","lastName":"","email":"","error":"' . $err . '"}';
        sendResultInfoAsJson($retValue);
    }

    function returnWithInfo($firstName, $lastName, $email, $id)
    {
        $retValue = '{"ID":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","email":"' . $email . '","error":""}';
        sendResultInfoAsJson($retValue);
    }

?>