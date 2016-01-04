<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use CiscoSystems\SparkBundle\Authentication\Oauth;

class Membership  {

	CONST MEMBERSHIPURI   = 'https://api.ciscospark.com/v1/memberships/';

	protected $oauth;

	public function __construct( Oauth $oauth )
	{
		$this->oauth    = $oauth;

	}
	
	public function getMemberships($roomId = null, $personId = null, $personEmail = null, $max = null)
	{
		
	    $baseHeaders    = array('Authorization' => $this->oauth->getStoredToken(),'content-type'  => 'application/json');
	    $refreshHeaders = array('Authorization' => $this->oauth->getNewToken(),'content-type'  => 'application/json');
		$queryParams    = array("roomId" => $roomId, "personId" => $personId, "personEmail" => $personEmail, "max" => $max );
		
		$client  = new Client();

		try{
		$response = $client->request("GET", self::MEMBERSHIPURI, array(
				'headers'       => $baseHeaders,
				'query'         => $queryParams
		));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();

			if ($statusCode == '401')
			{
	
				$response = $client->request("GET", self::MEMBERSHIPURI, array(
				'headers'       => $refreshHeaders,
				'query'         => $queryParams
				));
			}
	
		}
		
		$jsonResponse =  json_decode($response->getBody());
		
		return $jsonResponse;
	
	}
	




}