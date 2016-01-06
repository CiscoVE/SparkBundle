<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use \GuzzleHttp\Psr7\Request;
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
	
	public function createRoom($title = "New Room")
	{
		$roomJson = '{"title":"'.$title.'"}';
		
		$client      = new Client();
		$baseRequest = new \GuzzleHttp\Psr7\Request("POST", self::ROOMURI, $this->getBaseHeaders() , $roomJson );
		
		try{
			$response = $client->send($baseRequest);
		} catch (RequestException $e) {
          
        	$statusCode = $e->getResponse()->getStatusCode();
          	if ($statusCode == '401')
          	{
	
          	$request  = $baseRequest->withHeader('Authorization', $this->oauth->getNewToken());
          	$response = $client->send($request);
          	} else if ($statusCode != '200') {
				return ApiException::errorMessage($statusCode);
			}

		}
		
		return json_decode($response->getBody());
	}
	
	public function getRoomDetails($rid = null, $showSipAddress = 'FALSE')
	{
		$queryParams    = array('showSipAddress' => $showSipAddress );
		
		$client = new Client(['base_uri' => self::ROOMURI]);
		
		try{
			$response = $client->request('GET', $rid, array(
				'headers'       => $this->getBaseHeaders(),
				'query'         => $queryParams
			));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('GET', $rid, array(
				'headers'       => $this->getRefreshHeaders(),
				'query'         => $queryParams
				));
			} else if ($statusCode != '200') {
				return ApiException::errorMessage($statusCode);
			}
	
		}
	
		return json_decode($response->getBody());
	}
    
	public function updateRoom( $rid = null, $title = 'Default Room Title' )
	{
		$roomJson = '{"title":"'.$title.'"}';
		$client   = new Client(['base_uri' => self::ROOMURI]);
		
		try{
			$response = $client->request('PUT', $rid, array(
					'headers'       => $this->getBaseHeaders(),
					'body'          => $roomJson
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('PUT', $rid, array(
						'headers' => $this->getRefreshHeaders(),
						'body'    => $roomJson
				));
			} else if ($statusCode != '200') {
				return ApiException::errorMessage($statusCode);
			}
		
		}
		return json_decode($response->getBody());
	}
	
	public function deleteRoom( $rid = null )
	{
		$client   = new Client(['base_uri' => self::ROOMURI]);
	
		try{
			$response = $client->request('DELETE', $rid, array(
					'headers'       => $this->getBaseHeaders()
			));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('DELETE', $rid, array(
						'headers' => $this->getRefreshHeaders()
				));
			} else if ($statusCode != '204') {
				return ApiException::errorMessage($statusCode);
			}
	
		}
		return json_decode($response->getBody());
	}

}