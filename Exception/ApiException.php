<?php
namespace CiscoSystems\SparkBundle\Exception;

use Symfony\Component\Config\Definition\Exception\Exception;



class ApiException extends Exception
{

	
	public function errorMessage($code = null)
	{
		
		switch ($code) {
			case 200:
				return array("statusMessage" => "OK");
				break;
			case 204:
				return array("statusMessage" => "Deleted OK");
				break;
			case 400:
				return array("statusMessage" => "The request was invalid or cannot be otherwise served.");
				break;
			case 401: 
				return array("statusMessage" => "Authentication credentials were missing or incorrect.");
				break;
			case 403:
				return array("statusMessage" => "The request is understood, but it has been refused or access is not allowed.");
				break;
			case 404:
				return array("statusMessage" => "The URI requested is invalid or the resource requested, such as a user, does not exist. Also returned when the requested format is not supported by the requested method.
	");
				break;
			case 409:
				return array("statusMessage" => "The request could not be processed because it conflicts with some established rule of the system. For example, a person may not be added to a room more than once.
	");
				break;				
			case 500:
				return array("statusMessage" => "Something went wrong on the server.");
				break;
			case 503:
				return array("statusMessage" => "Server is overloaded with requests. Try again later.");
				break;
		}

	}

	
	

	
	
}