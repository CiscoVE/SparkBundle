<?php
namespace CiscoSystems\SparkBundle\Authentication;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use \GuzzleHttp\Psr7\Request;
use Symfony\Component\DependencyInjection\Container;


class Saml
{
	
private $container;

public function __construct(Container $container) {
	$this->container = $container;
}
	
public function getNewToken() {
		
		$clientId = $this->container->getParameter('ciscospark.client_id');
		
		echo $clientId;
		
		//$assetsManager = $kernel->getContainer()->get('acme_assets.assets_manager');
	
		/* This will Start by getting us a bearer token that we need for the sfdcspark user 
		$client   = new \GuzzleHttp\Client();
		$response = $client->post($this->authlink_gen, [
				'headers'         => ['Content-Type' => 'application/json'],
				'body'            => '{"name":"'.$this->client_id.'","password":"'.$this->client_secret.'"}',
				'allow_redirects' => true,
				'timeout'         => 5
		]);
	
		$jsonarray 		= json_decode($response->getBody());
		$bearerToken    = $jsonarray->BearerToken;
	
		/* This  is the second part and will use the bearer token to issue a machine token. 
		$c2 = $client->post(self::AUTHLINK_FINAL, [
				'headers'         => ['Authorization'=>self::USERAUTH_GENERIC,'Content-Type'=>'application/x-www-form-urlencoded','user-agent'=>'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'],
				'body'            => "grant_type=urn:ietf:params:oauth:grant-type:saml2-bearer&assertion=".$bearerToken."&scope=Identity:Config Identity:Organization Identity:SCIM",
		]);
	
		$authresponse = json_decode($c2->getBody());
		$machineToken = "Bearer " . $authresponse->access_token;
	
		return $machineToken; */
	}
	
	
	
	
}


