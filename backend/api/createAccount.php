<?php
    $inData = getRequestInfo();

    if (!is_array($inData) || !isset($inData["firstName"], $inData["lastName"], $inData["email"], $inData["password"]))
    {
        returnWithError("Missing required fields");
        exit;
    }

    $firstName = trim($inData["firstName"]);
    $lastName = trim($inData["lastName"]);
    $email = trim($inData["email"]);
    $password = $inData["password"];

    if ($firstName === "" || $lastName === "" || $email === "" || $password === "") // Check for empty fields
    {
        returnWithError("All fields are required");
        exit;
    }

    $conn = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'), getenv('DB_NAME'));
    if ($conn->connect_error) 
    {
        returnWithError($conn->connect_error);
    } 
    else
    { 
        $stmt = $conn->prepare("SELECT Login FROM Users WHERE Login = ?"); 
        if (!$stmt)
        {
            returnWithError($conn->error);
            $conn->close();
            exit;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) // Check if the email already exists in the database
        {
            returnWithError("Email already exists");
            $conn->close();
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO Users (FirstName, LastName, Login, Password) VALUES (?, ?, ?, ?)"); // Use a prepared statement to insert the new user into the database
        if (!$stmt)
        {
            returnWithError($conn->error);
            $conn->close();
            exit;
        }

        $stmt->bind_param("ssss", $firstName, $lastName, $email, $password); // Bind the parameters to the prepared statement
        if ($stmt->execute())
        {
            returnWithInfo($firstName, $lastName, $email, $conn->insert_id);
        }
        else
        {
            returnWithError($stmt->error);
        }

        $stmt->close();
        $conn->close();
    }

    // functions:

    function getRequestInfo()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    function sendResultInfoAsJson($obj)
    {
        header('Content-type: application/json');
        echo $obj;
    }

    function returnWithError($err)
    {
        sendResultInfoAsJson(json_encode([
            "ID" => 0,
            "firstName" => "",
            "lastName" => "",
            "email" => "",
            "error" => $err
        ]));
    }

    function returnWithInfo($firstName, $lastName, $email, $id)
    {
        sendResultInfoAsJson(json_encode([
            "ID" => $id,
            "firstName" => $firstName,
            "lastName" => $lastName,
            "email" => $email,
            "error" => ""
        ]));
    }

?>