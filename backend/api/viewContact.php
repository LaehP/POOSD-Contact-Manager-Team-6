<?php 
    require_once "databaseConnection.php";
    require_once "frontendInfo.php";

    $conn = connectToDatabase();
    if (empty($_GET['userId'])) {
        http_response_code(400);
        returnWithError("User ID must be defined");
        exit;
    }
    if (empty($_GET['id'])) {
        http_response_code(400);
        returnWithError("Contact ID must be defined");
        exit;
    }
    $userId = $_GET['userId'];
    $id = $_GET['id'];
    if ($conn->connect_error) 
    {
        http_response_code(500);
        returnWithError( $conn->connect_error );
        exit;
    }
    else {
        $pullContact = $conn->prepare("SELECT FirstName, LastName, Phone, Email, date_added FROM CONTACTS WHERE UserID = ? AND ID = ? ");
        $pullContact->bind_param("ii", $userId, $id);
        if (!($pullContact->execute())) {
            http_response_code(500);
            returnWithError("Error encountered when pulling contact.");
            $pullContact->close();
            $conn->close();
            exit;
        }
        $result = $pullContact->get_result();
        $pullContact->close();
        $conn->close();
        if ($result->num_rows === 0) {
            http_response_code(404);
            returnWithError("Contact not found.");
        }
        $contactData = [];
        while ($row = $result->fetch_assoc()) {
            $contactData[] = $row;
        }
        returnContactInfo($contactData);
    }
?>