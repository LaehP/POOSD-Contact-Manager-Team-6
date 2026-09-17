
<?php

    require_once "databaseConnection.php";
    require_once "frontendInfo.php";


    $conn = new mysqli('localhost', 'root', 'password', 'SmallProject');

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
    $contactFirstName = $inData["firstName"];
    $contactLastName = $inData["lastName"];
    $contactPhoneNumber = $inData["phoneNumber"];
    $contactEmail = $inData["email"];
    $userId = $inData["userId"];


    if ($conn->connect_error) 
    {
        returnWithError( $conn->connect_error );

    }
    else {
        $contactInsertion = $conn->prepare("INSERT into CONTACTS (FirstName, LastName, Phone, Email, UserID) VALUES(?, ?, ?, ?, ?) ");
        $contactInsertion->bind_param("ssssi", $contactFirstName, $contactLastName, $contactPhoneNumber, $contactEmail, $userId);
        $contactInsertion->execute();
        $contactInsertion->close();
        $conn->close();
        returnWithError("");
    }   
?>