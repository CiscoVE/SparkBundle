<?php
namespace CiscoSystems\SparkBundle\Authentication;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Doctrine\ORM\EntityManager;
use CiscoSystems\SparkBundle\Entity\Token as SparkToken;
use CiscoSystems\SparkBundle\Authentication\HttpPost;

class Oauth
{

	protected $configuration;
	protected $em;
	protected $authEndpoint;
	protected $redirectUri;
	protected $consumerAuth;
	
	public function __construct( array $configuration = array(),EntityManager $em )
	{
		$this->configuration = $configuration;
		$this->em            = $em;
		$this->authEndpoint  = "https://api.ciscospark.com/v1/authorize";
		$this->tokenEndpoint = "https://api.ciscospark.com/v1/access_token";
		$this->accessToken   = "https://idbroker.webex.com/idb/oauth2/v1/access_token";
		$this->redirectUri   = (isset($this->configuration['redirect_url'])) ? $this->configuration['redirect_url'] : null;
		$this->consumerAuth  = 'https://idbroker.webex.com/idb/oauth2/v1/authorize';
		if (isset($this->configuration['scope']))
		{
			$this->scope = $this->configuration['scope'];
		}
		else
		{
			$this->scope = 'spark:rooms_read spark:rooms_write spark:memberships_read spark:memberships_write spark:messages_read spark:messages_write spark:people_read';
		}
	}
	
	public function getBaseHeaders()
	{
		return array('Authorization' => $this->getStoredToken(),'content-type'  => 'application/json');
	}
	
	public function getRefreshHeaders()
	{
		return array('Authorization' => $this->getNewToken(),'content-type'  => 'application/json');
	}
	
	public function getClientId()
	{
		
	}

	public function getNewToken() 
	{
		$token = null;		

	   	if (isset($this->configuration['granttype']) && $this->configuration['granttype'] == 'saml2-bearer') {
	   	
	   		$token 	 =  $this->getMachineAccessToken();
	   		
	   	} else if (isset($this->configuration['granttype']) && $this->configuration['granttype'] == 'code')  {

	   		$token = $this->getCodeToken();	
	   		
	   	} else if (isset($this->configuration['granttype']) && $this->configuration['granttype'] == 'consumer')  {
	   		
	   		$token = $this->getConsumerToken($cid,$csc);
	   		
	   	}
	   	
	   	$tokenValue = $this->em->getRepository('CiscoSystemsSparkBundle:Token')->find( $this->configuration['client_id'] );
	   	if ($tokenValue)
	   	{
	   		$tokenValue->setSparkToken($token);
	   		$tokenValue->setClientId($this->configuration['client_id']);
	   		$this->em->persist($tokenValue);
	   		$this->em->flush($tokenValue);
	   	} else {
	   		$newToken = new SparkToken();
	   		$newToken->setSparkToken($token);
	   		$newToken->setClientId($this->configuration['client_id']);
	   		$this->em->persist($newToken);
	   		$this->em->flush($newToken);	
	   	}
	   	
	   	if (isset($this->configuration['granttype']) && $this->configuration['granttype'] == 'saml2-bearer') {
	   	
	   		$mid  =  $this->getMachinePersonId();
	   		if ($mid){		   	
		   		$buid   = str_replace('=','', $mid);	
		   		$updateMid = $this->em->getRepository('CiscoSystemsSparkBundle:Token')->find( array("clientId" => $this->configuration['client_id']) );
		   		$updateMid->setMachinePersonId($buid);
		   		$this->em->persist($updateMid);
		   		$this->em->flush($updateMid);
	   		}

	   	}
	   	
	return $token;	
	}
	
	/* This is the first step in the two factor Oauth authentication */
	public function getMachineBearerToken()
	{
		$authlink; $genericJsonBody; $genericToken;

		/* Check if config parameters are set, before generating links and body */
		if (isset($this->configuration['machine_org']))
		{
		$authlink = 'https://idbroker.webex.com/idb/token/'.$this->configuration['machine_org'].'/v2/actions/GetBearerToken/invoke';
		} else {
			throw new InvalidConfigurationException( "The 'machine_org' parameter is not configured in your config.yml file." );
		}
		
		if (isset($this->configuration['machine_id']) && isset($this->configuration['machine_secret']))
		{
			$genericJsonBody = '{"name":"'.$this->configuration['machine_id'].'","password":"'.$this->configuration['machine_secret'].'"}';
		} else {
			throw new InvalidConfigurationException( "Either the 'machine_id' and/or the 'machine_secret' parameters are not configured in your config.yml file." );			
		}

		/* This will Start by getting us a bearer token that we need for the machine user */
		$genClient   = new \GuzzleHttp\Client();
		$response = $genClient->post($authlink, [
				'headers'         => ['Content-Type' => 'application/json'],
				'body'            => $genericJsonBody,
				'allow_redirects' => true,
				'timeout'         => 5,
				'verify' 		=> false
		]);
		$jsonarray 		= json_decode($response->getBody());
		return $jsonarray->BearerToken;
		
	}
	/* This is the second step in the two factor OAUTH authentication */
	public function getMachineAccessToken()
	{
		/* This  is the second part and will use the bearer token to issue an access token. */
		if (isset($this->configuration['client_id']) && isset($this->configuration['client_secret']))
		{
			$genericToken = 'Basic ' . base64_encode($this->configuration['client_id'].':'.$this->configuration['client_secret']);
		} else {
			throw new InvalidConfigurationException( "Either the 'client_id' and/or the 'client_secret' parameters are not configured in your config.yml file." );
		}
		
		$client = new \GuzzleHttp\Client();
		$c2     = $client->post($this->accessToken, [
		 'headers'         => ['Authorization'=>$genericToken,'Content-Type'=>'application/x-www-form-urlencoded','user-agent'=>'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'],
		 'body'            => "grant_type=urn:ietf:params:oauth:grant-type:saml2-bearer&assertion=". $this->getMachineBearerToken() ."&scope=".$this->scope,
				'verify' 		=> false
		 ]);

		 $authresponse = json_decode($c2->getBody());
		 $accessToken = "Bearer " . $authresponse->access_token;
		 
		 return $accessToken; 
	}
	
	public function getIdentityToken()
	{
		/* This generic application account will be used to update the machine detail such as password. */		
	 
		$genericToken = "Basic Q2Q1NDJiY2Y1N2YzZDIxYWVjMTE3YjQyOGFlMjFiOGFjMGI3MDVmMWZjY2NjY2Y3ZTViZDljYTE1MjAxZDA1Mzc6MDk0OTE0M2FlNTdmZTFkMzYzZWM1NTQ0OGNlNGU3ODI4ODYxNjM4YzUyYmM3MWU1NmE2ODMyMTg5YjZjMTQzOA==";
		
	    $client = new \GuzzleHttp\Client();
		$c2     = $client->post("https://idbroker.webex.com/idb/oauth2/v1/access_token", [
			 'headers'         => ['Authorization'=>$genericToken,'Content-Type'=>'application/x-www-form-urlencoded'],
			 'body'            => "grant_type=urn:ietf:params:oauth:grant-type:saml2-bearer&assertion=". $this->getMachineBearerToken() ."&scope=Identity:Config Identity:Organization Identity:SCIM",
			 'verify' 		=> false
		]);
		$authresponse = json_decode($c2->getBody());
		$machineToken = "Bearer " . $authresponse->access_token;
			
		return $machineToken;
	}
	
	public function getCodeToken()
	{
		if (isset($this->configuration['client_id']) && isset($this->configuration['client_secret']))
		{
			$genericToken = 'Basic ' . base64_encode($this->configuration['client_id'].':'.$this->configuration['client_secret']);
		} else {
			throw new InvalidConfigurationException( "Either the 'client_id' and/or the 'client_secret' parameters are not configured in your config.yml file." );
		}
		if (!isset($this->configuration['redirect_url']))
		{
			throw new InvalidConfigurationException( "You must set a redirect url in your config.yml file." );
		}
		if (!isset($_GET['code']))
		{
			$extraParameters = array('scope' => 'spark:rooms_read spark:rooms_write spark:memberships_read spark:memberships_write spark:messages_read spark:messages_write spark:people_read', 'state' => 'oauth_code_state_id');
		    $codeUrl = $this->getAuthenticationUrl('https://api.ciscospark.com/v1/authorize', $extraParameters );
		
			header('Location: ' . $codeUrl);
			die('Redirect');
		} else {
			$url    = 'https://api.ciscospark.com/v1/access_token';
			$params = array(
					"code" => $_GET['code'],
					"client_id" => $this->configuration['client_id'],
					"client_secret" => $this->configuration['client_secret'],
					"redirect_uri" => $this->configuration['redirect_url'],
					"grant_type" => "authorization_code"
			);
			$request = new HttpPost($url);
			$request->setPostData($params);
			$request->send();
			
			// decode the incoming string as JSON
			$authresponse = json_decode($request->getHttpResponse());
			
			if (isset($authresponse->access_token))
			{
				return "Bearer " . $authresponse->access_token;
			} else {
				return "";
			}
			
			
		}
	}
	
	public function getConsumerToken($username, $password)
	{

		$gotoUrlString  = trim($this->getAuthenticationUrl($this->consumerAuth, array('scope' => $this->scope)));
        $gotoUrl        = base64_encode($gotoUrlString);   
        $sunQueryString = $this->getSunQuery($username);
        $sunQuery       = mb_convert_encoding($sunQueryString, "BASE64", "UTF-8");
		$params = array('IDToken0' => '', 'IDToken1' => $username,'IDToken2' => $password,'IDButton' =>'Sign+In',
					    'goto' => $gotoUrl,'SunQueryParamsString' => $sunQuery,	'encoded' =>'true', 
				        'loginid' =>$username, 'isAudioCaptcha' =>'false', 'gx_charset' => 'UTF-8',
				        'rememberEmail' => 'rememberEmail'
		);

		$c  = new \GuzzleHttp\Client(['cookies' => true]);
		$r = $c->get('https://idbroker.webex.com/idb/UI/Login', [
				'headers'         => ['Content-Type' => 'application/x-www-form-urlencoded'],
				'query'           => $params,
				'allow_redirects' => true,
				'timeout'         => 5,
				'verify' 		=> false
		]);

		if ($r->getStatusCode() == '200')
		{
		  if (null !== $this->parseSecurityCode($r->getBody()))
		  {
		  	
		  	$secCode = $this->parseSecurityCode($r->getBody());
		  	$sc      = new \GuzzleHttp\Client();
		  	
		  	$codeParams  = json_encode(array(
		  			"security_code" => $secCode,
		  			"client_id"     => $this->configuration['client_id'],
		  			"response_type" => 'code',
		  			"decision"      => 'accept'		  		
		  	));
		  	
	        $codeQuery = array(
	        		"response_type" => 'code',
	        		"client_id"     => $this->configuration['client_id'],
	        		"redirect_uri"  => $this->configuration['redirect_url'],
	        		"scope"         => $this->scope
	        );
	        $codeUrl = 'https://idbroker.webex.com/idb/oauth2/v1/authorize?response_type=code&redirect_uri='.$this->configuration['redirect_url'].'&scope='.$this->scope.'&client_id=C4d52e9ec25202b39f454f9128a005a2dfd6476e6ee51a9e92b4d25ba01de3f1b';
		  	$s = $sc->request('POST', $codeUrl, [	  			  		
		  			'allow_redirects' => true,
		  			'timeout'         => 5,
		  			'verify' 		=> false
		  	], $codeParams);
		  	
		  	if ($s->getStatusCode() ==  '302' ) {
            echo $s->getStatusCode();
		  	echo $s->getBody();
		  	} else if ( $s->getStatusCode() ==  '200' ) {
		  		echo $s->getBody();
		  	}
		  }
		}
	

	}
	
	/* Helper functions to Generate Access tokens for API Usage */
	
	/**
	 * getAuthenticationUrl
	 *
	 * @param string $auth_endpoint Url of the authentication endpoint
	 * @param string $redirect_uri  Redirection URI
	 * @param array  $extra_parameters  Array of extra parameters like scope or state (Ex: array('scope' => null, 'state' => ''))
	 * @return string URL used for authentication
	 */
	public function getAuthenticationUrl( $auth_endpoint, array $extra_parameters = array())
	{
		$parameters = array_merge(array(
				'response_type' => 'code',
				'client_id'     => $this->configuration['client_id'],
				'redirect_uri'  => $this->redirectUri
		), $extra_parameters);
		$qs = $auth_endpoint . '?' . http_build_query($parameters, null, '&');
		return $qs;
	}
	
	public function getSunQuery($login)
	{
		$query = 'isCookie=false&fromGlobal=yes&gx_charset=UTF-8&realm=consumer&type=login&msgId_0=identity.login.squareduser.message&email='.$login;
		return  $query;
	}
    
	public function parseSecurityCode($html) {
		if (preg_match('/<input type="hidden" value="(\w+)" name="security_code/', $html , $matches)) {
    		return $matches[1];
		}
		
		if (preg_match('/<input type="hidden" name="security_code" value="(\w+)"/', $html , $matches)) {
			return $matches[1];
		}
	}
	
	
	public function getStoredToken()
	{
		$accessToken = '';
		$tokenValue = $this->em->getRepository('CiscoSystemsSparkBundle:Token')
		->find( $this->configuration['client_id'] );
		if ($tokenValue)
		{
			$accessToken = $tokenValue->getSparkToken();
		}		
		return $accessToken;
	
	}
	
	public function getMachineId()
	{
			
		$client   = new \GuzzleHttp\Client();
		$response = $client->get('https://conv-a.wbx2.com/conversation/api/v1/users/directory?q='.$this->configuration['machine_id'].'&includeMyBots=true', [
				'headers'         => ['Authorization'=> $this->getStoredToken(),'user-agent'=>'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'],
				'verify' 		=> false
		]);
	
		$jsonArray = json_decode($response->getBody());
		$userString = "ciscospark://us/PEOPLE/".$jsonArray[0]->id;
		return base64_encode($userString);
	}
	
	public function getMachinePersonId(){
	
		$client   = new \GuzzleHttp\Client();
	
		try{
			$response = $client->get('https://conv-a.wbx2.com/conversation/api/v1/users/directory?q='.$this->configuration['machine_id'].'&includeMyBots=true', [
					'headers'  => $this->getBaseHeaders(),
					'verify' 		=> false
			]);
		} catch (RequestException $e) {
	
			$statusCode = $e->getResponse()->getStatusCode();
			if ($statusCode == '401')
			{
				$response = $client->get('https://conv-a.wbx2.com/conversation/api/v1/users/directory?q='.$this->configuration['machine_id'].'&includeMyBots=true', [
						'headers'  => $this->getRefreshHeaders(),
						'verify' 		=> false
				]);
	
			} else if ($statusCode != '200') {
				return ApiException::errorMessage($statusCode);
			}
		}
		$jsonArray = json_decode($response->getBody());
		$userString = "ciscospark://us/PEOPLE/".$jsonArray[0]->id;
		return base64_encode($userString);
	}
	
	public function getMachineDetail()
	{
		
		$client   = new \GuzzleHttp\Client();
		$response = $client->get('https://conv-a.wbx2.com/conversation/api/v1/users/directory?q='.$this->configuration['machine_id'].'&includeMyBots=true', [
				'headers'         => ['Authorization'=> $this->getIdentityToken(),'user-agent'=>'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'],
				'verify' 		=> false
		]);
	
		$jsonArray = json_decode($response->getBody());
		
		$userString = $jsonArray[0]->id;
        
		$getclient   = new \GuzzleHttp\Client(array('verify' => false));
		$getresp     = $getclient->get('https://identity.webex.com/organization/'. $this->configuration['machine_org'] .'/v1/Machines/' . $userString ,[
				'headers' => ['Authorization'=> $this->getIdentityToken(), 'Content-Type' => 'application/json','user-agent'=>'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'],
				'verify' 		=> false
		
		]);

		return json_decode($getresp->getBody());
	}
	
	public function setMachineDetail($options = array())
	{
		$client   = new \GuzzleHttp\Client();
		$response = $client->get('https://conv-a.wbx2.com/conversation/api/v1/users/directory?q='.$this->configuration['machine_id'].'&includeMyBots=true', [
				'headers'         => ['Authorization'=> $this->getStoredToken(),'user-agent'=>'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'],
				'verify' 		=> false
		]);
	
		$jsonArray 	=  json_decode($response->getBody());
		$mid 		= $jsonArray[0]->id;
		
		$body = "{";
		if (isset($options["email"])){
			$body .= '"email":"'. $options["email"] .'" ';
		}
		if (isset($options["description"])){
			$body .= '"description":"'. $options["description"] .'" ';
		}
		if (isset($options["password"])){
			$body .= '"password":"'. $options["password"] .'" ';
		}
		$body .= "}";

		$pclient   = new \GuzzleHttp\Client(array('verify' => false));
		$pr        = $pclient->patch('https://identity.webex.com/organization/'. $this->configuration['machine_org'] .'/v1/Machines/' . $mid ,[
				'headers' => ['Authorization'=> $this->getIdentityToken(), 'Content-Type' => 'application/json'],
				'body'    => '{"email" : "dse-sfdc-spark-integration@cisco.com"}',
				'verify'  => false
	
		]);
	
	return json_decode($pr->getBody());
	}
	
	
	
}


