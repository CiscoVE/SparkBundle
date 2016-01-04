<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use \GuzzleHttp\Psr7\Request;
use CiscoSystems\SparkBundle\Authentication\Oauth;

class Membership  {

	CONST MEMBERSHIPURI   = 'https://api.ciscospark.com/v1/memberships/';

	protected $oauth;

	public function __construct( Oauth $oauth )
	{
		$this->oauth    = $oauth;

	}
	
	public function getMemberships($roomId = null, $personId = null, $personEmail = null, $max = null)
	{
		
	    $client = new Client();
		$baseRequest = new \GuzzleHttp\Psr7\Request("POST", self::MEMBERSHIPURI, [
				'Authorization' => $this->oauth->getStoredToken(),
				'Content-Type'  => 'application/json',
				'query'         => []
		] );

		
	
	
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
		echo $jsonResponse;
		return ''; //$jsonResponse->id;
	
	}
	




}