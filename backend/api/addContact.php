
<?php

    require_once "databaseConnection.php";
    require_once "frontendInfo.php";

    $conn = connectToDatabase();

    $inData = getRequestInfo(); 
    if ($inData === null) {
        http_response_code(500);
        returnWithError("JSON decode failed");
        exit;
    }
    if (!array_key_exists("firstName", $inData) || !array_key_exists("lastName", $inData) || !array_key_exists("phoneNumber", $inData) || !array_key_exists("email", $inData)) {
        http_response_code(400);
        returnWithError("Minimum contact information not defined. Must include first name, last name, phone number, and email.");
        exit;
    }
    if (!array_key_exists("userId", $inData)) {
        http_response_code(400);
        returnWithError("User ID must be defined");
        exit;
    }
    $contactFirstName = $inData["firstName"];
    $contactLastName = $inData["lastName"];
    $contactPhoneNumber = $inData["phoneNumber"];
    $contactEmail = $inData["email"];
    $userId = $inData["userId"];



    if ($conn->connect_error) 
    {
        http_response_code(500);
        returnWithError( $conn->connect_error );
        exit;
    }
    else {
        $userCheck = $conn->prepare("SELECT id FROM Users WHERE id = ? LIMIT 1");
        $userCheck->bind_param("i", $userId);
        if ($userCheck->execute()) {
            $userResult =$userCheck->get_result();
            $returnedUserId = $userResult -> fetch_assoc();

            if ($returnedUserId === null) {
                http_response_code(404);
                returnWithError("User not found for given userId");
                $userCheck->close();
                $conn->close();
                exit;
            }
            $userCheck->close();
        }
        else {
            http_response_code(500);
            returnWithError($userCheck->error);
            $userCheck->close();
            $conn->close();
            exit;
        }
        $contactInsertion = $conn->prepare("INSERT into CONTACTS (FirstName, LastName, Phone, Email, UserID) VALUES(?, ?, ?, ?, ?) ");
        $contactInsertion->bind_param("ssssi", $contactFirstName, $contactLastName, $contactPhoneNumber, $contactEmail, $userId);
        if($contactInsertion->execute()) {
            $contactId = $conn->insert_id;
            $message = json_encode ([
                "message" => "Contact created",
                "id" => $contactId
            ]);
            http_response_code(200);
            sendResultInfoAsJson($message);
        }
        else {
            http_response_code(500);
            returnWithError($contactInsertion->error);
        }
        $contactInsertion->close();
        $conn->close();
    }   
?>