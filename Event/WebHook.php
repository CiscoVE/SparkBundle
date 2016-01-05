<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use CiscoSystems\SparkBundle\Authentication\Oauth;

class WebHook  {

	CONST WEBHOOKURI   = 'https://api.ciscospark.com/v1/webhooks/';

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
	
	public function getWebhooks( $max = null )
	{
		$queryParams    = array( "max" => $max );
	
		$client  = new Client();
		try{
			$response = $client->request("GET", self::WEBHOOKURI, array(
					'headers'       => $this->getBaseHeaders(),
					'query'         => $queryParams
			));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();
	
			if ($statusCode == '401')
			{
	
				$response = $client->request("GET", self::WEBHOOKURI, array(
						'headers'       => $this->getRefreshHeaders(),
						'query'         => $queryParams
				));
			}
	
		}
		return json_decode($response->getBody());
	}
	
	public function createWebhook($name = "My Webhook", $targetUrl = '', $resource = "messages", $event = 'created', $filter = 'roomId=1234')
	{
		
		$requestArray = array();
		$reqeustArray["name"] 		= $name;
		$reqeustArray["targetUrl"] 	= $targetUrl;
		$reqeustArray["resource"] 	= $resource;
		$reqeustArray["event"] 	    = $event;
		$reqeustArray["filter"] 	= $filter;
		
		$whJson = json_encode($requestArray);
		
		$client  = new Client();
		try{
			$response = $client->request("POST", self::WEBHOOKURI, array(
					'headers'       => $this->getBaseHeaders(),
					'body'          => $whJson
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
		
			if ($statusCode == '401')
			{
		
				$response = $client->request("POST", self::WEBHOOKURI, array(
						'headers'       => $this->getRefreshHeaders(),
						'body'          => $whJson
				));
			}
		
		}
		return json_decode($response->getBody());	
	}
	
	public function getWebhookDetails($wid = null)
	{
	
	
		$client = new Client(['base_uri' => self::WEBHOOKURI]);
	
		try{
			$response = $client->request('GET', $wid, array(
					'headers'       => $this->getBaseHeaders()
			));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('GET', $wid, array(
						'headers'       => $this->getRefreshHeaders()
				));
			}
	
		}	
		return json_decode($response->getBody());
	}
	
	public function updateWebhook($wid = null, $name = 'My Webhook', $url = '')
	{
		$requestArray = array();
		$reqeustArray["name"] 		= $name;
		$reqeustArray["targetUrl"] 	= $targetUrl;
		
		$whJson = json_encode($requestArray);
		
		$client = new Client(['base_uri' => self::WEBHOOKURI]);
		
		try{
			$response = $client->request('PUT', $wid, array(
					'headers'       => $this->getBaseHeaders(),
					'body'          => $whJson
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('PUT', $wid, array(
						'headers'       => $this->getRefreshHeaders(),
						'body'          => $whJson
				));
			}
		
		}
		return json_decode($response->getBody());		
	}
	
	public function deleteWebhook($wid = null)
	{
	
	
		$client = new Client(['base_uri' => self::WEBHOOKURI]);
	
		try{
			$response = $client->request('DELETE', $wid, array(
						'headers'       => $this->getBaseHeaders()
			));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('DELETE', $wid, array(
						'headers'       => $this->getRefreshHeaders()
				));
			}
	
		}
	
		return json_decode($response->getBody());
	}
	
	
}