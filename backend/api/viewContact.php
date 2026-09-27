<?php 
    require_once __DIR__ . "/databaseConnection.php";
    require_once __DIR__ . "/frontendInfo.php";

    // Make sure LAMP droplet has necessary extensions enabled
    if (!class_exists("mysqli"))
    {
        http_response_code(500);
        returnWithError("Server Configuration Error: PHP mysqli extension is not enabled.");
        exit;
    }

    // attempt to connect to database
    try {
        $conn = connectToDatabase();
    }
    catch (Throwable $exception) {
        http_response_code(500);
        returnWithError("Database connection failed: " . $exception->getMessage());
        exit;
    }

    // make sure necessary parameters are present
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
        // attempt to select information from database
        $pullContact = $conn->prepare("SELECT FirstName, LastName, Phone, Email, date_added FROM Contacts WHERE UserID = ? AND ID = ? ");
        if ($pullContact === false) {
            http_response_code(500);
            returnWithError($conn->error);
            $conn->close();
            exit;
        }
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
        // put each row of contact data found into array
        while ($row = $result->fetch_assoc()) {
            $contactData[] = $row;
        }
        returnContactInfo($contactData);
    }
?>