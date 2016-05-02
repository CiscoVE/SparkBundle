<?php

namespace CiscoSystems\SparkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="CiscoSystems\SparkBundle\Entity\Repository\TokenRepository")
 * @ORM\Table(name="spark__token")
 */
class Token
{
	
	/** @ORM\Column(name="token", type="string") */
	protected $sparkToken;
	
	/** @ORM\Id @ORM\Column(name="client_id", type="string") */
	protected $clientId;
	
	/** @ORM\Id @ORM\Column(name="clientPersonId", type="string") */
	protected $clientPersonId;
	
	
	
	/**
	 * Set sparkToken
	 * @param string $token
	 */
	public function setSparkToken( $token )
	{
		$this->sparkToken = $token;
	
		return $this;
	}
	
	/**
	 * Get sparkToken
	 *
	 * @return string
	 */
	public function getSparkToken()
	{
		return $this->sparkToken;
	}
	
	/**
	 * Set clientId
	 * @param string $cid
	 */
	public function setClientId( $cid )
	{
		$this->clientId = $cid;
	
		return $this;
	}
	
	/**
	 * Get clientId
	 *
	 * @return string
	 */
	public function getClientId()
	{
		return $this->clientId;
	}
	
	/**
	 * Set clientPersonId
	 * @param string $cpid
	 */
	public function setClientPersonId( $cpid )
	{
		$this->clientPersonId = $cpid;
	
		return $this;
	}
	
	/**
	 * Get clientId
	 *
	 * @return string
	 */
	public function getClientPersonId()
	{
		return $this->clientPersonId;
	}
	
	
}
