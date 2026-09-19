<?php 
    require_once "databaseConnection.php";
    require_once "frontendInfo.php";

    $conn = connectToDatabase();
    if (empty($_GET['userId'])) {
        http_response_code(400);
        returnWithError("User ID must be defined");
        exit;
    }
    $userId = $_GET['userId'];
    if ($conn->connect_error) 
    {
        http_response_code(500);
        returnWithError( $conn->connect_error );
        exit;
    }
    else {
        $pullContacts = $conn->prepare("SELECT FirstName, LastName, ID FROM CONTACTS WHERE UserID = ?");
        $pullContacts->bind_param("i", $userId);
        if (!($pullContacts->execute())) {
            http_response_code(500);
            returnWithError("Error encountered when pulling contacts.");
            $pullContacts->close();
            $conn->close();
            exit;
        }
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