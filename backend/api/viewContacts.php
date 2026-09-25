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
        $pullContacts = $conn->prepare("SELECT FirstName, LastName, ID FROM Contacts WHERE UserID = ?");
        if ($pullContacts === false) {
            http_response_code(500);
            returnWithError($conn->error);
            $conn->close();
            exit;
        }

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