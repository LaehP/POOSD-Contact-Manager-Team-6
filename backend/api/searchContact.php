<?php
    // search contacts in the database with the same first name given by the user and there contacts and return each contact's FistName, LastName, PhoneNumber. This will allow the user to select which contact they want to view. 
    
    // Get the request data
    $inData = getRequestInfo();

    // Check for empty fields
    if ($inData["search"] === "" || $inData["userId"] === "") 
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

   $searchResults = "";
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
        // Search for the contact in the database and show all information about the contact by matching information given by the user. This is to confirm that the contact exists and is associated with the user before showing the contact.
    
        $stmt = $conn->prepare("select Name from Contacts where Name like ? and UserID=?");
		$contactName = "%" . $inData["search"] . "%";
		$stmt->bind_param("ss", $contactName, $inData["userId"]);
		$stmt->execute();
		
		$result = $stmt->get_result();
		
		while($row = $result->fetch_assoc())
		{
			if( $searchCount > 0 )
			{
				$searchResults .= ",";
			}
			$searchCount++;
			$searchResults .= '"' . $row["Name"] . '"';
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
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}

?>