<?php
    // server side search, no shared contacts
    // search contacts in the database with the same first name and or last name given by the user and there contacts and return each contact's FistName, LastName, PhoneNumber. This will allow the user to select which contact they want to view. 
    // (partial matching) if user searches "Jo" the search algorithm should match everything with "Jo" in it (case insensitive) ie, John, Jones, Jobs

    // get the request data
    // trim the search term and first name and last name to remove any leading or trailing whitespace
    $inData = getRequestInfo();
    $searchTerm = trim((string)($inData["search"] ?? ""));
    $firstName = trim((string)($inData["firstName"] ?? ""));
    $lastName = trim((string)($inData["lastName"] ?? ""));
    $userId = $inData["userId"] ?? "";

    // Check for missing or empty fields before building the LIKE pattern.
    if (($searchTerm === "" && $firstName === "" && $lastName === "") || $userId === "")
    {
        returnWithError("All fields are required");
        exit;
    }


    // parse the .env file for database connection parameters
    $envPath = __DIR__ . '/../../.env';

    // check if file exists
    if (!file_exists($envPath)) {
        returnWithError("Server Configuration Error: .env file not found at " . $envPath);
        exit;
    }

    // try to parse it
    $env = @parse_ini_file($envPath);
    
    if ($env === false) {
         returnWithError("Server Configuration Error: Failed to parse .env file.");
         exit;
    }

    // check if required DB keys exist in the parsed file
    if (!isset($env['DB_HOST'], $env['DB_USER'], $env['DB_PASSWORD'], $env['DB_NAME'])) {
        returnWithError("Server Configuration Error: Missing database credentials in .env file.");
        exit;
    }

    $searchResults = [];
    $searchCount = 0;

    // connect to the database
    if (!class_exists("mysqli"))
    {
        returnWithError("Server Configuration Error: PHP mysqli extension is not enabled.");
        exit;
    }

    // Connection to the database using the parameters from the .env file
    try
    {
        $conn = new mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASSWORD'], $env['DB_NAME']);
    }
    catch (Throwable $exception)
    {
        returnWithError("Database connection failed: " . $exception->getMessage());
        exit;
    }

    if ($conn->connect_error) 
    {
        returnWithError($conn->connect_error);
    } 
    else
    {   
        // Search for the contacts in the database and show all information about the contact by matching information given by the user. This is to confirm that the contacts exist and is associated with the user before showing the contacts.
    
        /* 
        $stmt = $conn->prepare("select FirstName, LastName from Contacts where (FirstName like ? or LastName like ?) and UserID=?");
		$search = "%" . $inData["search"] . "%";
		$stmt->bind_param("ss", $search, $search, $inData["userId"]);
		*/
        
        if ($searchTerm !== "") // if the search term is provided, search by it
        {
            $stmt = $conn->prepare("SELECT ID, FirstName, LastName, Phone FROM Contacts WHERE (FirstName LIKE ? OR LastName LIKE ? OR CONCAT_WS(' ', FirstName, LastName) LIKE ?) AND UserID = ?");
            $search = "%" . $searchTerm . "%";
            $stmt->bind_param("sssi", $search, $search, $search, $userId);
        }
        elseif ($firstName !== "" && $lastName !== "") // if both first name and last name are provided, search by both
        {
            $stmt = $conn->prepare("SELECT ID, FirstName, LastName, Phone FROM Contacts WHERE FirstName LIKE ? AND LastName LIKE ? AND UserID = ?");
            $firstNameSearch = "%" . $firstName . "%";
            $lastNameSearch = "%" . $lastName . "%";
            $stmt->bind_param("ssi", $firstNameSearch, $lastNameSearch, $userId);
        }
        elseif ($firstName !== "") // if only first name is provided, search by it
        {
            $stmt = $conn->prepare("SELECT ID, FirstName, LastName, Phone FROM Contacts WHERE FirstName LIKE ? AND UserID = ?");
            $firstNameSearch = "%" . $firstName . "%";
            $stmt->bind_param("si", $firstNameSearch, $userId);
        }
        else // if only last name is provided, search by it
        {
            $stmt = $conn->prepare("SELECT ID, FirstName, LastName, Phone FROM Contacts WHERE LastName LIKE ? AND UserID = ?");
            $lastNameSearch = "%" . $lastName . "%";
            $stmt->bind_param("si", $lastNameSearch, $userId);
        }
        $stmt->execute();
		
		$result = $stmt->get_result();

		// loop through the result set and add each contact to the searchResults array
		while($row = $result->fetch_assoc()) 
		{
			$searchCount++;
            $searchResults[] = 
            [
                "id" => (int)$row["ID"],
                "firstName" => $row["FirstName"],
                "lastName" => $row["LastName"],
                "phoneNumber" => $row["Phone"]
            ];
		}
		
		if( $searchCount == 0 )
		{
			returnWithError( "No Records Found" );
		}
		else
		{
			returnWithInfo( $searchResults );
		}
		
		$stmt->close();
		$conn->close();
        
    }

    function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo( $searchResults )
	{
        $retValue = json_encode(["results" => $searchResults, "error" => ""]);
		sendResultInfoAsJson( $retValue );
	}

?>