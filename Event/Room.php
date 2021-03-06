<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\ClientException;
use CiscoSystems\SparkBundle\Authentication\Oauth;
use CiscoSystems\SparkBundle\Exception\ApiException;


class Room  {
	
	CONST ROOMURI   = 'https://api.ciscospark.com/v1/rooms/';
	
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
	
	public function getRooms($options = array())
	{
		if (sizeOf($options) < 1)
		{
			return ApiException::errorMessage(999);
		}
		/*$queryParams = array();
		if (isset($options['type'])) 	{ $queryParams['type'] 		= $options['type']; }
		if (isset($options['max'])) 	{ $queryParams['max'] 		= $options['max']; }
		if (isset($options['cursor'])) 	{ $queryParams['cursor'] 	= $options['cursor']; }
		if (isset($options['teamid'])) 	{ $queryParams['teamId'] 	= $options['teamid']; }
		*/
	
		$client  = new Client();
		try{
			$response = $client->request("GET", self::ROOMURI, array(
					'headers'       => $this->getBaseHeaders(),
					'query'         => $options,
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
			
			if ($statusCode == 401)
			{
	
				$response = $client->request("GET", self::ROOMURI, array(
						'headers'       => $this->getRefreshHeaders(),
						'query'         => $queryParams,
						'verify' 		=> false
				));
			} else if ($statusCode != 200) {
				return ApiException::errorMessage($statusCode);
			}
	
		}
		return $response;
	}
	
	public function createRoom($options = array())
	{
		
		if (sizeOf($options) < 1)
		{
			return ApiException::errorMessage(999);
		}
		$roomJson = json_encode($options);
		
		$client      = new Client();
		try{
			$response = $client->request("POST", self::ROOMURI, array(
					'headers'    => $this->getBaseHeaders(),
					'body'       => $roomJson,				
					'verify'     => false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
			
			if ($statusCode == 401)
			{
				$response = $client->request("POST", self::ROOMURI, array(
						'headers'    => $this->getRefreshHeaders(),
						'body'       => $roomJson,
						'verify'     => false
				));
			
          	
			} else if ($statusCode != 200) {
				return ApiException::errorMessage($statusCode);
			}

		}
		
		return $response;
	}
	
	public function getRoomDetails($rid = null, $showSipAddress = 'FALSE')
	{
		$queryParams    = array('showSipAddress' => $showSipAddress );
		
		$client = new Client(['base_uri' => self::ROOMURI]);
		
		try{
			$response = $client->request('GET', $rid, array(
				'headers'       => $this->getBaseHeaders(),
				'query'         => $queryParams,
				'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
			
			if ($statusCode == 401)
			{
				$response = $client->request('GET', $rid, array(
				'headers'       => $this->getRefreshHeaders(),
				'query'         => $queryParams,
				'verify' 		=> false
				));
			} else if ($statusCode != 200) {
				return ApiException::errorMessage($statusCode);
			}
	
		}
	
		return $response;
	}
    
	public function updateRoom( $rid = null, $title = 'Default Room Title' )
	{
		$roomJson = '{"title":"'.$title.'"}';
		$client   = new Client(['base_uri' => self::ROOMURI]);
		
		try{
			$response = $client->request('PUT', $rid, array(
					'headers'       => $this->getBaseHeaders(),
					'body'          => $roomJson,
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
			
			if ($statusCode == 401)
			{
				$response = $client->request('PUT', $rid, array(
						'headers' => $this->getRefreshHeaders(),
						'body'    => $roomJson,
						'verify'  => false
				));
			} else if ($statusCode != 200) {
				return ApiException::errorMessage($statusCode);
			}
		
		}
		return $response;
	}
	
	public function deleteRoom( $rid = null )
	{
		$client   = new Client(['base_uri' => self::ROOMURI]);
	
		try{
			$response = $client->request('DELETE', $rid, array(
					'headers'       => $this->getBaseHeaders(),
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
			
			if ($statusCode == 401)
			{
				$response = $client->request('DELETE', $rid, array(
						'headers' => $this->getRefreshHeaders(),
						'verify' 		=> false
				));
			} else if ($statusCode != 204) {
				return ApiException::errorMessage($statusCode);
			}
	
		}
		return $response;
	}

}