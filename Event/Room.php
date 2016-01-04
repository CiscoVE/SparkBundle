<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use \GuzzleHttp\Psr7\Request;
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
	
	


}