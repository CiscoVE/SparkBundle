<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\ClientException;
use CiscoSystems\SparkBundle\Authentication\Oauth;
use CiscoSystems\SparkBundle\Exception\ApiException;

class TeamMembership  {

	CONST TEAMMEMBERSHIPURI   = 'https://api.ciscospark.com/v1/team/memberships/';

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

	public function getTeamMembership($options = array())
	{
		if (sizeOf($options) < 1)
		{
			return ApiException::errorMessage(999);
		}
	    /* options are: teamId, personId, personEmail, max */
		$client  = new Client();
		try{
			$response = $client->request("GET", self::TEAMMEMBERSHIPURI, array(
					'headers'       => $this->getBaseHeaders(),
					'query'         => $options,
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
	
			if ($statusCode == 401)
			{
				$response = $client->request("GET", self::TEAMMEMBERSHIPURI, array(
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
	
	public function createTeamMembership($teamId = '', $options = array())
	{
		if (sizeOf($options) < 1 || $teamId == '')
		{
			return ApiException::errorMessage(999);
		}
		$requestParams    = array();
		$requestParams["teamId"] 			= $teamId;
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
			$response = $client->post(self::TEAMMEMBERSHIPURI, array(
					'headers'       => $this->getBaseHeaders(),
					'body'          => $mJson,
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
				
			if ($statusCode == 401)
			{
	
				$response = $client->post(self::TEAMMEMBERSHIPURI, array(
						'headers'       => $this->getRefreshHeaders(),
						'body'          => $mJson,
						'verify' 		=> false
				));
			} else if ($statusCode != 200) {
				return ApiException::errorMessage($statusCode);
			}
	
		}
		return $response;
	}
	
	public function getTeamMembershipDetail($mid = '')
	{
	
		$client   = new Client(['base_uri' => self::TEAMMEMBERSHIPURI]);
	
		try{
			$response = $client->request('GET', $mid, array(
					'headers'       => $this->getBaseHeaders(),
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
	
			if ($statusCode == 401)
			{
				$response = $client->request('get', $mid, array(
						'headers' => $this->getRefreshHeaders(),
						'verify'  => false
				));
			} else if ($statusCode != 200) {
				return ApiException::errorMessage($statusCode);
			}
	
		}
		return $response;
	}
	
	public function updateTeamMembership($mid = '', $isModerator = '')
	{
		$mJsonArray = array();
		if ('' !== $isModerator)	{ $mJsonArray['isModerator'] = $isModerator; }
		$mJson    = json_encode($mJsonArray);
		$client   = new Client(['base_uri' => self::TEAMMEMBERSHIPURI]);
	
		try{
			$response = $client->request('PUT', $mid, array(
					'headers'   => $this->getBaseHeaders(),
					'body'		=> $mJson,
					'verify' 	=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
				
			if ($statusCode == 401)
			{
				$response = $client->request('PUT', $mid, array(
						'headers' => $this->getRefreshHeaders(),
						'body'	  => $mJson,
						'verify'  => false
				));
			} else if ($statusCode != 200) {
				return ApiException::errorMessage($statusCode);
			}
	
		}
		return $response;
	}
	
	public function deleteTeamMembership( $mid = '' )
	{
		$client   = new Client(['base_uri' => self::TEAMMEMBERSHIPURI]);
	
		try{
			$response = $client->request('DELETE', $mid, array(
					'headers'   => $this->getBaseHeaders(),
					'verify' 	=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
				
			if ($statusCode == 401)
			{
				$response = $client->request('DELETE', $mid, array(
						'headers' => $this->getRefreshHeaders(),
						'verify'  => false
				));
			} else if ($statusCode != 204) {
				return ApiException::errorMessage($statusCode);
			}
	
		}
		return $response;
	}
	
	
	
	
	
}