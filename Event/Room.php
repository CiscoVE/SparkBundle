<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Uri;
use Doctrine\ORM\EntityManager;
use CiscoSystems\SparkBundle\Authentication\Oauth;


class Room  {
	
	CONST ROOMURI   = 'https://api.ciscospark.com/v1/rooms/';
	
	protected $oauth;
	
	public function __construct( Oauth $oauth )
	{
		$this->oauth    = $oauth;
		
	}

	public function createRoom($title = "New Room")
	{
		$roomJson = '{"title":"'.$title.'"}';
		
		$client = new Client();
		$baseRequest = new \GuzzleHttp\Psr7\Request("POST", self::ROOMURI, [
    									'Authorization' => $this->oauth->getStoredToken(),
    									'Content-Type'  => 'application/json',
		], $roomJson );
	

		try{
			$response = $client->send($baseRequest);
		} catch (RequestException $e) {
          
        	$statusCode = $e->getResponse()->getStatusCode();
          	if ($statusCode == '401')
          	{
	
          	$request  = $baseRequest->withHeader('Authorization', $this->oauth->getNewToken());
          	$response = $client->send($request);
          	} 

		}

		$jsonResponse =  $response->getBody();
		
		return $jsonResponse->id;

	}
	
	public function updateRoom($rid = null, $showSipAddress = 'FALSE')
	{
	
		$baseHeaders    = array('Authorization' => $this->oauth->getStoredToken(),'content-type'  => 'application/json');
	    $refreshHeaders = array('Authorization' => $this->oauth->getNewToken(),'content-type'  => 'application/json');
		$queryParams    = array('showSipAddress' => $showSipAddress );
		
		
		$client = new Client(['base_uri' => self::ROOMURI]);


		try{
			$response = $client->request('GET', $rid, array(
				'headers'       => $baseHeaders,
				'query'         => $queryParams
			));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
	
				$response = $client->request('GET', $rid, array(
				'headers'       => $refreshHeaders,
				'query'         => $queryParams
				));
			}
	
		}
	
		$jsonResponse =  json_decode($response->getBody());
		print_r($jsonResponse);
	
	}


}