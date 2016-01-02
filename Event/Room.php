<?php
namespace CiscoSystems\SparkBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use \GuzzleHttp\Client as http;


class Room  {
	
	CONST ROOMURI   = 'https://api.ciscospark.com/v1/rooms/';
	
	public function createRoom($title = "New Room")
	{
		$roomJson = '{"title":"'.$title.'"}';
		
		$client   = new \GuzzleHttp\Client();
		$request  = $client->post(self::ROOMURI, [
				'headers'         => ['Authorization'=> $this->accessToken, 
									  'Content-Type' => 'application/json',
						              'user-agent'=>'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'
									 ],
				'body'            => $roomJson
		]);
		
		$response   = $request->send();
		$statuscode = $response->getStatusCode();
		
		$sparkId = json_decode($req->getBody());
		
		return $sparkId->id;
		
		
	
		/* use the stored bearerToken for Authentication */
		String AccessToken = getBearerToken();
		/* New Room JSON String */
		
		 
		
	              = http.send(req);
	
		if ( res.getStatusCode() == 401 ){
			String newToken   = sa.SparkAPIAuth();
			req.setHeader('Authorization', newToken);
			res = http.send(req);
			setBearerToken(newToken);
		}
	
		/* getting the body so we can find the Spark Id */
		Map<String, Object> result = (Map<String, Object>)JSON.deserializeUntyped(res.getBody());
		 
		/* gettting the Id only from the string so that we can update the room later */
		String newSparkId = (String)result.get('id');
	
		String SparkJSON = '{"roomId" : "' + newSparkId + '","personEmail" : "' + creatorEmail + '","isModerator" : false }';
	
		/* This is the call that will add a user to the room, and set that user as moderator */
		HttpRequest req1 = new HttpRequest();
		req1.setMethod('POST');
		req1.setHeader('Authorization', AccessToken);
		req1.setHeader('Content-Type', 'application/json');
		req1.setBody(SparkJSON);
		req1.setEndpoint(SparkBaseURL + 'memberships');
	
		Http http1        = new Http();
		HTTPResponse res1 = new HTTPResponse();
		res1              = http1.send(req1);
		if ( res1.getStatusCode() == 401 ){
			String newToken   = sa.SparkAPIAuth();
			req1.setHeader('Authorization', newToken);
			res1 = http1.send(req1);
			setBearerToken(newToken);
		}
		/* Lock the room to the API User */
		lockRoom(newSparkId);
		return newSparkId;
	}
	
	


}