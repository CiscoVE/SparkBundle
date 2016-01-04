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
	
	public function getBaseHeaders()
	{
		return array('Authorization' => $this->oauth->getStoredToken(),'content-type'  => 'application/json');
	}
	
	public function getRefreshHeaders()
	{
		return array('Authorization' => $this->oauth->getNewToken(),'content-type'  => 'application/json');
	}
	
	public function getMemberships($roomId = null, $personId = null, $personEmail = null, $max = null)
	{
		$queryParams    = array("roomId" => $roomId, "personId" => $personId, "personEmail" => $personEmail, "max" => $max );
		
		$client  = new Client();
		try{
		$response = $client->request("GET", self::MEMBERSHIPURI, array(
				'headers'       => $this->getBaseHeaders(),
				'query'         => $queryParams
		));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();

			if ($statusCode == '401')
			{
	
				$response = $client->request("GET", self::MEMBERSHIPURI, array(
				'headers'       => $this->getRefreshHeaders(),
				'query'         => $queryParams
				));
			}
	
		}
		return json_decode($response->getBody());	
	}
	
	public function createMembership($roomId = null, $personId = null, $personEmail = null, $isModerator = FALSE)
	{
		$queryParams    = array("roomId" => $roomId, "personId" => $personId, "personEmail" => $personEmail, "isModerator" => $isModerator );
		$roomJson = '{"roomId": "'.$roomId.'"';
	    	if ($personId != null){
			$roomJson .= ',"personId": "'.$personId.'"';
			}
			if ($personEmail != null){
			$roomJson .= ',"personEmail": "'.$personEmail.'"';
			}
		$roomJson .= ',"isModerator": '.$isModerator.' }';
		
		$client  = new Client();
		try{
			$response = $client->request("POST", self::MEMBERSHIPURI, array(
					'headers'       => $this->getBaseHeaders(),
					'body'          => $roomJson
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
		
			if ($statusCode == '401')
			{
		
				$response = $client->request("GET", self::MEMBERSHIPURI, array(
						'headers'       => $this->getRefreshHeaders(),
						'body'          => $roomJson
				));
			}
		
		}
		return json_decode($response->getBody());	
	}
	




}