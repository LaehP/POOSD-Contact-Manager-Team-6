<?php 
    require_once "databaseConnection.php";
    require_once "frontendInfo.php";

    $conn = new mysqli('localhost', 'root', 'password', 'SmallProject');
    $inData = getRequestInfo();
    if (empty($_GET['userId'])) {
        returnWithError("User ID must be defined");
    }
    $userId = $_GET['userId'];
    if ($conn->connect_error) 
    {
        returnWithError( $conn->connect_error );

    }
    else {
        $pullContacts = $conn->prepare("SELECT FirstName, LastName FROM CONTACTS WHERE UserID = ?");
        $pullContacts->bind_param("i", $userId);
        $pullContacts->execute();
        $result = $pullContacts->get_result();
        $pullContacts->close();
        $conn->close();
        sendResultInfoAsJson($result);
    }
?>