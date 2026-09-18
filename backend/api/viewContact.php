<?php 
    require_once "databaseConnection.php";
    require_once "frontendInfo.php";

    $conn = new mysqli('localhost', 'root', 'password', 'SmallProject');
    $inData = getRequestInfo();
    if (empty($_GET['userId'])) {
        returnWithError("User ID must be defined");
    }
    if (empty($_GET['id'])) {
        returnWithError("Contact ID must be defined");
    }
    $userId = $_GET['userId'];
    $id = $_GET['id'];
    if ($conn->connect_error) 
    {
        returnWithError( $conn->connect_error );
    }
    else {
        $pullContacts = $conn->prepare("SELECT FirstName, LastName, Phone, Email FROM CONTACTS WHERE UserID = ? AND ID = ? ");
        $pullContacts->bind_param("ii", $userId, $id);
        $pullContacts->execute();
        $result = $pullContacts->get_result();
        $pullContacts->close();
        $conn->close();
        $contactData = [];
        while ($row = $result->fetch_assoc()) {
            $contactData[] = $row;
        }
        returnContactInfo($contactData);
    }
?>