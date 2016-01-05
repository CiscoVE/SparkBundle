<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use CiscoSystems\SparkBundle\Authentication\Oauth;

class People  {

	CONST PEOPLEURI   = 'https://api.ciscospark.com/v1/people/';

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
	
	public function getPeople( $email = null, $displayName = null, $max = null)
	{
		
		$queryParams    = array("email" => $email, "displayName" => $displayName, "max" => $max );
		
		$client  = new Client();
		try{
			$response = $client->request("GET", self::PEOPLEURI, array(
					'headers'       => $this->getBaseHeaders(),
					'query'         => $queryParams
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
		
			if ($statusCode == '401')
			{
		
				$response = $client->request("GET", self::PEOPLEURI, array(
						'headers'       => $this->getRefreshHeaders(),
						'query'         => $queryParams
				));
			}
		
		}
		return json_decode($response->getBody());	
	}
	
	public function getPersonDetail($pid = '')
	{
	
		$client   = new Client(['base_uri' => self::PEOPLEURI]);
	
		try{
			$response = $client->request('GET', $pid, array(
					'headers'       => $this->getBaseHeaders()
			));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('get', $pid, array(
						'headers' => $this->getRefreshHeaders()
				));
			}
	
		}
		return json_decode($response->getBody());
	}
    
	public function getMe()
	{
	
		$client   = new Client(['base_uri' => self::PEOPLEURI]);
	
		try{
			$response = $client->request('GET','me', array(
					'headers'       => $this->getBaseHeaders()
			));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('get','me', array(
						'headers' => $this->getRefreshHeaders()
				));
			}
	
		}
		return json_decode($response->getBody());
	}

}