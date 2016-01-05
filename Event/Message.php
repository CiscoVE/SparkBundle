<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use CiscoSystems\SparkBundle\Authentication\Oauth;

class Message  {

	CONST MESSAGESURI   = 'https://api.ciscospark.com/v1/messages/';

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
	
	/* options should be in key => value array.  Options are before,beforeMessage,max */
	public function getMessages($roomId = '', $options = array() )
	{
		$queryParams = array();
		$queryParams['roomId'] = $roomId;
		if (isset($options['before'])){ $queryParams['before'] = $options['before']; }
		if (isset($options['beforeMessage'])){ $queryParams['beforeMessage'] = $options['beforeMessage']; }
		if (isset($options['max'])){ $queryParams['max'] = $options['max']; }
		
		$client  = new Client();
		try{
			$response = $client->request("GET", self::MESSAGESURI, array(
					'headers'       => $this->getBaseHeaders(),
					'query'         => $queryParams
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
		
			if ($statusCode == '401')
			{
		
				$response = $client->request("GET", self::MESSAGESURI, array(
						'headers'       => $this->getRefreshHeaders(),
						'query'         => $queryParams
				));
			}
		
		}
		return json_decode($response->getBody());		
	}
	
	public function createMessage($roomId = '', $text = '', $files = array(), $file = '', $toPersonId = '', $toPersonEmail = '')
	{
		$requestArray = array();
		$requestArray["roomId"] 		= $roomId;
		if ('' != $text){ 
			$requestArray["text"]   	= $text; 
		}
		if (count($files) > 0){
			$requestArray["files"]   	= $files; /* must be an array array("url","url2","url3") */
		}
		if ('' != $file){
			$requestArray["file"]   	= $file;			
		}
		if ('' != $toPersonId){
			$requestArray["toPersonId"] = $toPersonId;
		}
		if ('' != $toPersonEmail){
			$requestArray["toPersonEmail"] = $toPersonEmail;
		}
		
		
		$mJson = json_encode($requestArray);
		
		$client  = new Client();
		try{
			$response = $client->request("POST", self::MESSAGESURI, array(
					'headers'       => $this->getBaseHeaders(),
					'body'          => $mJson
			));
		} catch (RequestException $e) {
		
			$statusCode = $e->getResponse()->getStatusCode();
		
			if ($statusCode == '401')
			{
		
				$response = $client->request("POST", self::MESSAGESURI, array(
						'headers'       => $this->getRefreshHeaders(),
						'body'          => $mJson
				));
			}
		
		}
		return json_decode($response->getBody());		
	}
	
	public function getMessageDetails($mid = null)
	{	
		$client = new Client(['base_uri' => self::MESSAGESURI]);
	
		try{
			$response = $client->request('GET', $mid, array(
					'headers'       => $this->getBaseHeaders()
			));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('GET', $mid, array(
						'headers'       => $this->getRefreshHeaders()
				));
			}
	
		}	
		return json_decode($response->getBody());
	}
	
	public function deleteMessage($mid = null)
	{
	
	
		$client = new Client(['base_uri' => self::MESSAGESURI]);
	
		try{
			$response = $client->request('DELETE', $mid, array(
					'headers'       => $this->getBaseHeaders()
			));
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->request('DELETE', $mid, array(
						'headers'       => $this->getRefreshHeaders()
				));
			}
	
		}
	
		return json_decode($response->getBody());
	}
	
	
	
}