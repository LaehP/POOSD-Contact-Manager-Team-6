
<?php

    require_once __DIR__ . "/databaseConnection.php";
    require_once __DIR__ . "/frontendInfo.php";

    // connect to the database
    if (!class_exists("mysqli"))
    {
        http_response_code(500);
        returnWithError("Server Configuration Error: PHP mysqli extension is not enabled.");
        exit;
    }

    try {
        $conn = connectToDatabase();
    }
    catch (Throwable $exception) {
        http_response_code(500);
        returnWithError("Database connection failed: " . $exception->getMessage());
        exit;
    }

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
    $contactFirstName = trim((string)$inData["firstName"] ?? "");
    $contactLastName = trim((string)$inData["lastName"] ?? "");
    $contactPhoneNumber = trim((string)$inData["phoneNumber"] ?? "");
    $contactEmail = trim((string)$inData["email"] ?? "");
    $userId = trim((string)$inData["userId"] ?? "");



    if ($conn->connect_error) 
    {
        http_response_code(500);
        returnWithError( $conn->connect_error );
        exit;
    }
    else {
        $userCheck = $conn->prepare("SELECT id FROM Users WHERE id = ? LIMIT 1");
        if ($userCheck === false) {
            http_response_code(500);
            returnWithError($conn->error);
            $conn->close();
            exit;
        }
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
        $contactInsertion = $conn->prepare("INSERT into Contacts (FirstName, LastName, Phone, Email, UserID) VALUES(?, ?, ?, ?, ?) ");
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