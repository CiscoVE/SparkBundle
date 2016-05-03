<?php

namespace CiscoSystems\SparkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="CiscoSystems\SparkBundle\Entity\Repository\TokenRepository")
 * @ORM\Table(name="spark__token")
 */
class Token
{
	

	
	
	/** 
	 * @ORM\Id @ORM\Column(name="client_id", type="string") */
	protected $clientId;
	
	/** @ORM\Column(name="token", type="string") */
	protected $sparkToken;
	
	/** @ORM\Column(name="machinePerson_id", type="string") */
	protected $machinePersonId;
	
	
	
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
	 * Set machinePersonId
	 * @param string $mpid
	 */
	public function setMachinePersonId( $mpid )
	{
		$this->machinePersonId = $mpid;
	
		return $this;
	}
	
	/**
	 * Get machinePersonId
	 *
	 * @return string
	 */
	public function getMachinePersonId()
	{
		return $this->machinePersonId;
	}
	
	
}
