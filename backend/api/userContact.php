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
            $pullContact = $conn->prepare("SELECT FirstName, LastName, PhoneNumber, Login FROM Users WHERE ID = ?");
            if ($pullContact === false) {
                http_response_code(500);
                returnWithError($conn->error);
                $conn->close();
                exit;
            }
            $pullContact->bind_param("i", $userId);
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