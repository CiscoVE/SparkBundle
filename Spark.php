<?php

namespace CiscoSystems\SparkBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CiscoSystems\SparkBundle\Event\Room;
use CiscoSystems\SparkBundle\Event\Membership;
use CiscoSystems\SparkBundle\Event\Message;
use CiscoSystems\SparkBundle\Event\People;
use CiscoSystems\SparkBundle\Event\WebHook;
use CiscoSystems\SparkBundle\Authentication\Oauth;


class Spark 
{

	protected $room;
	protected $membership;
	protected $message;
	protected $people;
	protected $webhook;
	protected $oauth;
	
	
	public function __construct( Room $room, Membership $membership, Message $message, People $people, WebHook $webhook, Oauth $oauth )
	{
		$this->room    		= $room;
		$this->membership   = $membership;
		$this->message      = $message;
		$this->people       = $people;
		$this->webhook      = $webhook;
		$this->oauth        = $oauth;
	
	}
	
	
	
	public function createRoom($title = "Default Room Name", $moderated = FALSE, $defaultUserEmail = '')
	{
		if ('' == $defaultUserEmail)
		{ return "Add a default user, or this room will not be accessible"; }
		
		$room = $this->room->createRoom($title);
		
		if ($room->id)
		{
			$this->lockRoom($room->id);
			
			$memberOptions = array();
		    $memberOption['personEmail']  = $defaultUserEmail;
		    $memberOptions['isModerator'] = FALSE;

			$this->membership->createMembership($room->id, $memberOptions );

		}
		return $room->id;
	}

	/* This function sets a person as moderator.  Default is to set the API user as moderator */
	public function lockRoom($sparkId = '', $personEmail = '')
	{
	    $membershipOptions = array();
	    $membershipOptions['roomId'] = $sparkId;
	    if ('' != $personEmail){
	    	$membershipOptions['personEmail'] = $personEmail;
	    } else {
	    	$membershipOptions['personId']    = $this->oath->getMachineId( );
	    }
	    
		$membershipArray = $this->membership->getMemberships($membershipOptions);
		$membershipId    = $membershipArray->items->id;
		
		$addMod          = $this->membership->updateMembership($membershipId, TRUE);
	    return $addMod;
		}


}