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
	
	/* options are roomId,personId,personEmail,max
	 * and should be passed as a key => value array
	 */  
	public function getMemberships($options = array())
	{
		$queryParams = array();
		if (isset($options['roomId'])) { $queryParams['roomId'] = $options['roomId']; }
		if (isset($options['personId'])) { $queryParams['personId'] = $options['personId']; }
		if (isset($options['personEmail'])) { $queryParams['personEmail'] = $options['personEmail']; }
		if (isset($options['max'])) { $queryParams['max'] = $options['max']; }
		
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
	/*  Options are personId, PersonEmail, isModerator.  RoomId is Required */
	public function createMembership($roomId = '', $options = array())
	{
		$requestParams    = array();
		$requestParams["roomId"] 			    = $roomId;
	    	if (isset($options["personId"])){
				$requestParams["personId"] 		= $options["personId"];
			}
			if (isset($options["personEmail"])){
				$requestParams["personEmail"] 	= $options["personEmail"];
			}
			if (isset($options["isModerator"])){
				$requestParams["isModerator"]   = $options["isModerator"];
			}

		$mJson = json_encode($requestParams);
		
		$client  = new Client();
		try{
			$response = $client->request("POST", self::MEMBERSHIPURI, array(
					'headers'       => $this->getBaseHeaders(),
					'body'          => $mJson
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
		
			if ($statusCode == '401')
			{
		
				$response = $client->request("GET", self::MEMBERSHIPURI, array(
						'headers'       => $this->getRefreshHeaders(),
						'body'          => $mJson
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
		$mJson = '';
		if ('' != $isModerator)	{ $roomJson = '{"isModerator": '.$isModerator.' }'; }
		
		
		$client   = new Client(['base_uri' => self::MEMBERSHIPURI]);
		
		try{
			$response = $client->request('PUT', $mid, array(
					'headers'   => $this->getBaseHeaders(),
					'body'		=> $mJson
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('PUT', $mid, array(
						'headers' => $this->getRefreshHeaders(),
						'body'	  => $mJson
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
						'headers'   => $this->getBaseHeaders()					
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('DELETE', $mid, array(
						'headers' => $this->getRefreshHeaders()
				));
			}
		
		}
	return json_decode($response->getBody());
	}

}