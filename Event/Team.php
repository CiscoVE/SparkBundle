<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\ClientException;
use CiscoSystems\SparkBundle\Authentication\Oauth;
use CiscoSystems\SparkBundle\Exception\ApiException;

class Team  {

	CONST TEAMURI   = 'https://api.ciscospark.com/v1/teams/';

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

	
	public function getTeams($options = array())
	{
		if (sizeOf($options) < 1)
		{
			return ApiException::errorMessage(999);
		}
	
		$client  = new Client();
		try{
			$response = $client->request("GET", self::TEAMURI, array(
					'headers'       => $this->getBaseHeaders(),
					'query'         => $options,
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
				
			if ($statusCode == 401)
			{
	
				$response = $client->request("GET", self::TEAMURI, array(
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
	
	public function createTeam($options = array())
	{
	
		if (sizeOf($options) < 1)
		{
			return ApiException::errorMessage(999);
		}
		$roomJson = json_encode($options);
	
		$client      = new Client();
		try{
			$response = $client->request("POST", self::TEAMURI, array(
					'headers'    => $this->getBaseHeaders(),
					'body'       => $roomJson,
					'verify'     => false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
				
			if ($statusCode == 401)
			{
				$response = $client->request("POST", self::TEAMURI, array(
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
	
	
	public function getTeamDetails($tid = null)
	{
	
	
		$client = new Client(['base_uri' => self::TEAMURI]);
	
		try{
			$response = $client->request('GET', $tid, array(
					'headers'       => $this->getBaseHeaders(),		
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
				
			if ($statusCode == 401)
			{
				$response = $client->request('GET', $tid, array(
						'headers'       => $this->getRefreshHeaders(),
						'verify' 		=> false
				));
			} else if ($statusCode != 200) {
				return ApiException::errorMessage($statusCode);
			}
	
		}
	
		return $response;
	}
	
	public function updateTeam( $tid = null, $title = 'Default Team Name' )
	{
		$roomJson = '{"name":"'.$title.'"}';
		$client   = new Client(['base_uri' => self::TEAMURI]);
	
		try{
			$response = $client->request('PUT', $tid, array(
					'headers'       => $this->getBaseHeaders(),
					'body'          => $roomJson,
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
				
			if ($statusCode == 401)
			{
				$response = $client->request('PUT', $tid, array(
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
	
	public function deleteTeam( $tid = null )
	{
		$client   = new Client(['base_uri' => self::TEAMURI]);
	
		try{
			$response = $client->request('DELETE', $tid, array(
					'headers'       => $this->getBaseHeaders(),
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
				
			if ($statusCode == 401)
			{
				$response = $client->request('DELETE', $tid, array(
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