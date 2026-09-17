<?php
    // shared functions for getting info from and sending info to frontend
    function getRequestInfo()   
	{
		return json_decode(file_get_contents('php://input'), true);
	}

    function sendResultInfoAsJson($obj) 
    {
        header('Content-type: application/json');
        echo $obj;
    }

    function returnWithError( $err )
    {
        $returnValue = '{"error":"' . $err . '"}';
        sendResultInfoAsJson( $returnValue );
    }
?>