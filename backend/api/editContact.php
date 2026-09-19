<?php

    require_once "databaseConnection.php";
    require_once "frontendInfo.php";


    $inData = getRequestInfo();

    if ($inData === null) {
        returnWithError("JSON decode failed");
    }
    if (!array_key_exists("firstName", $inData) && !array_key_exists("lastName", $inData) && !array_key_exists("phoneNumber", $inData) && !array_key_exists("email", $inData)) {
        returnWithError("Minimum contact information not defined. Must include first name, last name, phone number, and email.");
    }
    if (!array_key_exists("userId", $inData)) {
        returnWithError("User ID must be defined");
    }
    if (!array_key_exists("id", $inData)) {
        returnWithError("Contact ID must be defined");
    }
    $contactId = $inData["id"];
    $userId = $inData["userId"];
    $contactFirstName = $inData["firstName"];
    $contactLastName = $inData["lastName"];
    $contactEmail = $inData["email"];
    $contactPhoneNumber = $inData["phoneNumber"];

    $conn = new mysqli('localhost', 'root', 'password', 'SmallProject');

    if ($conn->connect_error) 
    {
        returnWithError( $conn->connect_error );

    }
    else {
        $contactUpdate = $conn->prepare("UPDATE Contacts SET FirstName = ?, LastName = ?, Phone = ?, Email = ? WHERE UserID = ? AND ID = ?");
        $contactUpdate->bind_param("ssssii", $contactFirstName, $contactLastName, $contactPhoneNumber, $contactEmail, $userId, $contactId);
        $contactUpdate->execute();
        $contactUpdate->close();
        $conn->close();
        returnWithError("Contact edited successfully.");
    }   

?>