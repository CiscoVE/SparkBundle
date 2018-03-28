<?php
namespace CiscoSystems\SparkBundle\Event;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\ClientException;
use CiscoSystems\SparkBundle\Authentication\Oauth;
use CiscoSystems\SparkBundle\Exception\ApiException;

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
					'query'         => $queryParams,
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
			
			if ($statusCode == 401)
			{
		
				$response = $client->request("GET", self::MESSAGESURI, array(
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
	
	public function createMessage($roomId = '', $text = '', $markdown = false, $files = array(), $file = '', $toPersonId = '', $toPersonEmail = '')
	{
	    $requestArray = array();
	    $requestArray["roomId"] 		= $roomId;
	    if ('' != $text){
	        if (!$markdown){
	            $requestArray["text"]   	= $text;
	        } else {
	            $requestArray["markdown"]   	= $text;
	        }
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
					'body'          => $mJson,
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
			
			if ($statusCode == 401)
			{
		
				$response = $client->request("POST", self::MESSAGESURI, array(
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
	
	public function createMultipartMessage($multipart = null)
	{

	    $client  = new Client();
	    try{
	        $response = $client->request("POST", self::MESSAGESURI, array(
	            'headers'       => array('Authorization' => $this->oauth->getStoredToken(),'content-type' => $multipart->contentType()),
	            'body'          => $multipart->data(),
	            'verify' 	   => false
	        ));
	    } catch (ClientException $e) {
	        $errorResponse = $e->getResponse();
	        $statusCode = $errorResponse->getStatusCode();
	        return ApiException::errorMessage($statusCode); 
	    }
	    return $response;		
	}
	
	public function getMessageDetails($mid = null)
	{	
		$client = new Client(['base_uri' => self::MESSAGESURI]);
	
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
				$response = $client->request('GET', $mid, array(
						'headers'       => $this->getRefreshHeaders(),
						'verify' 		=> false
				));
			} else if ($statusCode != 200) {
				return ApiException::errorMessage($statusCode);
			}
	
		}	
		return $response;
	}
	
	public function deleteMessage($mid = null)
	{
	
	
		$client = new Client(['base_uri' => self::MESSAGESURI]);
	
		try{
			$response = $client->request('DELETE', $mid, array(
					'headers'       => $this->getBaseHeaders(),
					'verify' 		=> false
			));
		} catch (ClientException $e) {
			$errorResponse = $e->getResponse();
			$statusCode = $errorResponse->getStatusCode();
			
			if ($statusCode == 401)
			{
				$response = $client->request('DELETE', $mid, array(
						'headers'       => $this->getRefreshHeaders(),
						'verify' 		=> false
				));
			} else if ($statusCode != 204) {
				return ApiException::errorMessage($statusCode);
			}
	
		}
	
		return $response;
	}
	
	public function getMessageFile($fileUrl = null)
	{
	    $resource = fopen('/tmp/sparkfile', 'w');
	    
	    $client = new Client();
	    
	    try{
	        $response = $client->request('GET', $fileUrl, array(
	            'headers'       => $this->getBaseHeaders(),
	            'verify' 	   => false,
	            'sink'          => $resource
	        )); 
	    } catch (ClientException $e) {
	        $errorResponse = $e->getResponse();
	        $statusCode = $errorResponse->getStatusCode();
	        
	        if ($statusCode == 401)
	        {
	            $response = $client->request('GET', $fileUrl, array(
	                'headers'       => $this->getRefreshHeaders(),
	                'verify' 	   => false,
	                'sink'          => $resource
	            ));
	        } else if ($statusCode != 200) {
	            return ApiException::errorMessage($statusCode);
	        }
	        
	    }
	    return $response;
	}
	
	
	
}
