<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use \GuzzleHttp\Psr7\Request;


class Room  {
	
	CONST ROOMURI   = 'https://api.ciscospark.com/v1/rooms/';
	
	public function createRoom($title = "New Room")
	{
		$roomJson = '{"title":"'.$title.'"}';
		
		$this->accessToken = "1234";
		$at = 'Bearer YjcxYzNjZjgtYjQ1Yi00YTU3LWJlY2ItOTQ1NmVkN2MxMzgzNDI1NmIzY2MtZWE1';

		$client = new Client();
		$baseRequest = new \GuzzleHttp\Psr7\Request("POST", self::ROOMURI, [
    									'Authorization' => 'Bearer 1234',
    									'Content-Type'  => 'application/json',
		], $roomJson );
	

		try{
			$response = $client->send($baseRequest);
		} catch (RequestException $e) {
          
        	$statusCode = $e->getResponse()->getStatusCode();
          	if ($statusCode == '401')
          	{
	
          	$request  = $baseRequest->withHeader('Authorization', $at);
          	$response = $client->send($request);
          	} 

		}

		$jsonResponse =  $response->getBody();
		
		return $jsonResponse->id;

	}
	
	


}