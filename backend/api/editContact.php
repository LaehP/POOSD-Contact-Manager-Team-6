<?php

    require_once __DIR__ . "/databaseConnection.php";
    require_once __DIR__ . "/frontendInfo.php";


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
    if (!array_key_exists("id", $inData)) {
        http_response_code(400);
        returnWithError("Contact ID must be defined");
        exit;
    }
    $contactId = trim((string)$inData["id"] ?? "");
    $userId = trim((string)$inData["userId"] ?? "");
    $contactFirstName = trim((string)$inData["firstName"] ?? "");
    $contactLastName = trim((string)$inData["lastName"] ?? "");
    $contactEmail = trim((string)$inData["email"] ?? "");
    $contactPhoneNumber = trim((string)$inData["phoneNumber"] ?? "");

    if ($userId === "" || $contactEmail === "" || $contactFirstName === "" || $contactLastName === "" || $contactPhoneNumber === "") {
        http_response_code(400);
        returnWithError("Required information must not be blank.");
        exit;
    }

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

    if ($conn->connect_error) 
    {
        http_response_code(500);
        returnWithError( $conn->connect_error );
        exit;

    }
    else {
        $contactUpdate = $conn->prepare("UPDATE Contacts SET FirstName = ?, LastName = ?, Phone = ?, Email = ? WHERE UserID = ? AND ID = ?");
        if ($contactUpdate === false) {
            http_response_code(500);
            returnWithError($conn->error);
            $conn->close();
            exit;
        }
        $contactUpdate->bind_param("ssssii", $contactFirstName, $contactLastName, $contactPhoneNumber, $contactEmail, $userId, $contactId);
        if ($contactUpdate->execute()) {
            $updatedRows = $contactUpdate->affected_rows;
            if ($updatedRows === -1) {
                http_response_code(500);
                returnWithError("An unexpected error occured. Unable to determine update result.");
            }
            else if ($updatedRows === 0) {
                $checkContact = $conn->prepare(
                    "SELECT ID FROM Contacts WHERE userID = ? AND ID = ?"
                );
                $checkContact->bind_param("ii", $userId, $contactId);
                if ($checkContact->execute()) {

                    $result = $checkContact->get_result();

                    if ($result->num_rows === 0) {
                        http_response_code(404);
                        returnWithError("Contact not found.");
                    } else {
                        http_response_code(200);
                        returnWithError("No changes were needed.");
                    }
                }
                else {
                    http_response_code(500);
                    returnWithError($checkContact->error);
                }
                $checkContact->close();
            }
            else {
                $message = json_encode ([
                "message" => "Contact updated successfully"
                ]);
                http_response_code(200);
                sendResultInfoAsJson($message);
            }
        }
        $contactUpdate->close();
        $conn->close();
    }   

?>