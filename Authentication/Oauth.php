<?php
namespace CiscoSystems\SparkBundle\Authentication;

use \GuzzleHttp\Client;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Doctrine\ORM\EntityManager;
use CiscoSystems\SparkBundle\Entity\Token as SparkToken;



class Oauth
{

	protected $configuration;
	protected $em;
	
	public function __construct( array $configuration = array(), EntityManager $em )
	{
		$this->configuration = $configuration;
		$this->em            = $em;
		
	}

	public function getNewToken() 
	{
		$token = null;		

	   	if (isset($this->configuration['granttype']) && $this->configuration['granttype'] == 'saml2-bearer') {
	   	
	   		$token =  $this->getMachineToken();
	   
	   	} else if (isset($this->configuration['granttype']) && $this->configuration['granttype'] == 'code')  {
	        /* not implemented yet */
	   		$token = $this->getCodeToken();	
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
	   	
	return $token;	
	}
	
	public function getMachineToken()
	{
		$authlink; $genericJsonBody; $genericToken;
		
		$tokenURI        = 'https://idbroker.webex.com/idb/oauth2/v1/access_token';
		
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
		
		if (isset($this->configuration['client_id']) && isset($this->configuration['client_secret']))
		{
			$genericToken = 'Basic ' . base64_encode($this->configuration['client_id'].':'.$this->configuration['client_secret']);
		} else {
			throw new InvalidConfigurationException( "Either the 'client_id' and/or the 'client_secret' parameters are not configured in your config.yml file." );	
		}
			
		/* This will Start by getting us a bearer token that we need for the machine user */
		$genClient   = new \GuzzleHttp\Client();
		$response = $genClient->post($authlink, [
				'headers'         => ['Content-Type' => 'application/json'],
				'body'            => $genericJsonBody,
				'allow_redirects' => true,
				'timeout'         => 5
		]);
		
		$jsonarray 		= json_decode($response->getBody());
		$bearerToken    = $jsonarray->BearerToken;
		
		/* This  is the second part and will use the bearer token to issue an access token. */ 
		$client = new \GuzzleHttp\Client();
		$c2     = $client->post($tokenURI, [
		 'headers'         => ['Authorization'=>$genericToken,'Content-Type'=>'application/x-www-form-urlencoded','user-agent'=>'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'],
		 'body'            => "grant_type=urn:ietf:params:oauth:grant-type:saml2-bearer&assertion=".$bearerToken."&scope=spark:rooms_read spark:rooms_write spark:memberships_read spark:memberships_write spark:messages_read spark:messages_write spark:people_read",
		 ]);
		
		 $authresponse = json_decode($c2->getBody());
		 $machineToken = "Bearer " . $authresponse->access_token;
		
		 return $machineToken; 
	}
	
	
	
	
}


