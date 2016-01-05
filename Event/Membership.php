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
		$requestParams    = array();
		$requestParams["roomId"] 			= $roomId;
	    	if ($personId != null){
			$requestParams["personId"] 		= $personId;
			}
			if ($personEmail != null){
			$requestParams["personEmail"] 	= $personEmail;
			}
		$requestParams["isModerator"]       = $isModerator;
		
		$roomJson = json_encode($requestParams);
		
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
	
	public function getMembershipDetail($mid = '')
	{

			$client   = new Client(['base_uri' => self::MEMBERSHIPURI]);
		
			try{
				$response = $client->request('GET', $mid, array(
						'headers'       => $this->getBaseHeaders()
				));
			} catch (RequestException $e) {
		
				$statusCode = $e->getResponse()->getStatusCode();
				if ($statusCode == '401')
				{
					$response = $client->request('get', $mid, array(
							'headers' => $this->getRefreshHeaders()
					));
				}
		
			}
			return json_decode($response->getBody());
	}
	
	public function updateMembership($mid = '', $isModerator = '')
	{
		$roomJson = '';
		if ('' != $isModerator)	{ $roomJson = '{"isModerator": '.$isModerator.' }'; }
		
		
		$client   = new Client(['base_uri' => self::MEMBERSHIPURI]);
		
		try{
			$response = $client->request('PUT', $mid, array(
					'headers'   => $this->getBaseHeaders(),
					'body'		=> $roomJson
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('PUT', $mid, array(
						'headers' => $this->getRefreshHeaders(),
						'body'	  => $roomJson
				));
			}
		
		}
		return json_decode($response->getBody());
	}

	public function deleteMembership( $mid = '' )
	{
		$client   = new Client(['base_uri' => self::MEMBERSHIPURI]);
		
		try{
			$response = $client->request('DELETE', $mid, array(
					'headers'   => $this->getBaseHeaders(),
					'body'		=> $roomJson
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('DELETE', $mid, array(
						'headers' => $this->getRefreshHeaders(),
						'body'	  => $roomJson
				));
			}
		
		}
	return json_decode($response->getBody());
	}

}