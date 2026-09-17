
<?php

    require_once "databaseConnection.php";
    require_once "frontendInfo.php";


    $conn = new mysqli('localhost', 'root', 'password', 'SmallProject');

    $inData = getRequestInfo();
    if ((!array_key_exists($inData, "firstName") || !array_key_exists($inData, "lastName")) && !array_key_exists($inData, "phoneNumber") && !array_key_exists($inData, "email")) {
        returnWithError("Minimum contact information not defined. Must include first name, last name, phone number, and email.");
    }
    if (!array_key_exists($inData, "userId")) {
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
        if (array_key_exists($inData, "firstName") && array_key_exists($inData, "lastName")) {
            $contactInsertion = $conn->prepare("INSERT into CONTACTS (FirstName, LastName, Phone, Email, UserID) VALUES(?, ?, ?, ?, ?) ");
            $contactInsertion->bind_param("ssssi", $contactFirstName, $contactLastName, $contactPhoneNumber, $contactEmail, $userId);
        }
        else if (array_key_exists($inData, "firstName") && !array_key_exists($inData, "lastName")) {
            $contactInsertion = $conn->prepare("INSERT into CONTACTS (FirstName, Phone, Email, UserID) VALUES(?, ?, ?, ?) ");
            $contactInsertion->bind_param("sssi", $contactFirstName, $contactPhoneNumber, $contactEmail, $userId);
        }
        else {
            $contactInsertion = $conn->prepare("INSERT into CONTACTS (LastName, Phone, Email, UserID) VALUES(?, ?, ?, ?) ");
            $contactInsertion->bind_param("sssi", $contactLasttName, $contactPhoneNumber, $contactEmail, $userId);
        }
        $contactInsertion->execute();
        $contactInsertion->close();
        $conn->close();
        returnWithError("");
    }   
?>